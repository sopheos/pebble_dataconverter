<?php

namespace Pebble\DataConverter;

interface ConverterInterface
{
    /**
     * Add rule for a data
     *
     * @param mixed $rule
     * @return static
     */
    public function one(mixed $rule): static;


    /**
     * Add rule for a collection of data
     *
     * @param mixed $rule
     * @return static
     */
    public function many(mixed $rule): static;


    /**
     * Execute rule
     *
     * @param mixed $input
     * @return mixed
     */
    public function __invoke(mixed $input): mixed;
}
