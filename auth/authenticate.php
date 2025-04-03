<?php
require_once '../models/User.php';

class Authenticate
{
  public static $header = [
    'alg' => 'HS256',
    'typ' => 'JWT'
  ];
  public static function generateJWT($user_id, $username, $role)
  {
    $payload = [
      'user_id' => $user_id,
      'username' => $username,
      'role' => $role,
      'iat' => time(),
      'exp' => time() + 3600
    ];
    $base64Header = base64_encode(json_encode(self::$header));
    $base64Payload = base64_encode(json_encode($payload));
    $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, 'secret', true);
    $base64Signature = base64_encode($signature);
    return "{$base64Header}.{$base64Payload}.{$base64Signature}";
  }

  public static function verifyJWT($jwt)
  {
    $parts = explode('.', $jwt);  //correct format is "header" + "." + "payload" + "." + "signature"
    if (count($parts) !== 3) {    //if jwt format is not valid
      return false;
    }
    $base64Payload = $parts[1];   //to access exp, must decode payload, previously encoded into base64 
    $pay = json_decode(base64_decode($base64Payload), true);    //return assoc array after json decode 
    if (!self::checkExpired($pay)) {
      return false;
    }
    $base64Header = $parts[0];
    $base64Payload = $parts[1];
    $base64Signature = $parts[2];
    $signature = hash_hmac('sha256', $base64Header . '.' . $base64Payload, 'secret', true);
    $signature = base64_encode($signature);
    return $signature === $base64Signature;
  }

  public static function checkExpired($payload)
  {
    if (time() > $payload['exp']) {   //expired
      return false;
    }
    return true;
  }

}
