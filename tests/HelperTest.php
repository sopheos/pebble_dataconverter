<?php

use Pebble\DataConverter\CallbackConverter;
use Pebble\DataConverter\Converter;
use Pebble\DataConverter\Helpers;
use Pebble\DataConverter\HydrateConverter;
use Pebble\DataConverter\MapConverter;
use PHPUnit\Framework\TestCase;

/**
 * @group helper
 */
class HelperTest extends TestCase
{
    public function testInterface()
    {
        $actual = Helpers::parseRule(Converter::create());
        self::assertInstanceOf(Converter::class, $actual);
    }

    public function testCallable()
    {
        $actual = Helpers::parseRule(function ($input) {
            return $input;
        });

        self::assertInstanceOf(CallbackConverter::class, $actual);
    }

    public function testArray()
    {
        $actual = Helpers::parseRule([
            'date' => function ($input) {
                return $input;
            }
        ]);

        self::assertInstanceOf(MapConverter::class, $actual);
    }

    public function testClassname()
    {
        $actual = Helpers::parseRule(Person::class);
        self::assertInstanceOf(HydrateConverter::class, $actual);
    }

    public function testObject()
    {
        $actual = Helpers::parseRule(new Person);
        self::assertInstanceOf(HydrateConverter::class, $actual);
    }

    public function testKo()
    {
        $actual = Helpers::parseRule(0);
        self::assertNull($actual);
    }
}
