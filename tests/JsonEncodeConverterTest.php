<?php

use Pebble\DataConverter\JsonEncodeConverter;
use PHPUnit\Framework\TestCase;

/**
 * @group json
 */
class JsonEncodeConverterTest extends TestCase
{
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
}
