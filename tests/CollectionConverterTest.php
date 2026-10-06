<?php

use Pebble\DataConverter\CollectionConverter;
use PHPUnit\Framework\TestCase;

/**
 * @group collection
 */
class CollectionConverterTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Nominal
    // -------------------------------------------------------------------------

    public function testOk()
    {
        $converter = self::getConverter();
        $actual = $converter(['one', 'two', 'three']);

        self::assertIsArray($actual);
        self::assertCount(3, $actual);
        self::assertSame('ONE', $actual[0]);
        self::assertSame('TWO', $actual[1]);
        self::assertSame('THREE', $actual[2]);
    }

    public function testKo()
    {
        $converter = self::getConverter();
        $actual = $converter('input');

        self::assertIsArray($actual);
        self::assertEmpty($actual);
    }

    private static function getConverter(): CollectionConverter
    {
        return CollectionConverter::create(function ($input) {
            return $input && is_string($input) ? mb_strtoupper($input) : null;
        });
    }

    // -------------------------------------------------------------------------
    // Edge cases
    // -------------------------------------------------------------------------

    public function testKeysArePreserved()
    {
        $actual = self::getConverter()(['a' => 'one', 5 => 'two']);

        self::assertSame(['a' => 'ONE', 5 => 'TWO'], $actual);
    }

    public function testTraversableIsConvertedToArray()
    {
        $actual = self::getConverter()(new ArrayIterator(['x' => 'one']));

        self::assertSame(['x' => 'ONE'], $actual);
    }

    public function testNullGivesEmptyArray()
    {
        self::assertSame([], self::getConverter()(null));
    }

    public function testRuleCanBeAMapOrAClass()
    {
        $converter = CollectionConverter::create(Person::class);
        $actual = $converter([['name' => 'Toto'], ['name' => 'Titi']]);

        self::assertCount(2, $actual);
        self::assertInstanceOf(Person::class, $actual[1]);
        self::assertSame('Titi', $actual[1]->name);
    }
}
