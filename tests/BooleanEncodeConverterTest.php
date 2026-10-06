<?php

use Pebble\DataConverter\BooleanEncodeConverter;
use PHPUnit\Framework\TestCase;

/**
 * @group boolean
 */
class BooleanEncodeConverterTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Nominal
    // -------------------------------------------------------------------------

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

    // -------------------------------------------------------------------------
    // Edge cases
    // -------------------------------------------------------------------------

    public function testUsesTruthiness()
    {
        $converter = BooleanEncodeConverter::create();

        self::assertSame(1, $converter('no'));
        self::assertSame(0, $converter('0'));
        self::assertSame(0, $converter(''));
    }
}
