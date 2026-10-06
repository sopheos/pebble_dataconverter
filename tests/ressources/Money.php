<?php

/**
 * Hydration through an immutable setter (`withX()` returns a copy).
 */
class Money
{
    private $amount = null;

    public function withAmount($amount)
    {
        $copy = clone $this;
        $copy->amount = $amount;
        return $copy;
    }

    public function amount()
    {
        return $this->amount;
    }
}
