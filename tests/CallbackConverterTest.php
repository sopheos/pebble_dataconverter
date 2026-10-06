<?php

use Pebble\DataConverter\CallbackConverter;
use PHPUnit\Framework\TestCase;

/**
 * @group callback
 */
class CallbackConverterTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Nominal
    // -------------------------------------------------------------------------

    public function testOk()
    {
        $converter = CallbackConverter::create(function ($input) {
            return $input && is_string($input) ? mb_strtoupper($input) : null;
        });

        $actual = $converter('input');

        self::assertIsString($actual);
        self::assertSame('INPUT', $actual);
    }

    // -------------------------------------------------------------------------
    // Edge cases
    // -------------------------------------------------------------------------

    public function testNullIsNotPassedToTheCallback()
    {
        $calls = 0;
        $converter = CallbackConverter::create(function ($input) use (&$calls) {
            $calls++;
            return 'called';
        });

        self::assertNull($converter(null));
        self::assertSame(0, $calls);
        self::assertSame('called', $converter(''));
        self::assertSame(1, $calls);
    }

    public function testFunctionNameIsAccepted()
    {
        self::assertSame('ABC', CallbackConverter::create('strtoupper')('abc'));
    }
}
