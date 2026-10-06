<?php

use Pebble\DataConverter\DatetimeConverter;
use Pebble\DataConverter\MapConverter;
use PHPUnit\Framework\TestCase;

/**
 * @group map
 */
class MapConverterTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Nominal
    // -------------------------------------------------------------------------

    public function testOk()
    {
        $converter = MapConverter::create()
            ->map('name', 'ucfirst')
            ->map('birthdate', DatetimeConverter::create('d/m/Y', 'Y-m-d'));

        $actual = $converter(['name' => 'toto', 'birthdate' => '01/02/1990']);
        self::assertIsArray($actual);
        self::assertArrayHasKey('name', $actual);
        self::assertArrayHasKey('birthdate', $actual);
        self::assertSame('Toto', $actual['name']);
        self::assertSame('1990-02-01', $actual['birthdate']);
    }

    public function testRulesFromConstructor()
    {
        $converter = MapConverter::create(['name' => 'ucfirst', 'age' => 'intval']);

        self::assertSame(['name' => 'Toto', 'age' => 42], $converter(['name' => 'toto', 'age' => '42']));
    }

    public function testOtherKeysAreKept()
    {
        $actual = MapConverter::create(['name' => 'ucfirst'])(['name' => 'toto', 'other' => 'x']);

        self::assertSame(['name' => 'Toto', 'other' => 'x'], $actual);
    }

    // -------------------------------------------------------------------------
    // Edge cases
    // -------------------------------------------------------------------------

    public function testMissingKeysAreAddedAsNull()
    {
        $actual = MapConverter::create(['name' => 'ucfirst'])(['other' => 'x']);

        self::assertSame(['other' => 'x', 'name' => null], $actual);
    }

    public function testNonIterableInputGivesNull()
    {
        self::assertNull(MapConverter::create(['name' => 'ucfirst'])('toto'));
        self::assertNull(MapConverter::create(['name' => 'ucfirst'])(null));
    }

    public function testPlainObjectGivesNull()
    {
        $person = new Person;
        $person->name = 'toto';

        self::assertNull(MapConverter::create(['name' => 'ucfirst'])($person));
    }

    public function testTraversableIsConvertedToArray()
    {
        $actual = MapConverter::create(['name' => 'ucfirst'])(new ArrayObject(['name' => 'toto']));

        self::assertSame(['name' => 'Toto'], $actual);
    }

    public function testInvalidRuleIsSilentlyIgnored()
    {
        $actual = MapConverter::create(['name' => 0])(['name' => 'toto']);

        self::assertSame(['name' => 'toto'], $actual);
    }
}
