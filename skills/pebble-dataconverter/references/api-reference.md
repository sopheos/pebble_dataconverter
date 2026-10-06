# pebble-dataconverter — API cheat sheet

Quick lookup by intent. This is not exhaustive. Read the source in `vendor/sopheos/pebble_dataconverter/src/` for exact signatures and for edge cases not covered here.

## Common API (`Pebble\DataConverter\ConverterInterface`)

Implemented by every converter. `one()` and `many()` are fluent (they return `static`).

| Intent | Method |
| ------ | ------ |
| Add a rule applied to the converted value | `one(mixed $rule): static` |
| Add a rule applied to each item (wraps it in `CollectionConverter`) | `many(mixed $rule): static` |
| Run the conversion | `__invoke(mixed $input): mixed` |

Execution order: the converter's own `prepare()` step, then every `one()`/`many()` rule in the order they were added, each receiving the previous result.

## Rules (`Helpers::parseRule(mixed $rule): ?ConverterInterface`)

| Rule | Becomes |
| ---- | ------- |
| `ConverterInterface` instance | itself |
| any callable (closure, `'trim'`, `[$obj, 'm']`) | `CallbackConverter` |
| array | `MapConverter` (keys → rules) |
| existing class name, or any non-callable object | `HydrateConverter` |
| anything else | `null`, silently dropped by `one()`, `many()` and `map()` |

## Converters

| Class | Constructor / `create()` | `prepare()` behavior |
| ----- | ------------------------ | -------------------- |
| `Converter` | `create()` | identity |
| `CallbackConverter` | `create(callable $callback)` | `null` → `null` (callback skipped), else `$callback($input)` |
| `CollectionConverter` | `create(mixed $rule)` | non-iterable → `[]`; `Traversable` → array; rule applied per item, keys kept |
| `MapConverter` | `create(array $rules = [])`, then `map(string $key, mixed $rule): static` | non-iterable (incl. plain objects) → `null`; `Traversable` → array; `$input[$key] = rule($input[$key] ?? null)` for each mapped key |
| `HydrateConverter` | `create(string\|object $object)` | non-array → unchanged; array → clone of the prototype with public properties set |
| `DatetimeConverter` | `create(string $from = TS, string $to = TS)` | see below |
| `JsonDecodeConverter` | `create()` | non-string → unchanged; `json_decode($input, true)`; non-array result → `null` |
| `JsonEncodeConverter` | `create()` | array/object → `json_encode()`; anything else → `null` |
| `BooleanDecodeConverter` | `create()` | `null`/`''` → `null`; string cast to `int`; then truthiness → `bool` |
| `BooleanEncodeConverter` | `create()` | `null` → `null`; truthiness → `1`/`0` |

## HydrateConverter details

- A class name must exist (`InvalidArgumentException` otherwise) and is built with `new $class()` once, at construction.
- An object is cloned at construction and cloned again on every call (shallow clone).
- Each key is applied in order: public non-static property → `setKey()` → `withKey()` (return value kept) → ignored. The two setter steps never fire (bug).

## DatetimeConverter details

Constants: `TS = 'U'`, `ISO = 'c'`, `SQL = 'Y-m-d H:i:s'`.

| Case | Result |
| ---- | ------ |
| `null` input | `null` |
| `$from === $to` | input unchanged, not validated |
| `$from === TS` | `date($to, (int) $input)` |
| `$from` is `ISO`, `SQL` or `DateTime::ISO8601` | `strtotime($input)`, then `$to` (`false` on failure, bug) |
| any other `$from` | `DateTime::createFromFormat($from, $input)->getTimestamp()` (fatal on failure, bug) |

Dates are interpreted and formatted in the default timezone (`date_default_timezone_get()`).
