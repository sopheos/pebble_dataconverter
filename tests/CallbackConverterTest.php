<?php

use Pebble\DataConverter\CallbackConverter;
use PHPUnit\Framework\TestCase;

/**
 * @group callback
 */
class CallbackConverterTest extends TestCase
{
    public function testOk()
    {
        $converter = CallbackConverter::create(function ($input) {
            return $input && is_string($input) ? mb_strtoupper($input) : null;
        });

        $actual = $converter('input');

        self::assertIsString($actual);
        self::assertSame('INPUT', $actual);
    }
}
