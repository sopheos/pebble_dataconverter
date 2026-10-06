# TODO — pebble_dataconverter

Problèmes restant à traiter, détectés lors de l'audit du 2026-10-06. Le code `src/` n'a **pas** été modifié. Chaque bug est figé par un test qui vérifie le comportement actuel : il faut l'adapter au moment de la correction.

## Bugs

- [ ] **Les setters `setX()` et `withX()` ne sont jamais utilisés par l'hydratation.** `src/HydrateConverter.php:81`.
  - `hasMethod()` teste `$this->properties` au lieu de `$this->methods`. Une clé sans propriété publique correspondante est donc ignorée, même si la classe expose `setEmail()` ou `withAmount()`. Les propriétés privées alimentées par setter restent à leur valeur par défaut, sans erreur.
  - Correctif : `return isset($this->methods[$name]);`.
  - Test : `tests/HydrateConverterTest.php::testSetterIsNeverCalled` et `::testImmutableSetterIsNeverCalled`.
- [ ] **Une date non parsable avec un format personnalisé provoque une erreur fatale.** `src/DatetimeConverter.php:55`.
  - `DateTime::createFromFormat()` renvoie `false` et `->getTimestamp()` est appelé dessus : `Error: Call to a member function getTimestamp() on false`. Une chaîne vide ou une saisie utilisateur mal formée suffit à planter toute la conversion.
  - Correctif : tester le retour de `createFromFormat()` et renvoyer `null` en cas d'échec.
  - Test : `tests/DatetimeConverterTest.php::testUnparsableCustomFormatIsFatal`.
- [ ] **Une date non parsable aux formats `ISO`/`SQL` donne `false` ou le 1er janvier 1970.** `src/DatetimeConverter.php:54, 57-61`.
  - `strtotime()` renvoie `false`. Vers `TS`, `false` est renvoyé tel quel. Vers un autre format, `date($to, false)` formate l'epoch (`01/01/1970`), une valeur plausible qui passe inaperçue.
  - Correctif : renvoyer `null` quand `strtotime()` échoue, comme pour le bug précédent.
  - Test : `tests/DatetimeConverterTest.php::testUnparsableIsoOrSqlInputGivesFalseOrEpoch`.

## Dette / qualité

- [ ] `src/HydrateConverter.php:12, 49-53` : `$methods` est rempli mais jamais lu (conséquence du bug `hasMethod()`).
- [ ] `src/DatetimeConverter.php:50` : un timestamp non numérique (`'abc'`) est casté en `0` et donne l'epoch, sans erreur.
- [ ] `src/DatetimeConverter.php:55` : sans `!` en tête du format, `createFromFormat()` complète les champs absents avec l'heure courante. `'d/m/Y'` vers `TS` dépend donc de l'heure d'exécution. À documenter (fait) ou à forcer.
- [ ] `src/MapConverter.php:31` : un objet simple (non `Traversable`) donne `null`, alors que l'ancien README annonçait la prise en charge des objets.
- [ ] `src/BooleanDecodeConverter.php:16` : une chaîne est castée en entier, donc `'true'` et `'yes'` donnent `false`. À décider : comportement voulu ou à élargir.
- [ ] `src/CollectionConverter.php:22` et `src/MapConverter.php:32` : une donnée non itérable donne `[]` dans un cas et `null` dans l'autre.
- [ ] `src/ConverterAbstract.php:5` : la classe s'appelle `…Abstract` mais n'est pas `abstract`.
- [ ] `src/DatetimeConverter.php:36` : le docblock documente `$value` au lieu de `$input`.
- [ ] `src/Helpers.php:15` : `is_string($rule) && class_exists($rule) || is_object($rule)` sans parenthèses, correct mais peu lisible.
- [ ] `src/ConverterAbstract.php`, `src/MapConverter.php` : une règle invalide est ignorée silencieusement, ce qui masque les fautes de frappe (nom de classe ou de fonction).
