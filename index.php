<?php

//  this is the db connection and global variable info . . . relocate up the tree for deployment
require 'config/config.php';

require 'php_classes/class.data_connecter.php';
//  account
require 'php_classes/class.account.php';
require 'php_classes/class.jwt_util.php';
require 'php_classes/class.validate.php';

//  reservation engine
require "php_classes/class.customer.php";
require "php_classes/class.reservations.php";
//  require "phpClasses/class.validate.php";
require "php_classes/class.spaces.php";
require "php_classes/class.reservation.php";
require "php_classes/class.folio.php";
require "php_classes/class.reservation_history.php";
require "php_classes/class.root_spaces.php";
require "php_classes/class.root_space.php";

require __DIR__ . '/vendor/autoload.php';

// set the timezone
date_default_timezone_set(DEFAULT_TIMEZONE);

//  slim v2
$app = new \Slim\Slim();

//  ROUTES:

$app->post('/admin/create/', 'account_create');

$app->post('/account/login/', 'account_check_login');
$app->post('/account/testtoken/', 'account_test_token');

//  RESERVATION ENGINE:
$app->get('/root-spaces/', 'getRootSpaces');
$app->post('/root-spaces/', 'createRootSpace');
$app->delete('/root-spaces/', 'deleteRootSpace');
$app->put('/root-spaces/:id', 'updateRootSpace');
$app->get('/spaces/', 'getSpaces');
$app->post('/spaces/', 'createRootSpace');
$app->delete('/spaces/:id', 'deleteSpace');
$app->get('/types/', 'getTypes');
$app->get('/selectGroups/', 'getSelectGroups');
$app->get('/reservations/', 'getReservations');
$app->post('/reservations/', 'createReservation');
$app->post('/reservations-range/', 'getReservationsFromRange');
$app->get('/availability/:start/:end', 'getAvailabilityByDates');

$app->post('/allCustomers/', 'getAllCustomers');



// DEVELOPMENT
$app->get('/devSpaceCode/', 'appDevSpaceCode');

function appDevSpaceCode () {

  //  the function has to preceed the call
  //  because php doesn't 'hoist' ?????
  //  recursive getChildren()
  function getChildren($spaceId, $rootSpaces) {
    $children = [];
    foreach($rootSpaces as $space){
      if ($space['childOf'] == $spaceId) {
        //  recursive:
        $c = getChildren($space['id'], $rootSpaces);
        array_push($children, $space['id']);
        $children = array_merge($children, $c);
      }
    }
    return $children;
  }

  function getSpaceCode($spaceId, $rootSpaces) {
    $children = getChildren($spaceId, $rootSpaces);
    array_push($children, $spaceId);
    return $children;
  }

  //  get the spaces . . .
  $rootSpaces = RootSpaces::getRootSpaces();

  $a = array();
  foreach($rootSpaces as $space) {
    $a[$space['id']] = getSpaceCode($space['id'], $rootSpaces);

  }
  print json_encode($a);
}

//  FUNCTIONS:

function account_create() {
  $app = \Slim\Slim::getInstance();
  $params = json_decode($app->request->getBody(true));
  $token = $params->token;
  //  validate user
  $username = $params->data->username;
  $email = $params->data->email;
  $password = $params->data->password;
  print Account::create_account($username, $password, $email);
}

function account_check_login() {
  $result = array();
  $app = \Slim\Slim::getInstance();
  $params = json_decode($app->request->getBody(true));
  $username = $params->username;
  $password = $params->password;

  //  validate, $valid will be an array
  $valid = Validate::validate_login( array('username' => $username, 'password' => $password) );
  //  if valid, go ahead with the db check
  if($valid['is_valid'] == true) {
    $result = Account::check_login($username, $password);
    if($result['pass'] == true && $result['account_id'] > 0) {
      // this is where we generate the json web token
      $output = Jwt_Util::generate($result['account_id']);
      $result['jwt'] = $output;
    }
  } else {
    $result = array( 'pass' => false );
  }
  echo json_encode(array_merge($result, $valid));
}

function account_test_token() {
  $result = array();
  $app = \Slim\Slim::getInstance();
  $params = json_decode($app->request->getBody(true));
  $jwt = $params->jwt;
  $test = Jwt_Util::validate_token($jwt);
  echo json_encode($test);
}

