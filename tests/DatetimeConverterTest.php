<?php

use Pebble\DataConverter\DatetimeConverter;
use PHPUnit\Framework\TestCase;

/**
 * @group datetime
 */
class DatetimeConverterTest extends TestCase
{
    public function testOk()
    {
        $formats = [
            'U' => 631148400,
            'c' => "1990-01-01T00:00:00+01:00",
            'Y-m-d H:i:s' => "1990-01-01 00:00:00",
            'd/m/Y H:i:s' => "01/01/1990 00:00:00",
        ];

        foreach ($formats as $from => $input) {
            foreach ($formats as $to => $expected) {
                $converter = DatetimeConverter::create($from, $to);
                $actual = $converter($input);
                self::assertSame($expected, $actual, "$from => $to");
            }
        }
    }
}
