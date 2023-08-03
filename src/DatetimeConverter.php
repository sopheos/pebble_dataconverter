<?php

namespace Pebble\DataConverter;

use DateTime;

class DatetimeConverter extends PrepareConverterAbstract
{
    const TS = 'U';
    const ISO = 'c';
    const SQL = 'Y-m-d H:i:s';

    private string $from;
    private string $to;

    /**
     * @param string $from
     * @param string $to
     */
    public function __construct(string $from = self::TS, string $to = self::TS)
    {
        $this->from = $from;
        $this->to = $to;
    }

    /**
     * @param string $from
     * @param string $to
     */
    public static function create(string $from = self::TS, string $to = self::TS): static
    {
        return new static($from, $to);
    }

    /**
     * @param mixed $value
     * @return mixed
     */
    protected function prepare(mixed $input): mixed
    {
        if ($input === null) {
            return null;
        }

        if ($this->from === $this->to) {
            return $input;
        }

        if ($this->from === self::TS) {
            return date($this->to, (int) $input);
        }

        $ts = $this->isFast() ?
            strtotime($input) :
            DateTime::createFromFormat($this->from, $input)->getTimestamp();

        if ($this->to === self::TS) {
            return $ts;
        }

        return date($this->to, $ts);
    }

    private function isFast(): bool
    {
        return $this->from === self::ISO
            || $this->from === self::SQL
            || $this->from === DateTime::ISO8601;
    }
}
