<?php

namespace Pebble\DataConverter;

class Helpers
{
    public static function parseRule(mixed $rule): ?ConverterInterface
    {
        if ($rule instanceof ConverterInterface) {
            return $rule;
        } elseif (is_callable($rule)) {
            return CallbackConverter::create($rule);
        } elseif (is_array($rule)) {
            return MapConverter::create($rule);
        } elseif (is_string($rule) && class_exists($rule) || is_object($rule)) {
            return HydrateConverter::create($rule);
        }

        return null;
    }
}