function createRootSpace () {
  $app = \Slim\Slim::getInstance();
  $jwt = $app->request->headers->get('jwt');
  $params = json_decode($app->request->getBody(true));
  $response = array();
  $newRootSpace = $params->newRootSpace;
  $response['params'] = $newRootSpace;
  //  authenticate the token
  $authorize = Jwt_Util::validate_token($jwt);
  if($authorize['token_error'] != null){
    $response['authenticated'] = false;
    $response['auth_error'] = $validate['token_error'];
  } else {
    $response['authenticated'] = true;
    $response['auth_error'] = null;
    $response['execute'] = RootSpaces::createRootSpace( $newRootSpace->title, $newRootSpace->childOf, $newRootSpace->displayOrder, $newRootSpace->showChildren, $newRootSpace->spaceType, $newRootSpace->people, $newRootSpace->beds);
    $rootSpacesPreChildren = RootSpaces::getRootSpaces();
    $rootSpacesWithChildrenAndParents = array();
    foreach( $rootSpacesPreChildren as $rspc ) {
      $rspc['children'] = RootSpaces::getRootSpaceChildren($rspc['id']);
      $rspc['parents'] = RootSpaces::getRootSpaceParents($rspc['id']);
      array_push($rootSpacesWithChildrenAndParents, $rspc);
    }
    $response['rootSpacesWithChildrenAndParents'] = $rootSpacesWithChildrenAndParents;

  }
  print json_encode($response);
}

function createReservation () {
  $app = \Slim\Slim::getInstance();
  $jwt = $app->request->headers->get('jwt');
  $params = json_decode($app->request->getBody(true));
  $response = array();
  //$response['params'] = $params;
  $checkin = $params->newResObj->checkin;
  $checkout = $params->newResObj->checkout;
  $customer = $params->newResObj->customer;
  $spaceId = $params->newResObj->spaceId;
  $people = $params->newResObj->people;
  $beds = $params->newResObj->beds;



  //$response['jwt'] = $jwt;
  $response['checkAvailability'] = Reservations::checkConflictsByIdDate($checkin, $checkout, $spaceId );
  $response['create'] = Reservation::createReservation($checkin, $checkout, $customer, $spaceId, $people, $beds);

  print json_encode($response);
}

function createSpace () {
  //  TODO validate user

  //  TODO valide data

  $app = \Slim\Slim::getInstance();
  $jwt = $app->request->headers->get('jwt');
  $params = json_decode($app->request->getBody(true));
  $response = array();
  $response['params'] = $params;
  $response['jwt'] = $jwt;

  $rs35 = new Root_Space(35);
  $response['rs35_obj'] = $rs35->to_array();
  $response['rs35_space_code'] = implode(",", $rs35->update_subspaces());
  //  first, we add the space

  // then, we 
  

 print json_encode($response);
}

function deleteRootSpace () {
  $app = \Slim\Slim::getInstance();
  $jwt = $app->request->headers->get('jwt');
  $params = json_decode($app->request->getBody(true));
  $rootSpaceId = $params->rootSpaceId;
  $response = array();
  //  authenticate the token
  $validate = Jwt_Util::validate_token($jwt);
  if($validate['token_error'] != null){
    $response['authenticated'] = false;
    $response['auth_error'] = $validate['token_error'];
  } else {
    $response['authenticated'] = true;
    $response['auth_error'] = null;
    $response['rootSpaceId'] = $rootSpaceId;
    //  process here
    $response['execute'] = RootSpaces::deleteRootSpace($rootSpaceId);
    $response['root_spaces'] = RootSpaces::getRootSpaces();
  }

  print json_encode($response);
}

function deleteSpace ( $spaceId ) {
  $app = \Slim\Slim::getInstance();
  $jwt = $app->request->headers->get('jwt');
  $params = json_decode($app->request->getBody(true));
  $validate = Jwt_Util::validate_token($jwt);
  $response = array();
  $response['validate'] = $validate;
  $response['spaceId'] = $spaceId;
  if($validate['decoded'] && $validate['decoded']->accountId > 0 ){
    //  we're good
    $response['execute'] = RootSpaces::deleteRootSpace($spaceId);
    $rootSpacesPreChildren = RootSpaces::getRootSpaces();
    $rootSpacesWithChildrenAndParents = array();
    foreach( $rootSpacesPreChildren as $rspc ) {
      $rspc['children'] = RootSpaces::getRootSpaceChildren($rspc['id']);
      $rspc['parents'] = RootSpaces::getRootSpaceParents($rspc['id']);
      array_push($rootSpacesWithChildrenAndParents, $rspc);
    }
    $response['rootSpacesWithChildrenAndParents'] = $rootSpacesWithChildrenAndParents;
    print json_encode($response);
  } else {
    print json_encode($response);
  }
}

