<?php

use \Firebase\JWT\JWT;

Class Jwt_Util {

  public static function generate($accountId) {
    $account = new Account($accountId);
    $payload = [
      'iat' => date("Y-m-d H:m:s", time()),
      'iss' => 'localhost',
      'exp' => time() + 86400,
      //  'exp' => time(),
      'exp_f' => date("Y-m-d H:m:s", time() + 86400),
      'accountId' => $accountId,
      'account' => $account->to_array_secure()
    ];
    $token = JWT::encode($payload, JWT_KEY);
    return $token;
  }

  public static function validate_token($token) {
    try{
      $test = array();
      $test['decoded']= JWT::decode($token, JWT_KEY, array('HS256'));
      $test['token_error'] = null;
    } catch (Exception $e){
      $test = array();
      $test['decoded'] = null;
      $test['token_error'] = $e->getMessage();
    }
    return $test;
  }

  public static function validate_test($token) {
    //JWT::$leeway = 60; // $leeway in seconds
    try{
      return JWT::decode($token, JWT_KEY, array('HS256'));
    } catch (Exception $e){
      return $e->getMessage();
    }
  }
}