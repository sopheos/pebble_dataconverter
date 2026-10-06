<?php

use Pebble\DataConverter\BooleanDecodeConverter;
use PHPUnit\Framework\TestCase;

/**
 * @group boolean
 */
class BooleanDecodeConverterTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Nominal
    // -------------------------------------------------------------------------

    public function testNull()
    {
        $converter = BooleanDecodeConverter::create();

        $actual = $converter(null);
        self::assertNull($actual);

        $actual = $converter('');
        self::assertNull($actual);
    }

    public function testTrue()
    {
        $converter = BooleanDecodeConverter::create();

        $actual = $converter(1);
        self::assertTrue($actual);

        $actual = $converter('1');
        self::assertTrue($actual);
    }

    public function testFalse()
    {
        $converter = BooleanDecodeConverter::create();

        $actual = $converter(0);
        self::assertFalse($actual);

        $actual = $converter('0');
        self::assertFalse($actual);
    }

    // -------------------------------------------------------------------------
    // Edge cases
    // -------------------------------------------------------------------------

    public function testNonNumericStringsAreFalse()
    {
        $converter = BooleanDecodeConverter::create();

        self::assertFalse($converter('true'));
        self::assertFalse($converter('yes'));
        self::assertTrue($converter('2'));
    }

    public function testNonStringValuesUseTruthiness()
    {
        $converter = BooleanDecodeConverter::create();

        self::assertFalse($converter(false));
        self::assertTrue($converter([1]));
        self::assertFalse($converter([]));
    }
}
