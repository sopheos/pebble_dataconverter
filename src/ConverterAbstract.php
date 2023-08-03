<?php

namespace Pebble\DataConverter;

class ConverterAbstract implements ConverterInterface
{
    private array $converters = [];

    public function one(mixed $rule): static
    {
        if (($converter = Helpers::parseRule($rule))) {
            $this->converters[] = $converter;
        }

        return $this;
    }

    public function many(mixed $rule): static
    {
        if (($converter = Helpers::parseRule($rule))) {
            $this->converters[] = CollectionConverter::create($converter);
        }

        return $this;
    }

    public function __invoke(mixed $input): mixed
    {
        foreach ($this->converters as $converter) {
            $input = $converter($input);
        }

        return $input;
    }
}
