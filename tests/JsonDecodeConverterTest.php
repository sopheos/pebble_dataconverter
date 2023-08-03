<?php

use Pebble\DataConverter\JsonDecodeConverter;
use PHPUnit\Framework\TestCase;

/**
 * @group json
 */
class JsonDecodeConverterTest extends TestCase
{
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
}
