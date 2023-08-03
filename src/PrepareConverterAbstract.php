<?php

namespace Pebble\DataConverter;

abstract class PrepareConverterAbstract extends ConverterAbstract
{
    abstract protected function prepare(mixed $input): mixed;

    /**
     * @param mixed $input
     * @return mixed
     */
    public function __invoke(mixed $input): mixed
    {
        return parent::__invoke($this->prepare($input));
    }
}