function getAllCustomers () {
  $app = \Slim\Slim::getInstance();
  $params = json_decode($app->request->getBody(true));
  $jwt = $app->request->headers->get('jwt');
  $response = array();
  $response['jwt'] = $jwt;
  $validate = Jwt_Util::validate_token($jwt);
  $response['validate'] = $validate;
  if($validate['decoded'] && $validate['decoded']->accountId > 0 ){
    //  we're good
    $response['customers'] = Customer::getCustomers();
  }
  print json_encode($response);
}

function getAvailabilityByDates ($start, $end) {
  $response = array();
  $response['start'] = $start;
  $response['end'] = $end;
  $response['availability'] = Reservations::checkAvailabilityByDates($start, $end);
  print json_encode($response);
}

function getReservations () {
  $app = \Slim\Slim::getInstance();
  $pdo = Data_Connecter::get_connection();
  $stmt = $pdo->prepare("SELECT * FROM reservations");

  $response['execute']= $stmt->execute();
  $arr= array();
  while($obj = $stmt->fetch(PDO::FETCH_OBJ)){
      $cust = new Customer($obj->customer);
      
      $iArr = array();
      $iArr['id'] = $obj->id;
      $iArr['space_id'] = $obj->space_id;
      $iArr['space_code'] = $obj->space_code;
      $iArr['checkin'] = $obj->checkin;
      $iArr['checkout'] = $obj->checkout;
      $iArr['customer'] = $obj->customer;
      $iArr['customer_obj'] = $cust->dumpArray();
      $iArr['people'] = $obj->people;
      $iArr['beds'] = $obj->beds;
      $iArr['folio'] = $obj->folio;
      $iArr['status'] = $obj->status;
      $iArr['history'] = json_decode($obj->history, true);
      $iArr['notes'] = json_decode($obj->notes, true);
      array_push($arr, $iArr);
  };
  $response['reservations'] = $arr;

  print json_encode($arr);
}

function getReservationsFromRange () {
  $app = \Slim\Slim::getInstance();
  $jwt = $app->request->headers->get('jwt');
  $params = json_decode($app->request->getBody(true));
  $startDate = $params->startDate;
  $endDate = $params->endDate;
  $response = array();
  //  parse the token
  $validate = Jwt_Util::validate_token($jwt);
  if($validate['token_error'] != null){
    $response['authenticated'] = false;
    $response['auth_error'] = $validate['token_error'];
  } else {
    $response['authenticated'] = true;
    $response['auth_error'] = null;
    //  for this request, we aren't worried about permission . . . just a valid account
    $response['startDate'] = $startDate;
    $response['endDate'] = $endDate;
    $response['reservations'] = Reservations::getReservationsDateRange($startDate, $endDate);

  }

  print json_encode($response);
}

function getRootSpaces () {
  $app = \Slim\Slim::getInstance();
  //  $jwt = $app->request->headers->get('jwt');
  $response = array();
  //  authenticate the token
  $rootSpacesPreChildren = RootSpaces::getRootSpaces();
  $response['root_spaces'] = $rootSpacesPreChildren;

  $rootSpacesWithChildrenAndParents = array();
  foreach( $rootSpacesPreChildren as $rspc ) {
    $rspc['children'] = RootSpaces::getRootSpaceChildren($rspc['id']);
    $rspc['parents'] = RootSpaces::getRootSpaceParents($rspc['id']);
    array_push($rootSpacesWithChildrenAndParents, $rspc);
  }

  $response['rootSpacesWithChildrenAndParents'] = $rootSpacesWithChildrenAndParents;


  print json_encode($response);
}

//  this is the OLD function being used by rms2
function getSelectGroups(){
  $app = \Slim\Slim::getInstance();
  $pdo = Data_Connecter::get_connection();
  //todo validate user

  $stmt = $pdo->prepare("SELECT * FROM select_groups");
  $success= $stmt->execute();
  $selectGroups= array();
  while($obj = $stmt->fetch(PDO::FETCH_OBJ)){
      $iArr = array();
      $iArr['id'] = $obj->id;
      $iArr['title'] = $obj->title;
      $iArr['order'] = $obj->order;
      $selectGroups[$obj->id] = $iArr;
  };
  //now get the appropriate spaces
  foreach($selectGroups as $group){
    $stmt = $pdo->prepare("SELECT * from spaces WHERE select_group = :groupId");
    $stmt->bindParam(':groupId',$group['id'],PDO::PARAM_INT);
    $success = $stmt->execute();
    $groupsArr = array();
    while($obj = $stmt->fetch(PDO::FETCH_OBJ)){
      $iArr = array();
      $iArr['space_id'] = $obj->space_id;
      $iArr['text'] = $obj->description;
      $iArr['value'] = $obj->space_id;
      $iArr['description'] = $obj->description;
      array_push($groupsArr, $iArr);
      $selectGroups[$group['id']]['groups'] = array();
      $selectGroups[$group['id']]['groups'] = $groupsArr;
    };
  }
  $response['selectGroups'] = $selectGroups;
  print json_encode($response);  
}

