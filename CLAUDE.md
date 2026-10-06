# CLAUDE.md — pebble_dataconverter

Ce fichier guide Claude Code quand il **maintient** cette librairie. Pour l'**utiliser** depuis un projet, voir le skill [`skills/pebble-dataconverter/`](skills/pebble-dataconverter/SKILL.md).

## Rôle

`sopheos/pebble_dataconverter`, namespace `Pebble\DataConverter\`, PHP >= 8.1, aucune dépendance runtime. La lib fournit des converters invocables et chaînables (`one()`, `many()`) pour transformer des données structurées :
- décodage/encodage JSON et booléen ;
- conversion de dates entre formats ;
- application de règles par clé (`MapConverter`) ou par élément (`CollectionConverter`) ;
- hydratation d'objets à partir de tableaux associatifs (`HydrateConverter`).

Pas de validation, pas d'exception métier : une donnée inattendue est en général renvoyée telle quelle ou remplacée par `null`.

## Commandes

```bash
composer install
vendor/bin/phpunit            # toute la suite
vendor/bin/phpunit --filter HydrateConverterTest
```

## Carte de `src/`

| Fichier | Rôle |
|---|---|
| `ConverterInterface.php` | Contrat : `one()`, `many()`, `__invoke()` |
| `ConverterAbstract.php` | Liste de converters exécutés en séquence. Classe concrète malgré son nom |
| `PrepareConverterAbstract.php` | `__invoke()` = `prepare($input)` puis la chaîne `one()`/`many()` |
| `CreateTrait.php` | `create()` sans argument pour les converters qui n'en ont pas |
| `Helpers.php` | `parseRule()` : interface > callable > tableau (`MapConverter`) > classe/objet (`HydrateConverter`) > `null` |
| `Converter.php` | Converter vide, point de départ d'une chaîne |
| `CallbackConverter.php` | Applique un callable, sauf sur `null` |
| `CollectionConverter.php` | Applique une règle à chaque élément d'un itérable, clés conservées |
| `MapConverter.php` | Applique une règle par clé d'un tableau ou d'un `Traversable` |
| `HydrateConverter.php` | Clone un prototype et renseigne ses propriétés publiques (setters prévus mais cassés) |
| `DatetimeConverter.php` | Conversion de format de date, constantes `TS`, `ISO`, `SQL` |
| `JsonDecodeConverter.php` / `JsonEncodeConverter.php` | JSON vers tableau associatif, et inversement |
| `BooleanDecodeConverter.php` / `BooleanEncodeConverter.php` | Valeur vers `bool`, et `bool` vers `1`/`0` |

## Tests

- PHPUnit 9.5. `tests/bootstrap.php` fixe le fuseau `Europe/Paris` (les tests de dates en dépendent) et charge `tests/ressources/*.php` (classes à hydrater : `Person`, `Car`, `Model`, `Account` avec setter, `Money` avec `withX()`, `Strict` avec constructeur obligatoire).
- Un fichier de test par converter. Les classes de test n'ont pas de namespace. Les nouvelles méthodes s'appellent `testPhraseEnCamelCase` et utilisent `self::assertSame`. Les tests historiques (`testOk`, `testKo`…) sont conservés. Des bannières `// ----` séparent les sections.

## Conventions du code

Respecter le style existant, sans le « moderniser » au passage :
- pas de `declare(strict_types=1)` ;
- constantes de classe sans visibilité ;
- constructeur statique `create()` qui reprend les arguments du constructeur, retour `static` ;
- `prepare()` protégé dans chaque converter, jamais d'exception volontaire.

Une modification de comportement doit être répercutée dans `skills/pebble-dataconverter/` (SKILL.md, `references/api-reference.md`, `references/gotchas.md`) et dans le `README.md`.

## Bugs connus

Ils sont listés dans [`TODO.md`](TODO.md). Chacun est **figé par un test** annoté `// BUG:` qui vérifie le comportement *actuel*, dans la section « Known bugs » de `tests/HydrateConverterTest.php` et `tests/DatetimeConverterTest.php`.

Pour corriger un bug :
1. Corriger `src/`.
2. Réécrire le test `// BUG:` pour qu'il vérifie le comportement attendu.
3. Mettre à jour l'entrée « (bug) » de `skills/pebble-dataconverter/references/gotchas.md` et le SKILL.md.
4. Retirer l'entrée de `TODO.md` (il ne liste que ce qui reste à faire).
