# pebble-dataconverter — gotchas

Things the method names don't tell you, grouped by class. Every item below is pinned by a test in `tests/`. Items marked **(bug)** are listed in the package's `TODO.md` and may be fixed in a later version. Check the test of the same name in `vendor/sopheos/pebble_dataconverter/tests/` to see the current behavior.

## Rules and chaining

- **Invalid rules are silently ignored.** `one(42)`, `one('NoSuchClassOrFunction')` or `map('k', 0)` add nothing and raise nothing. (`ConverterTest::testInvalidRuleIsSilentlyIgnored`, `MapConverterTest::testInvalidRuleIsSilentlyIgnored`)
- **A function name is a callable rule.** `'strtoupper'` becomes a `CallbackConverter`. (`HelperTest::testFunctionNameIsACallback`)
- **Rules run after the converter's own step.** `JsonDecodeConverter::create()->one($fn)` passes the decoded array to `$fn`. (`ConverterTest::testRulesRunAfterThePrepareStep`)
- **`many()` on a non-iterable gives `[]`.** (`ConverterTest::testManyOnNonIterableGivesEmptyArray`)

## CallbackConverter

- **`null` is never passed to the callback.** The converter returns `null` directly. `''`, `0` and `false` are passed. (`CallbackConverterTest::testNullIsNotPassedToTheCallback`)

## CollectionConverter

- **Keys are preserved**, string or integer. (`CollectionConverterTest::testKeysArePreserved`)
- **A `Traversable` is converted to an array.** (`CollectionConverterTest::testTraversableIsConvertedToArray`)
- **Non-iterable input, including `null`, gives `[]`.** (`CollectionConverterTest::testKo`, `::testNullGivesEmptyArray`)

## MapConverter

- **A plain object gives `null`.** Only arrays and `Traversable` are accepted, so map keys *before* hydrating. (`MapConverterTest::testPlainObjectGivesNull`)
- **A scalar or `null` gives `null`.** (`MapConverterTest::testNonIterableInputGivesNull`)
- **Missing keys are added** with the rule's result on `null` (usually `null`). (`MapConverterTest::testMissingKeysAreAddedAsNull`)
- **Unmapped keys are kept.** (`MapConverterTest::testOtherKeysAreKept`)

## HydrateConverter

- **(bug) `setX()` setters are never called.** `hasMethod()` looks in the property list, so a key without a matching public property is dropped. (`HydrateConverterTest::testSetterIsNeverCalled`)
- **(bug) `withX()` immutable setters are never called either.** (`HydrateConverterTest::testImmutableSetterIsNeverCalled`)
- **Only public non-static properties are hydrated.** Private ones keep their default. (`HydrateConverterTest::testPrivatePropertiesAreNotHydrated`)
- **Unknown keys are ignored**, no dynamic property is created. (`HydrateConverterTest::testUnknownKeysAreIgnored`)
- **Each call returns a fresh clone of the prototype**; the prototype's own values serve as defaults and are never modified. (`HydrateConverterTest::testEachCallReturnsAFreshCloneOfThePrototype`)
- **Non-array input is returned unchanged**, including `stdClass` and `null`. (`HydrateConverterTest::testNonArrayInputIsReturnedUnchanged`)
- **A class name is instantiated with no arguments**, at construction time: a required constructor parameter throws `ArgumentCountError`. Pass a prebuilt object instead. (`HydrateConverterTest::testClassIsInstantiatedWithoutConstructorArguments`, `::testObjectWithConstructorArgumentsCanBeUsedAsPrototype`)
- **An unknown class name throws `InvalidArgumentException`** when used directly. Through `one()`/`map()` it is silently ignored instead. (`HydrateConverterTest::testUnknownClassNameThrows`)

## DatetimeConverter

- **(bug) An unparsable date with a custom format is a fatal `Error`** ("Call to a member function getTimestamp() on false"). (`DatetimeConverterTest::testUnparsableCustomFormatIsFatal`)
- **(bug) An unparsable `ISO`/`SQL` date gives `false` (to `TS`) or the epoch (`01/01/1970`) for other formats.** (`DatetimeConverterTest::testUnparsableIsoOrSqlInputGivesFalseOrEpoch`)
- **Fields missing from a custom format take the current time.** `'d/m/Y'` to `TS` returns today's time of day on that date. Prefix with `!` (`'!d/m/Y'`) to zero them. (`DatetimeConverterTest::testMissingFieldsAreTakenFromTheCurrentTime`, `::testBangPrefixResetsMissingFields`)
- **Same `$from` and `$to` returns the input unchanged**, valid or not, with its original type. (`DatetimeConverterTest::testSameFormatReturnsInputUnchanged`, `::testDefaultsAreTimestampToTimestamp`)
- **A non-numeric timestamp becomes the epoch.** (`DatetimeConverterTest::testNonNumericTimestampBecomesEpoch`)
- **`null` stays `null`.** (`DatetimeConverterTest::testNullIsReturnedAsIs`)

## JSON

- **`JsonDecodeConverter` passes non-strings through** (arrays, `null`, numbers). (`JsonDecodeConverterTest::testNonStringPassesThrough`)
- **Invalid JSON or a JSON scalar gives `null`.** (`JsonDecodeConverterTest::testKo`, `::testInvalidJsonGivesNull`)
- **JSON objects are always decoded as associative arrays.** (`JsonDecodeConverterTest::testObjectsAreDecodedAsArrays`)
- **`JsonEncodeConverter` gives `null` for scalars and `null`.** (`JsonEncodeConverterTest::testScalarsAndNullGiveNull`, `::testKo`)
- **Only public properties of an object are encoded.** (`JsonEncodeConverterTest::testOnlyPublicPropertiesAreEncoded`)

## Booleans

- **`BooleanDecodeConverter` casts strings to `int` first**: `'true'` and `'yes'` give `false`, `'2'` gives `true`. (`BooleanDecodeConverterTest::testNonNumericStringsAreFalse`)
- **`null` and `''` decode to `null`**, not `false`. (`BooleanDecodeConverterTest::testNull`)
- **`BooleanEncodeConverter` uses PHP truthiness**: `'no'` gives `1`, `'0'` and `''` give `0`, `null` stays `null`. (`BooleanEncodeConverterTest::testUsesTruthiness`, `::testNull`)
