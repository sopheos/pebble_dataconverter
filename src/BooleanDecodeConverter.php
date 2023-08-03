<?php

namespace Pebble\DataConverter;

class BooleanDecodeConverter extends PrepareConverterAbstract
{
    use CreateTrait;

    protected function prepare(mixed $input): mixed
    {
        if ($input === null || $input === '') {
            return null;
        }

        if (is_string($input)) {
            $input = (int) $input;
        }

        return $input ? true : false;
    }
}
