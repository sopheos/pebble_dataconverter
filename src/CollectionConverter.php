<?php

namespace Pebble\DataConverter;

class CollectionConverter extends PrepareConverterAbstract
{
    private ConverterInterface $converter;

    public function __construct(mixed $rule)
    {
        $this->converter = Helpers::parseRule($rule);
    }

    public static function create(mixed $rule): static
    {
        return new static($rule);
    }

    protected function prepare(mixed $input): mixed
    {
        if (!is_iterable($input)) {
            return [];
        }

        if (!is_array($input)) {
            $input = iterator_to_array($input, true);
        }

        foreach ($input as $key => $value) {
            $input[$key] = $this->converter->__invoke($value);
        }

        return $input;
    }
}
