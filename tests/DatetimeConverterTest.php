<?php

use Pebble\DataConverter\DatetimeConverter;
use PHPUnit\Framework\TestCase;

/**
 * @group datetime
 */
class DatetimeConverterTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Nominal
    // -------------------------------------------------------------------------

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

    public function testDefaultsAreTimestampToTimestamp()
    {
        self::assertSame(631148400, DatetimeConverter::create()(631148400));
        self::assertSame('631148400', DatetimeConverter::create()('631148400'));
    }

    public function testNullIsReturnedAsIs()
    {
        self::assertNull(DatetimeConverter::create('d/m/Y', 'Y-m-d')(null));
    }

    // -------------------------------------------------------------------------
    // Edge cases
    // -------------------------------------------------------------------------

    public function testSameFormatReturnsInputUnchanged()
    {
        self::assertSame('not a date', DatetimeConverter::create('Y-m-d', 'Y-m-d')('not a date'));
    }

    public function testNonNumericTimestampBecomesEpoch()
    {
        self::assertSame('1970-01-01', DatetimeConverter::create(DatetimeConverter::TS, 'Y-m-d')('abc'));
    }

    public function testMissingFieldsAreTakenFromTheCurrentTime()
    {
        $ts = DatetimeConverter::create('d/m/Y', 'U')('01/02/1990');
        $secondsSinceMidnight = $ts - mktime(0, 0, 0, 2, 1, 1990);
        $nowSinceMidnight = time() - mktime(0, 0, 0);

        self::assertEqualsWithDelta($nowSinceMidnight, $secondsSinceMidnight, 5);
    }

    public function testBangPrefixResetsMissingFields()
    {
        $actual = DatetimeConverter::create('!d/m/Y', DatetimeConverter::SQL)('01/02/1990');

        self::assertSame('1990-02-01 00:00:00', $actual);
    }

    // -------------------------------------------------------------------------
    // Known bugs (see TODO.md)
    // -------------------------------------------------------------------------

    public function testUnparsableCustomFormatIsFatal()
    {
        // BUG: createFromFormat() returns false and ->getTimestamp() is called on it.
        $this->expectException(Error::class);
        $this->expectExceptionMessage('getTimestamp() on false');

        DatetimeConverter::create('d/m/Y', 'Y-m-d')('garbage');
    }

    public function testUnparsableIsoOrSqlInputGivesFalseOrEpoch()
    {
        // BUG: strtotime() returns false, which is returned as-is or formatted as the epoch.
        self::assertFalse(DatetimeConverter::create(DatetimeConverter::SQL, DatetimeConverter::TS)('garbage'));
        self::assertFalse(DatetimeConverter::create(DatetimeConverter::ISO, DatetimeConverter::TS)(''));
        self::assertSame('01/01/1970', DatetimeConverter::create(DatetimeConverter::SQL, 'd/m/Y')('garbage'));
    }
}
