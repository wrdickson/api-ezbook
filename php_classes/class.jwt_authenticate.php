<?php

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

Class Jwt_Authenticate {

  private $decoded;

  public function __construct( $perm_required ){
    //  get the Slim v2 app
    $app = \Slim\Slim::getInstance();
    //  get the jwt header
    $jwt = $app->request->headers->get('jwt');
    //  handle jwt is null, ie 'jwt' is not set in headers
    if(!$jwt){
      //  status 400: Bad Request
      $app->response->setStatus(400);
    } else {
      try{
        $this->decoded = JWT::decode($jwt, new Key(JWT_KEY, 'HS256'));
        //  check that the user has the permission level
        //  OR has the role 
        if( $this->decoded->account->permission < $perm_required['permission'] &&
            !in_array($perm_required['role'], $this->decoded->account->roles) ) {
          //  status 403: Forbidden
          $app->response->setStatus(403);
        } else {
          //  test iss, nbf and exp
          $now = new DateTimeImmutable();
          if ($this->decoded->iss !== SERVER_NAME ||
              $this->decoded->nbf > $now->getTimestamp() ||
              $this->decoded->exp < $now->getTimestamp()) {
            $app->response->setStatus(401);
          } else {
            //  jwt has passed authentication
            //  Do nothing.  All this script does is throw an error
            //  if the jwt does not authenticate correctly
          }
        }
      //  JWT::decode() throws an error if the decode fails
      } catch (Exception $e){
        //  status 401: Unauthorized
        $app->response->setStatus(401);
      }
    }
  }

  public function to_array () {
    return $this->decoded;
  }

}