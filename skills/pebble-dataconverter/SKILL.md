---
name: pebble-dataconverter
description: How to correctly decode, reshape, convert and hydrate structured data with the sopheos/pebble_dataconverter PHP library (namespace Pebble\DataConverter — classes Converter, MapConverter, CollectionConverter, HydrateConverter, CallbackConverter, DatetimeConverter, JsonDecodeConverter, JsonEncodeConverter, BooleanDecodeConverter, BooleanEncodeConverter, Helpers and ConverterInterface). Use this whenever the project's composer.json requires sopheos/pebble_dataconverter, code imports from Pebble\DataConverter\*, or you're asked to turn a JSON payload, an API response, a database row or a form submission into objects, convert a date format, cast 0/1 flags to booleans, or transform every item of a list in a PHP project that has this library available — even if the request is phrased generically like "map this JSON to a DTO" or "convert these dates to d/m/Y" without naming the library. Also check this before writing a hand-rolled array_map/hydration loop in such a project, since this library replaces those and has non-obvious and in places broken behavior (setX()/withX() setters are never called so only public properties get hydrated, an unparsable date is a fatal error or silently becomes 1970-01-01, MapConverter returns null for plain objects, null skips callbacks, 'true' decodes to false, invalid rules are silently dropped) that hand-rolled code would miss.
---

# pebble-dataconverter

`sopheos/pebble_dataconverter` is a small PHP 8.1+ library of invokable, chainable converters. Each converter transforms its input in a `prepare()` step, then passes the result through any rules added with `one()` (applied to the value) or `many()` (applied to each item). Rules can be converter objects, callables, associative arrays (per-key rules) or class names/objects (hydration), so a whole JSON-to-object pipeline is one expression. The library does **not** validate, throw business exceptions or report errors: bad input is usually passed through or turned into `null`.

Namespace: `Pebble\DataConverter\*`. Source lives in `vendor/sopheos/pebble_dataconverter/src/`. Read it directly when you need an exact method signature; this skill focuses on *how the pieces fit together* and the behavior that isn't obvious from the method names.

## Orientation

- Every converter implements `ConverterInterface`: `one($rule)`, `many($rule)` (both fluent) and `__invoke($input)`. Call it like a function: `$converter($input)`.
- Every converter has a static `create(...)` with the constructor's arguments.
- `Helpers::parseRule()` turns a rule into a converter, in this order: `ConverterInterface` as-is, callable → `CallbackConverter`, array → `MapConverter`, existing class name or object → `HydrateConverter`, anything else → `null` (the rule is silently dropped).
- `Converter` does nothing by itself: it is the empty start of a chain (`Converter::create()->many(...)`).
- `MapConverter` applies a rule per key of an array. `CollectionConverter` applies one rule per item. `HydrateConverter` builds objects from arrays.
- `DatetimeConverter`, `JsonDecodeConverter`/`JsonEncodeConverter` and `BooleanDecodeConverter`/`BooleanEncodeConverter` are leaf converters.

For a full method cheat sheet, see `references/api-reference.md`. For the complete list of easy-to-miss behaviors, see `references/gotchas.md`. Read it before debugging a field that "should be set".

## Core recipes

### JSON payload to nested objects

```php
use Pebble\DataConverter\Converter;
use Pebble\DataConverter\DatetimeConverter;
use Pebble\DataConverter\JsonDecodeConverter;
use Pebble\DataConverter\MapConverter;

$carConverter = MapConverter::create()
    ->map('model', 'ucfirst')
    ->map('date', DatetimeConverter::create('!Y-m-d', 'd/m/Y'))
    ->one(Car::class);                              // hydrate after mapping

$userConverter = JsonDecodeConverter::create()
    ->one([                                         // array rule = MapConverter
        'birthdate' => DatetimeConverter::create('!Y-m-d', 'd/m/Y'),
        'cars' => Converter::create()->many($carConverter),
    ])
    ->one(new User);                                // object rule = cloned prototype

$user = $userConverter($json);
```

Order matters: map keys **before** hydrating, because `MapConverter` returns `null` for a plain object.

### Hydrating objects

```php
$person = HydrateConverter::create(Person::class)(['name' => 'Toto']);  // new Person()
$person = HydrateConverter::create($prototype)($row);                   // clone $prototype
```

Only **public non-static properties** are filled. Setters (`setName()`) and withers (`withName()`) are **not called** (bug), so a DTO with private properties and setters stays empty. Until it is fixed, either use public properties or hydrate with a callback:

```php
Converter::create()->one(function (array $row) {
    $account = new Account();
    $account->setEmail($row['email'] ?? null);
    return $account;
});
```

A class given by name is instantiated with **no constructor arguments**. For a class with a required constructor, pass a prebuilt object as the prototype.

### Converting dates

```php
DatetimeConverter::create(DatetimeConverter::SQL, 'd/m/Y')('2024-02-29 10:00:00');  // '29/02/2024'
DatetimeConverter::create('!d/m/Y', DatetimeConverter::TS)('01/02/1990');           // midnight timestamp
DatetimeConverter::create(DatetimeConverter::TS, DatetimeConverter::ISO)(time());
```

Prefix custom formats with `!`: without it, fields missing from the format take the current time. Validate the input yourself before converting: an unparsable custom-format date is a **fatal `Error`**, and an unparsable `ISO`/`SQL` date gives `false` or `1970-01-01`.

### Lists

```php
CollectionConverter::create(Person::class)($rows);   // keys kept, Traversable -> array
Converter::create()->many('trim')->many('strtoupper')($list);
```

### Flags and JSON columns

```php
MapConverter::create([
    'active' => BooleanDecodeConverter::create(),   // '1' -> true, '0' -> false, '' -> null
    'options' => JsonDecodeConverter::create(),     // '{"a":1}' -> ['a' => 1]
])($row);
```

## Behavior to keep in mind while writing code

- **(bug) Setters and withers are never called by `HydrateConverter`.** Only public properties are hydrated, everything else is silently dropped.
- **(bug) An unparsable date with a custom format is a fatal `Error`.** `createFromFormat()` returns `false` and `getTimestamp()` is called on it.
- **(bug) An unparsable `ISO`/`SQL` date gives `false` (to `TS`) or the epoch (to any other format).**
- **Missing date fields default to "now"** unless the format starts with `!`.
- **`null` short-circuits `CallbackConverter`** (the callable is never called) and `DatetimeConverter`.
- **`MapConverter` only accepts arrays and `Traversable`.** A plain object or a scalar gives `null`. Missing keys are **added** with the rule's result on `null`.
- **`CollectionConverter` gives `[]` for non-iterable input**, while `MapConverter` gives `null`.
- **`BooleanDecodeConverter` casts strings to int**: `'true'` and `'yes'` give `false`.
- **`JsonEncodeConverter` returns `null` for scalars and `null`**; `JsonDecodeConverter` returns `null` for invalid JSON or a JSON scalar, and passes non-strings through.
- **Invalid rules are silently ignored.** A typo in a class or function name turns the rule into a no-op.
- **A function name is a callable rule**, so `'trim'` becomes a `CallbackConverter`, not a class lookup.

Read `references/gotchas.md` for the rest before assuming a converter validates its input.
