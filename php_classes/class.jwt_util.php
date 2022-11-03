<?php

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

Class Jwt_Util {

  public static function generate($accountId) {
    $account = new Account($accountId);
    $issuedAt = new DateTimeImmutable();
    $expire = $issuedAt->modify('+1 days')->getTimestamp();
    $payload = [
      'iat' => $issuedAt->getTimestamp(),  // Issued at: time when the token was generated,
      //  SERVER_NAME is a constant set in config.php
      'iss' => SERVER_NAME,
      'exp' => $expire,
      'exp_f' => date("Y-m-d H:m:s", $expire),  // Formatted expire
      'nbf'  => $issuedAt->getTimestamp(),  // Not before
      'nbf_f' => date( "Y-m-d H:m:s", $issuedAt->getTimestamp() ),  // Formatted not before
      'account' => $account->to_array_secure()
    ];
    $token = JWT::encode($payload, JWT_KEY, 'HS256');
    return $token;
  }

  public static function validate_token($token) {
    try{
      $test = array();
      $test['decoded']= JWT::decode($token, new Key(JWT_KEY, 'HS256'));
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