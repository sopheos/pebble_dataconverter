<?php

namespace Pebble\DataConverter;

class BooleanEncodeConverter extends PrepareConverterAbstract
{
    use CreateTrait;

    protected function prepare(mixed $input): mixed
    {
        if ($input === null) {
            return $input;
        }

        return $input ? 1 : 0;
    }
}
