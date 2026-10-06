<?php

use Pebble\DataConverter\HydrateConverter;
use PHPUnit\Framework\TestCase;

/**
 * @group hydrate
 */
class HydrateConverterTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Nominal
    // -------------------------------------------------------------------------

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

    public function testPublicPropertiesAreHydrated()
    {
        $actual = HydrateConverter::create(Person::class)(['name' => 'Toto', 'birthdate' => '2000-04-01']);

        self::assertSame('Toto', $actual->name);
        self::assertSame('2000-04-01', $actual->birthdate);
    }

    public function testEachCallReturnsAFreshCloneOfThePrototype()
    {
        $prototype = new Person;
        $prototype->name = 'default';
        $converter = HydrateConverter::create($prototype);

        $a = $converter(['birthdate' => '2000-04-01']);
        $b = $converter(['name' => 'Toto']);

        self::assertNotSame($a, $b);
        self::assertNotSame($prototype, $a);
        self::assertSame('default', $a->name);
        self::assertSame('Toto', $b->name);
        self::assertSame('default', $prototype->name);
        self::assertNull($prototype->birthdate);
    }

    // -------------------------------------------------------------------------
    // Edge cases
    // -------------------------------------------------------------------------

    public function testUnknownKeysAreIgnored()
    {
        $actual = HydrateConverter::create(Person::class)(['name' => 'Toto', 'unknown' => 1]);

        self::assertSame('Toto', $actual->name);
        self::assertFalse(property_exists($actual, 'unknown'));
    }

    public function testPrivatePropertiesAreNotHydrated()
    {
        $actual = HydrateConverter::create(Account::class)(['calls' => 5]);

        self::assertSame(5, $actual->calls);
        self::assertNull($actual->email());
    }

    public function testNonArrayInputIsReturnedUnchanged()
    {
        $converter = HydrateConverter::create(Person::class);
        $object = new stdClass;

        self::assertSame('Toto', $converter('Toto'));
        self::assertNull($converter(null));
        self::assertSame($object, $converter($object));
    }

    public function testUnknownClassNameThrows()
    {
        $this->expectException(InvalidArgumentException::class);
        HydrateConverter::create('NoSuchClass');
    }

    public function testClassIsInstantiatedWithoutConstructorArguments()
    {
        $this->expectException(ArgumentCountError::class);
        HydrateConverter::create(Strict::class);
    }

    public function testObjectWithConstructorArgumentsCanBeUsedAsPrototype()
    {
        $actual = HydrateConverter::create(new Strict('a'))(['value' => 'b']);

        self::assertSame('b', $actual->value);
    }

    // -------------------------------------------------------------------------
    // Known bugs (see TODO.md)
    // -------------------------------------------------------------------------

    public function testSetterIsNeverCalled()
    {
        // BUG: hasMethod() looks in $this->properties instead of $this->methods,
        // so setEmail() is never called and the private property stays null.
        $actual = HydrateConverter::create(Account::class)(['email' => 'toto@example.com']);

        self::assertSame(0, $actual->calls);
        self::assertNull($actual->email());
    }

    public function testImmutableSetterIsNeverCalled()
    {
        // BUG: same cause, withAmount() is never called.
        $actual = HydrateConverter::create(Money::class)(['amount' => 10]);

        self::assertInstanceOf(Money::class, $actual);
        self::assertNull($actual->amount());
    }
}
