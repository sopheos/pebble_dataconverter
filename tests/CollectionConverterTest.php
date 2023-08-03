<?php

use Pebble\DataConverter\CollectionConverter;
use PHPUnit\Framework\TestCase;

/**
 * @group collection
 */
class CollectionConverterTest extends TestCase
{
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
}
