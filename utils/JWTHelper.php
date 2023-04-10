<?php
require_once "JWT.php";
require_once "./config/Const.php";
require_once "./models/UsersModel.php";


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

function authHeader($authHeader, $checkId = null)
{
    $token = null;
    if (str_starts_with($authHeader, "Bearer")) {
        $authHeaderArr = explode(" ", $authHeader);
        $token = $authHeaderArr[1];
    } else {
        return "Not Authenticated";
    }
    $usersModel = new UsersModel();
    if (verifyToken($token)) {
        $payload = decodeToken($token);
        $id = $payload["userId"] ? $payload['userId'] : null;
        if ($id) {
            try {
                $user = $usersModel->getUserById($id);
                if ($user) {
                    if ($user['role'] == 'admin') {
                        return "admin";
                    } else if ($checkId && $user['userId'] == $checkId) {
                        return "self";
                    } else {
                        return $user['role'];
                    }
                }
            } catch (Exception $e) {
                return "Wrong token " . $e->getMessage();
            }
        }
        return "Not Authenticated";
    } else {
        return "Not Authenticated";
    }
}
// Check token before use this function
function getUserId($authHeader)
{
    $token = getTokenFromAuthHeader($authHeader);
    if (verifyToken($token)) {
        $payload = decodeToken($token);
        $id = $payload["userId"] ? $payload['userId'] : null;
        return $id;
    }
    return null;
}
function getTokenFromAuthHeader($authHeader)
{
    $token = null;
    if (str_starts_with($authHeader, "Bearer")) {
        $authHeaderArr = explode(" ", $authHeader);
        $token = $authHeaderArr[1];
    }
    return $token;
}
