<?php

namespace Pebble\DataConverter;

class MapConverter extends PrepareConverterAbstract
{
    private array $map = [];

    public function __construct(array $rules = [])
    {
        foreach ($rules as $key => $rule) {
            $this->map($key, $rule);
        }
    }

    public static function create(array $rules = []): static
    {
        return new static($rules);
    }

    public function map(string $key, mixed $rule): static
    {
        if (($converter = Helpers::parseRule($rule))) {
            $this->map[$key] = $converter;
        }
        return $this;
    }

    protected function prepare(mixed $input): mixed
    {
        if (!is_iterable($input)) {
            return null;
        }

        if (!is_array($input)) {
            $input = iterator_to_array($input, true);
        }

        foreach ($this->map as $key => $convert) {
            $input[$key] = $convert($input[$key] ?? null);
        }

        return $input;
    }
}
