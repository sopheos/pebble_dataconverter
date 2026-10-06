<?php

use Pebble\DataConverter\JsonDecodeConverter;
use PHPUnit\Framework\TestCase;

/**
 * @group json
 */
class JsonDecodeConverterTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Nominal
    // -------------------------------------------------------------------------

    public function testOk()
    {
        $converter = JsonDecodeConverter::create();
        $actual = $converter(json_encode(['name' => 'Toto']));
        self::assertIsArray($actual);
        self::assertArrayHasKey('name', $actual);
    }

    public function testKo()
    {
        $converter = JsonDecodeConverter::create();
        $actual = $converter(json_encode("test"));
        self::assertNull($actual);
    }

    // -------------------------------------------------------------------------
    // Edge cases
    // -------------------------------------------------------------------------

    public function testNonStringPassesThrough()
    {
        $converter = JsonDecodeConverter::create();

        self::assertSame(['a' => 1], $converter(['a' => 1]));
        self::assertNull($converter(null));
        self::assertSame(5, $converter(5));
    }

    public function testInvalidJsonGivesNull()
    {
        self::assertNull(JsonDecodeConverter::create()('{invalid'));
    }

    public function testObjectsAreDecodedAsArrays()
    {
        self::assertSame(['a' => ['b' => 1]], JsonDecodeConverter::create()('{"a":{"b":1}}'));
        self::assertSame([], JsonDecodeConverter::create()('[]'));
    }
}
