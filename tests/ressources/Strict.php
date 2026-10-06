<?php

/**
 * A class that cannot be instantiated without arguments.
 */
class Strict
{
    public function __construct(public $value)
    {
    }
}
