<?php

use Pebble\DataConverter\JsonEncodeConverter;
use PHPUnit\Framework\TestCase;

/**
 * @group json
 */
class JsonEncodeConverterTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Nominal
    // -------------------------------------------------------------------------

    public function testArray()
    {
        $converter = JsonEncodeConverter::create();
        $actual = $converter(['name' => 'Toto']);
        self::assertIsString($actual);
        self::assertJson($actual);
    }

    public function testObject()
    {
        $person = new Person;
        $person->name = 'Toto';

        $converter = JsonEncodeConverter::create();
        $actual = $converter($person);
        self::assertIsString($actual);
        self::assertJson($actual);
    }

    public function testKo()
    {
        $converter = JsonEncodeConverter::create();
        $actual = $converter('Yolo !');
        self::assertNull($actual);
    }

    // -------------------------------------------------------------------------
    // Edge cases
    // -------------------------------------------------------------------------

    public function testScalarsAndNullGiveNull()
    {
        $converter = JsonEncodeConverter::create();

        self::assertNull($converter(null));
        self::assertNull($converter(5));
        self::assertNull($converter(true));
    }

    public function testOnlyPublicPropertiesAreEncoded()
    {
        self::assertSame('{"calls":0}', JsonEncodeConverter::create()(new Account));
    }
}
