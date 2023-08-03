<?php

namespace Pebble\DataConverter;

trait CreateTrait
{
    public static function create(): static
    {
        return new static();
    }
}
