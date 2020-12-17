<?php
Class Customer {
  public $id;
  public $lastName;
  public $firstName;
  public $address1;
  public $address2;
  public $city;
  public $region;
  public $country;
  public $postalCode;
  public $email;
  public $phone;
  
  public function __construct($id){
    $pdo = Data_Connecter::get_connection();
    $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = :id");
    $stmt->bindParam(":id",$id,PDO::PARAM_INT);
    $stmt->execute();
    while($obj = $stmt->fetch(PDO::FETCH_OBJ)){
      $this->id = $obj->id;
      $this->lastName = $obj->lastName;
      $this->firstName = $obj->firstName;
      $this->address1 = $obj->address1;
      $this->address2 = $obj->address2;
      $this->city = $obj->city;
      $this->region = $obj->region;
      $this->country = $obj->country;
      $this->postalCode = $obj->postalCode;
      $this->phone = $obj->phone;
      $this->email = $obj->email;
    }
  }
  
  public static function addCustomer($lastName, $firstName, $address1, $address2, $city, $region, $country, $postalCode, $phone, $email){
    
    //todo validate parameters
    
    $pdo = Data_Connecter::get_connection();
    $stmt = $pdo->prepare("INSERT INTO customers (lastName, firstName, address1, address2, city, region, country, postalCode, phone, email) VALUES (:ln, :fn, :a1, :a2, :ci, :re, :co, :pc, :ph, :em)");
    $stmt->bindParam(":ln", $lastName, PDO::PARAM_STR);
    $stmt->bindParam(":fn", $firstName, PDO::PARAM_STR);
    $stmt->bindParam(":a1", $address1, PDO::PARAM_STR);
    $stmt->bindParam(":a2", $address2, PDO::PARAM_STR);
    $stmt->bindParam(":ci", $city, PDO::PARAM_STR);
    $stmt->bindParam(":re", $region, PDO::PARAM_STR);
    $stmt->bindParam(":co", $country, PDO::PARAM_STR);
    $stmt->bindParam(":pc", $postalCode, PDO::PARAM_STR);
    $stmt->bindParam(":ph", $phone, PDO::PARAM_STR);
    $stmt->bindParam(":em", $email, PDO::PARAM_STR);
    $i = $stmt->execute();
    $insertId = $pdo->lastInsertId();
    return $insertId;
  }
  
  function dumpArray(){
    $arr = array();
    $arr['id'] = $this->id;
    $arr['lastName'] = $this->lastName;
    $arr['firstName'] = $this->firstName;
    $arr['address1'] = $this->address1;
    $arr['address2'] = $this->address2;
    $arr['city'] = $this->city;
    $arr['region'] = $this->region;
    $arr['country'] = $this->country;
    $arr['postalCode'] = $this->postalCode;
    $arr['phone'] = $this->phone;
    $arr['email'] = $this->email;
    return $arr;
  }
  
  public static function getCustomers(){
    $pdo = Data_Connecter::get_connection();
    $stmt = $pdo->prepare("SELECT * FROM customers ORDER BY lastName ASC");
    $stmt->bindParam(":id",$id,PDO::PARAM_INT);
    $stmt->execute();
    $cArr = array();
    while($obj = $stmt->fetch(PDO::FETCH_OBJ)){
      $iArr =array();
      $iArr['id'] = $obj->id;
      $iArr['lastName'] = $obj->lastName;
      $iArr['firstName'] = $obj->firstName;
      $iArr['address1'] = $obj->address1;
      $iArr['address2'] = $obj->address2;
      $iArr['city'] = $obj->city;
      $iArr['region'] = $obj->region;
      $iarr['country'] = $obj->country;
      $iArr['postalCode'] = $obj->postalCode;
      $iArr['phone'] = $obj->phone;
      $iArr['email'] = $obj->email;
      array_push($cArr, $iArr);
    }
    return $cArr;
  }
  
  public static function searchCustomers($lastName, $firstName){
    $last = $lastName . "%";
    $first = $firstName ."%";
    $pdo = Data_Connecter::get_connection();
    //are lastName and firstName both > 1?
    if( strlen($last) > 1 && strlen($first) > 1 ){
      $stmt = $pdo->prepare("SELECT * FROM customers WHERE lastName LIKE :last AND firstName LIKE :first ORDER BY lastName, firstName ASC" );
      $stmt->bindParam(":last",$last,PDO::PARAM_STR);
      $stmt->bindParam(":first",$first,PDO::PARAM_STR);
    //is lastName >1 while firstName = 1?
    } elseif ( strlen($last) > 1 && strlen($first) == 1 ){
      $stmt = $pdo->prepare("SELECT * FROM customers WHERE lastName LIKE :last ORDER BY lastName, firstName ASC");
      $stmt->bindParam(":last",$last,PDO::PARAM_STR);
    //is firstName > 1 and lastName = 1?
    } elseif ( strlen($first) > 1 && strlen($last) == 1 ){
      $stmt = $pdo->prepare("SELECT * FROM customers WHERE firstName LIKE :first ORDER BY lastName, firstName ASC");
      $stmt->bindParam(":first",$first,PDO::PARAM_STR);
    //first and last are both 1 (ie empty)
    } else {
      $stmt = $pdo->prepare("SELECT * FROM customers WHERE lastName LIKE :last AND firstName LIKE :first ORDER BY lastName, firstName ASC");
      $stmt->bindParam(":last",$last,PDO::PARAM_STR);
      $stmt->bindParam(":first",$first,PDO::PARAM_STR);      
    }
    $stmt->execute();
    $cArr = array();
    while($obj = $stmt->fetch(PDO::FETCH_OBJ)){
      $iCust = new Customer($obj->id);
      array_push($cArr, $iCust->dumpArray());
    }
    return $cArr;
  }
  
  public function update(){
    //TODO validate
    $pdo = Data_Connecter::get_connection();
    $stmt = $pdo->prepare("UPDATE customers SET lastName = :lastName, firstName = :firstName, address1 = :address1, address2 = :address2, city = :city, region = :region, country = :country, postalCode = :postalCode, phone = :phone, email = :email WHERE id=:id");
    $stmt->bindParam(":lastName", $this->lastName, PDO::PARAM_STR);
    $stmt->bindParam(":firstName", $this->firstName, PDO::PARAM_STR);
    $stmt->bindParam(":address1", $this->address1, PDO::PARAM_STR);
    $stmt->bindParam(":address2", $this->address2, PDO::PARAM_STR);
    $stmt->bindParam(":city", $this->city, PDO::PARAM_STR);
    $stmt->bindParam(":region", $this->region, PDO::PARAM_STR);
    $stmt->bindParam(":country", $this->country, PDO::PARAM_STR);
    $stmt->bindParam(":postalCode", $this->postalCode, PDO::PARAM_STR);
    $stmt->bindParam(":phone", $this->phone, PDO::PARAM_STR);
    $stmt->bindParam(":email", $this->email, PDO::PARAM_STR);
    $stmt->bindParam(":id", $this->id, PDO::PARAM_STR);
    $success = $stmt->execute();
    return $success;
  }
  
  //getters
  public function get_id(){
    return $this->id;
  }
  public function get_last_name(){
    return $this->lastName;
  }
  public function get_first_name(){
    return $this->firstName;
  }
  public function get_address1(){
    return $this->address1;
  }
  public function get_address2(){
    return $this->address2;
  }
  public function get_city(){
    return $this->city;
  }
  public function get_region(){
    return $this->region;
  }
  public function get_country(){
    return $this->country;
  }
  public function get_postal_code(){
    return $this->postalCode;
  }
  public function get_email(){
    return $this->email;
  }
  public function get_phone(){
    return $this->phone;
  }
}
