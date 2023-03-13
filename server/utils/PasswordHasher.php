<?php
class PasswordHasher
{
    private $cost;

    public function __construct($cost = 10)
    {
        $this->cost = $cost;
    }

    public function hash($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, array('cost' => $this->cost));
    }

    public function verify($password, $hash)
    {
        return password_verify($password, $hash);
    }
}
