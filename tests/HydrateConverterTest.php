<?php

use Pebble\DataConverter\HydrateConverter;
use PHPUnit\Framework\TestCase;

/**
 * @group hydrate
 */
class HydrateConverterTest extends TestCase
{
    public function testClassname()
    {
        $converter = HydrateConverter::create(Person::class);
        $actual = $converter(['name' => 'Toto', 'birthdate' => '2000-04-01']);
        self::assertIsObject($actual);
        self::assertInstanceOf(Person::class, $actual);
    }

    public function testObject()
    {
        $converter = HydrateConverter::create(new Person);
        $actual = $converter(['name' => 'Toto', 'birthdate' => '2000-04-01']);
        self::assertIsObject($actual);
        self::assertInstanceOf(Person::class, $actual);
    }
}