//  use this as a POST getter (for auth) from this point forward
function getP_SelectGroups () {

}

function getSpaces() {
  $app = \Slim\Slim::getInstance();
  $pdo = Data_Connecter::get_connection();
  //todo validate user
  $response = array();
  $stmt = $pdo->prepare("SELECT * FROM spaces ORDER BY show_order ASC;");
  $success= $stmt->execute();
  $arr = array();
  while($obj = $stmt->fetch(PDO::FETCH_OBJ)){
      $iArr = array();
      $iArr['space_id'] = $obj->space_id;
      $iArr['space_type'] = $obj->space_type;
      $iArr['description'] = $obj->description;
      $iArr['child_of'] = $obj->child_of;
      //!! IMPORTANT !!
      //casting to (bool) is important when we want to toggle the value in $store
      //if it's passed as 0 or 1, the toggle behavior is erratic
      $iArr['show_subspaces'] = $obj->show_subspaces;
      $iArr['show_order'] = $obj->show_order;
      $iArr['space_code'] = $obj->space_code;
      $iArr['subspaces'] = $obj->subspaces;
      $iArr['beds'] = $obj->beds;
      $iArr['people'] = $obj->people;
      $iArr['select_group'] = $obj->select_group;
      $iArr['select_order']= $obj->select_order;
      $arr[$obj->space_id] = $iArr;
  };
  $response['spaces'] = $arr;
  print json_encode($response);
}
function getTypes(){
  $app = \Slim\Slim::getInstance();
  $pdo = Data_Connecter::get_connection();
  //todo validate user

  $stmt = $pdo->prepare("SELECT * FROM space_types");
  $success= $stmt->execute();
  $arr= array();
  while($obj = $stmt->fetch(PDO::FETCH_OBJ)){
      $iArr = array();
      $iArr['id'] = $obj->id;
      $iArr['title'] = $obj->title;
      $arr[$obj->id] = $iArr;
  };
  $response['space_types'] = $arr;
  //note $app->request->params is for params that were
  //  appended to the get url, not data sent via POST
  $response['id'] = $app->request->params('id');
  $response['key'] = $app->request->params('key');

  print json_encode($response);
}

function updateRootSpace ($rootSpaceId) {
  $app = \Slim\Slim::getInstance();
  $jwt = $app->request->headers->get('jwt');
  $params = json_decode($app->request->getBody(true));
  //$updateRootSpace = $params->updateRootSpace;
  $response = array();
  //  authenticate the token
  $response['params'] = $params;
  $validate = Jwt_Util::validate_token($jwt);
  if($validate['token_error'] != null){
    $response['authenticated'] = false;
    $response['auth_error'] = $validate['token_error'];
  } else {
    $response['authenticated'] = true;
    $response['auth_error'] = null;
    $response['rootSpaceId'] = $rootSpaceId;
    //  process here
    //  convert boolean to tinyint . . . 
    $updateRootSpace = $params->updateRootSpace;
    $response['urs'] = $updateRootSpace;
    if ($updateRootSpace->showChildren == true){
      $updateRootSpace->showChildren = 1;
    } else {
      $updateRootSpace->showChildren = 0;
    }
    $response['execute'] = RootSpaces::updateRootSpace($rootSpaceId, $updateRootSpace->title, $updateRootSpace->childOf, $updateRootSpace->displayOrder, $updateRootSpace->showChildren, $updateRootSpace->spaceType, $updateRootSpace->people, $updateRootSpace->beds);
    $rootSpacesPreChildren = RootSpaces::getRootSpaces();
    $rootSpacesWithChildrenAndParents = array();
    foreach( $rootSpacesPreChildren as $rspc ) {
      $rspc['children'] = RootSpaces::getRootSpaceChildren($rspc['id']);
      $rspc['parents'] = RootSpaces::getRootSpaceParents($rspc['id']);
      array_push($rootSpacesWithChildrenAndParents, $rspc);
    }
    $response['rootSpacesWithChildrenAndParents'] = $rootSpacesWithChildrenAndParents;

    print json_encode($response);
  }
}

$app->run();
