<?php

/**
 * Hydration through a setter: the property is private.
 */
class Account
{
    private $email = null;
    public $calls = 0;

    public function setEmail($email)
    {
        $this->calls++;
        $this->email = $email;
    }

    public function email()
    {
        return $this->email;
    }
}
