<?php

namespace Pebble\DataConverter;

class JsonDecodeConverter extends PrepareConverterAbstract
{
    use CreateTrait;

    protected function prepare(mixed $input): mixed
    {
        if (!is_string($input)) {
            return $input;
        }

        $input = json_decode($input, true);

        if (!is_array($input)) {
            return null;
        }

        return $input;
    }
}
