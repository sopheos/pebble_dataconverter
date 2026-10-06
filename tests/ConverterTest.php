<?php

use Pebble\DataConverter\Converter;
use Pebble\DataConverter\DatetimeConverter;
use Pebble\DataConverter\JsonDecodeConverter;
use Pebble\DataConverter\MapConverter;
use PHPUnit\Framework\TestCase;

/**
 * @group convert
 */
class ConverterTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Nominal
    // -------------------------------------------------------------------------

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

    // -------------------------------------------------------------------------
    // Edge cases
    // -------------------------------------------------------------------------

    public function testEmptyConverterReturnsInput()
    {
        self::assertSame('input', Converter::create()('input'));
    }

    public function testInvalidRuleIsSilentlyIgnored()
    {
        $converter = Converter::create()
            ->one(42)
            ->one('NoSuchClassOrFunction')
            ->one('strtoupper');

        self::assertSame('INPUT', $converter('input'));
    }

    public function testRulesRunAfterThePrepareStep()
    {
        $converter = JsonDecodeConverter::create()->one(fn($input) => count($input));

        self::assertSame(2, $converter('{"a":1,"b":2}'));
    }

    public function testManyOnNonIterableGivesEmptyArray()
    {
        self::assertSame([], Converter::create()->many('strtoupper')('input'));
    }

    // -------------------------------------------------------------------------
    // Full pipeline
    // -------------------------------------------------------------------------

    public function testNestedPipeline()
    {
        $personConverter = MapConverter::create()
            ->map('name', 'ucfirst')
            ->map('birthdate', DatetimeConverter::create('Y-m-d', 'd/m/Y'))
            ->one(Person::class);

        $converter = JsonDecodeConverter::create()
            ->one(['passengers' => Converter::create()->many($personConverter)])
            ->one(new Car);

        $actual = $converter(json_encode([
            'number' => 1651984,
            'model' => 'renault',
            'passengers' => [
                ['name' => 'toto', 'birthdate' => '1980-10-03'],
                ['name' => 'titi', 'birthdate' => '2021-08-19'],
            ],
        ]));

        self::assertInstanceOf(Car::class, $actual);
        self::assertSame(1651984, $actual->number);
        self::assertCount(2, $actual->passengers);
        self::assertInstanceOf(Person::class, $actual->passengers[0]);
        self::assertSame('Toto', $actual->passengers[0]->name);
        self::assertSame('19/08/2021', $actual->passengers[1]->birthdate);
    }
}
