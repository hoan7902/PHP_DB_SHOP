<?php
require_once "JWT.php";
require_once "./config/Const.php";


function genToken($payload, $expiresIn = '30d')
{
    /* Generate token using json web token with secret key in Const.php
    Args:
        $payload: An array stores data
        $expiresIn: Time token is valid
    Returns:
        Token
    */
    $jwt = new JWT(SECRETKEY);
    return $jwt->encode($payload, $expiresIn);
}

function decodeToken($token)
{
    /* Decode token using secret key in Const.php
    (Use try/catch to catch exception)
    Args:
        $token: Token need to be decoded
    Returns:
        Payload (Array)
    */
    $jwt = new JWT(SECRETKEY);
    return $jwt->decode($token)["payload"];
}

function verifyToken($token)
{
    /* Verify token using secret key in Const.php
    Args:
        $token: Token need to be verified
    Returns:
        True or False
    */
    $jwt = new JWT(SECRETKEY);
    return $jwt->verify($token);
}
