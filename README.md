# Pebble/DataConverter

Bibliothèque de conversion de données structurées : décoder du JSON, transformer certaines clés d'un tableau, convertir des dates et hydrater des objets, en chaînant des règles.

## Installation

```bash
composer require sopheos/pebble_dataconverter
```

## Claude Code

Ce package fournit un skill Claude Code dans [`skills/pebble-dataconverter/`](skills/pebble-dataconverter/). Il documente les patterns d'usage et les pièges de la librairie : setters jamais appelés par l'hydratation, dates non parsables fatales, `MapConverter` qui ignore les objets simples, `null` qui court-circuite les callbacks, etc.

Dans un projet qui dépend de `sopheos/pebble_dataconverter`, copie-le une fois dans `.claude/skills/` après `composer install` pour que Claude Code le charge automatiquement. Le nom du dossier doit correspondre au `name` déclaré dans `SKILL.md` :

```bash
cp -r vendor/sopheos/pebble_dataconverter/skills/pebble-dataconverter .claude/skills/pebble-dataconverter
```

Pour la maintenance de la lib elle-même, voir [`CLAUDE.md`](CLAUDE.md). Les bugs connus sont listés dans [`TODO.md`](TODO.md).

## Règle de transformation

Partout où une règle est attendue (`one()`, `many()`, `map()`, `CollectionConverter`), elle peut être, dans cet ordre de priorité :

* un objet de conversion qui implémente `ConverterInterface` ;
* une fonction de rappel (`callable`, y compris un nom de fonction comme `'ucfirst'`), encapsulée dans un `CallbackConverter` ;
* un tableau associatif qui associe des clés de la donnée à une règle, encapsulé dans un `MapConverter` ;
* un objet ou un nom de classe à hydrater à partir de la donnée, encapsulé dans un `HydrateConverter`.

Une règle qui ne correspond à rien (entier, nom de classe inexistant…) est **ignorée silencieusement**.

## Chaînage

Chaque objet de conversion (`ConverterInterface`) est invocable et permet d'ajouter d'autres règles exécutées ensuite, dans l'ordre d'ajout, sur le résultat de sa propre conversion.

* `one(mixed $rule) : static` Ajoute une règle qui agit sur la donnée.
* `many(mixed $rule) : static` Ajoute une règle qui agit sur chaque élément d'une donnée itérable (via `CollectionConverter`).
* `__invoke(mixed $input) : mixed` Exécute la conversion.

Tous les converters ont un constructeur statique `create(...)` qui prend les mêmes arguments que le constructeur.

## Converter

Converter vide, qui ne fait rien par lui-même. Sert de point de départ à une chaîne.

* `Converter::create() : static`

## CallbackConverter

* `CallbackConverter::create(callable $callback) : static` Applique `$callback` à la donnée. Si la donnée est `null`, le callback n'est pas appelé et `null` est renvoyé.

## CollectionConverter

* `CollectionConverter::create(mixed $rule) : static` Applique la règle à chaque élément d'un itérable. Les clés sont conservées, un `Traversable` est converti en tableau. Une donnée non itérable donne `[]`.

## MapConverter

* `MapConverter::create(array $rules = []) : static` Règles indexées par clé.
* `map(string $key, mixed $rule) : static` Ajoute la règle d'une clé.

Applique chaque règle à la clé correspondante d'un tableau (ou d'un `Traversable`, converti en tableau). Les autres clés sont conservées. Une clé absente est ajoutée avec le résultat de la règle sur `null`. Une donnée non itérable, y compris un objet simple, donne `null`.

## HydrateConverter

* `HydrateConverter::create(string|object $object) : static` Classe ou prototype à hydrater. Une classe est instanciée **sans argument** ; un objet est cloné.

Transforme un tableau associatif en objet : chaque appel clone le prototype puis renseigne ses propriétés **publiques** non statiques. Les clés inconnues sont ignorées. Une donnée qui n'est pas un tableau est renvoyée telle quelle. Un nom de classe inexistant lève `InvalidArgumentException`.

