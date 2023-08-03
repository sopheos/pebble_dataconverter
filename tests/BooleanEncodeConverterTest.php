<?php

use Pebble\DataConverter\BooleanEncodeConverter;
use PHPUnit\Framework\TestCase;

/**
 * @group boolean
 */
class BooleanEncodeConverterTest extends TestCase
{
    public function testNull()
    {
        $converter = BooleanEncodeConverter::create();

        $actual = $converter(null);
        self::assertNull($actual);
    }

    public function testTrue()
    {
        $converter = BooleanEncodeConverter::create();

        $actual = $converter(true);
        self::assertSame(1, $actual);
    }

    public function testFalse()
    {
        $converter = BooleanEncodeConverter::create();

        $actual = $converter(false);
        self::assertSame(0, $actual);
    }
}
