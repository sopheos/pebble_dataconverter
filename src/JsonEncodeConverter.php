<?php

namespace Pebble\DataConverter;

class JsonEncodeConverter extends PrepareConverterAbstract
{
    use CreateTrait;

    protected function prepare(mixed $input): mixed
    {
        if (is_array($input) || is_object($input)) {
            return json_encode($input);
        }

        return null;
    }
}