**Attention** : le code prévoit d'utiliser les setters `setX()` et `withX()` pour les clés sans propriété publique, mais ils ne sont jamais appelés (voir [`TODO.md`](TODO.md)).

## DatetimeConverter

* `DatetimeConverter::create(string $from = DatetimeConverter::TS, string $to = DatetimeConverter::TS) : static` Convertit une date du format `$from` vers le format `$to` (formats de `date()`).

Constantes : `TS` (`'U'`, timestamp), `ISO` (`'c'`), `SQL` (`'Y-m-d H:i:s'`).

* `null` reste `null`. Si `$from === $to`, la donnée est renvoyée sans vérification.
* Les formats `ISO`, `SQL` et `DateTime::ISO8601` sont lus avec `strtotime()`, les autres avec `DateTime::createFromFormat()`.
* Avec `createFromFormat()`, les champs absents du format prennent la valeur de l'heure courante : préfixer le format par `!` (`'!d/m/Y'`) pour les mettre à zéro.
* **Attention** : une date non parsable plante (format personnalisé) ou donne `false` / le 1er janvier 1970 (formats `ISO`/`SQL`). Voir [`TODO.md`](TODO.md).

## JsonDecodeConverter / JsonEncodeConverter

* `JsonDecodeConverter::create() : static` Décode une chaîne JSON en tableau associatif. Renvoie `null` si le JSON est invalide ou ne contient pas un tableau/objet. Une donnée qui n'est pas une chaîne est renvoyée telle quelle.
* `JsonEncodeConverter::create() : static` Encode un tableau ou un objet (propriétés publiques) en JSON. Toute autre donnée donne `null`.

## BooleanDecodeConverter / BooleanEncodeConverter

* `BooleanDecodeConverter::create() : static` `null` et `''` donnent `null`. Une chaîne est castée en entier avant le test : `'1'` donne `true`, mais `'true'` donne `false`.
* `BooleanEncodeConverter::create() : static` `null` reste `null`, sinon `1` ou `0` selon la valeur de vérité.

## Exemple

```php
use Pebble\DataConverter\Converter;
use Pebble\DataConverter\DatetimeConverter;
use Pebble\DataConverter\JsonDecodeConverter;
use Pebble\DataConverter\MapConverter;

class Car
{
    public $number;
    public $model;
    public $date;
}

class User
{
    public $name;
    public $birthdate;
    public array $cars = [];
}

$input = json_encode([
    'name' => 'Toto',
    'birthdate' => '1980-10-03',
    'cars' => [
        [
            'number' => 1651984,
            'model' => 'renault',
            'date' => '2000-01-01'
        ],
        [
            'number' => 2061161,
            'model' => 'audi',
            'date' => '2021-08-19'
        ],
    ]
]);

$carConverter = MapConverter::create()
    ->map('model', function ($input) {
        return ucfirst($input);
    })
    ->map('date', DatetimeConverter::create('Y-m-d', 'd/m/Y'))
    ->one(Car::class);

$userConverter = JsonDecodeConverter::create()
    ->one([
        'birthdate' => DatetimeConverter::create('Y-m-d', 'd/m/Y'),
        'cars' => Converter::create()->many($carConverter)
    ])
    ->one(new User);

$output = $userConverter($input);
```

Résultat :

```
User Object
(
    [name] => Toto
    [birthdate] => 03/10/1980
    [cars] => Array
        (
            [0] => Car Object
                (
                    [number] => 1651984
                    [model] => Renault
                    [date] => 01/01/2000
                )

            [1] => Car Object
                (
                    [number] => 2061161
                    [model] => Audi
                    [date] => 19/08/2021
                )

        )

)
```

## Tests

```bash
composer install
vendor/bin/phpunit
```

Les bugs connus sont figés par des tests annotés `// BUG:` qui vérifient le comportement actuel.
