<?php
class JWT
{

  private $secret;

  public function __construct($secret)
  {
    $this->secret = $secret;
  }

  public function encode($payload, $expiresIn = '30d')
  {
    $header = [
      'alg' => 'HS256',
      'typ' => 'JWT'
    ];

    $encodedHeader = $this->base64UrlEncode(json_encode($header));
    $encodedPayload = $this->base64UrlEncode(json_encode(array_merge($payload, [
      'exp' => time() + $this->convertToSeconds($expiresIn),
      'iat' => time()
    ])));
    $signature = $this->createSignature($encodedHeader, $encodedPayload, $this->secret);

    return $encodedHeader . '.' . $encodedPayload . '.' . $this->base64UrlEncode($signature);
  }

  public function decode($token)
  {
    $tokenArr = explode('.', $token);
    if (count($tokenArr) == 3) {
      $encodedHeader = $tokenArr[0];
      $encodedPayload = $tokenArr[1];
      $encodedSignature = $tokenArr[2];
    } else {
      throw new Exception("Invalid token");
    }

    $header = json_decode($this->base64UrlDecode($encodedHeader), true);
    $payload = json_decode($this->base64UrlDecode($encodedPayload), true);
    $signature = $this->base64UrlDecode($encodedSignature);

    if (!$this->verifySignature($encodedHeader, $encodedPayload, $signature, $this->secret)) {
      throw new Exception('Invalid signature');
    }

    if (isset($payload['exp']) && $payload['exp'] < time()) {
      throw new Exception('Token has expired');
    }

    return [
      'header' => $header,
      'payload' => $payload,
    ];
  }
  public function verify($token)
  {

    $tokenArr = explode('.', $token);
    if (count($tokenArr) == 3) {
      $encodedHeader = $tokenArr[0];
      $encodedPayload = $tokenArr[1];
      $encodedSignature = $tokenArr[2];
    } else {
      return false;
    }

    $header = json_decode($this->base64UrlDecode($encodedHeader), true);
    $payload = json_decode($this->base64UrlDecode($encodedPayload), true);
    $signature = $this->base64UrlDecode($encodedSignature);

    if (!$this->verifySignature($encodedHeader, $encodedPayload, $signature, $this->secret)) {
      return false;
    }

    if (isset($payload['exp']) && $payload['exp'] < time()) {
      return false;
    }

    return true;
  }

  private function base64UrlEncode($str)
  {
    return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($str));
  }

  private function base64UrlDecode($str)
  {
    $base64 = str_replace(['-', '_'], ['+', '/'], $str);
    $padding = strlen($base64) % 4;
    if ($padding) {
      $base64 .= str_repeat('=', 4 - $padding);
    }
    return base64_decode($base64);
  }

  private function createSignature($encodedHeader, $encodedPayload, $secret)
  {
    $signatureInput = $encodedHeader . '.' . $encodedPayload;
    return hash_hmac('sha256', $signatureInput, $secret, true);
  }

  private function verifySignature($encodedHeader, $encodedPayload, $signature, $secret)
  {
    $expectedSignature = $this->createSignature($encodedHeader, $encodedPayload, $secret);
    return hash_equals($signature, $expectedSignature);
  }

  private function convertToSeconds($time)
  {
    $unit = substr($time, -1);
    $value = intval(substr($time, 0, -1));
    switch ($unit) {
      case 's':
        return $value;
      case 'm':
        return $value * 60;
      case 'h':
        return $value * 60 * 60;
      case 'd':
        return $value * 24 * 60 * 60;
      default:
        throw new Exception('Invalid time unit');
    }
  }
}
