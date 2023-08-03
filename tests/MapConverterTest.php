<?php

use Pebble\DataConverter\DatetimeConverter;
use Pebble\DataConverter\MapConverter;
use PHPUnit\Framework\TestCase;

/**
 * @group map
 */
class MapConverterTest extends TestCase
{
    public function testOk()
    {
        $converter = MapConverter::create()
            ->map('name', 'ucfirst')
            ->map('birthdate', DatetimeConverter::create('d/m/Y', 'Y-m-d'));

        $actual = $converter(['name' => 'toto', 'birthdate' => '01/02/1990']);
        self::assertIsArray($actual);
        self::assertArrayHasKey('name', $actual);
        self::assertArrayHasKey('birthdate', $actual);
        self::assertSame('Toto', $actual['name']);
        self::assertSame('1990-02-01', $actual['birthdate']);
    }
}
