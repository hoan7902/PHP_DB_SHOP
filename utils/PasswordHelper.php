<?php

require_once("PasswordHasher.php");

function hashPassword($password)
{
    $ph = new PasswordHasher();
    return $ph->hash($password);
}

function verifyPassword($password, $hashPassword)
{
    $ph = new PasswordHasher();
    return $ph->verify($password, $hashPassword);
}
