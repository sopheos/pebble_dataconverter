<?php

namespace Pebble\DataConverter;

class CallbackConverter extends PrepareConverterAbstract
{
    /**
     * @var callable
     */
    private $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback;
    }

    public static function create(callable $callback): static
    {
        return new static($callback);
    }

    /**
     * @param mixed $input
     * @return mixed
     */
    protected function prepare(mixed $input): mixed
    {
        if ($input === null) {
            return null;
        }

        return call_user_func($this->callback, $input);
    }
}
