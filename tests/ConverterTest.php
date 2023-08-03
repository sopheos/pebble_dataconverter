<?php

use Pebble\DataConverter\Converter;
use PHPUnit\Framework\TestCase;

/**
 * @group convert
 */
class ConverterTest extends TestCase
{
    public function testOne()
    {
        $square = function ($input) {
            if (!is_numeric($input)) return 0;
            return $input * $input;
        };

        $double = function ($input) {
            if (!is_numeric($input)) return 0;
            return $input * 2;
        };

        $converter = Converter::create()
            ->one($square)
            ->one($double);

        $actual = $converter(3);

        self::assertIsNumeric($actual);
        self::assertSame(18, $actual);
    }

    public function testMany()
    {
        $square = function ($input) {
            if (!is_numeric($input)) return 0;
            return $input * $input;
        };

        $double = function ($input) {
            if (!is_numeric($input)) return 0;
            return $input * 2;
        };

        $converter = Converter::create()
            ->many($square)
            ->many($double);

        $actual = $converter([1, 2, 3, 5, 8]);

        self::assertIsArray($actual);
        self::assertCount(5, $actual);
        self::assertSame(2, $actual[0]);
        self::assertSame(8, $actual[1]);
        self::assertSame(18, $actual[2]);
        self::assertSame(50, $actual[3]);
        self::assertSame(128, $actual[4]);
    }
}
