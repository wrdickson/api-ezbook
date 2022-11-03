<?php
Class Folio{
  //$id, $customer, and $reservaton are from the folio table
  private $id;
  private $customer;
  private $reservation;
  //$sales is generated from the sales table
  private $sales;
  //$payments is generated from the payments table
  private $payments;

  public static function create_folio( $resId, $customerId ) {
    $pdo = Data_Connecter::get_connection();
    $stmt = $pdo->prepare("INSERT INTO folios ( customer, reservation ) VALUES ( :custId, :resId )");
    $stmt->bindParam(":custId", $customerId, PDO::PARAM_INT);
    $stmt->bindParam(":resId", $resId, PDO::PARAM_INT);
    
    $execute = $stmt->execute();
    $id = $pdo->lastInsertId();
    $response = array();
    $response['execute'] = $execute;
    $response['newId'] = $id;
    return $response['newId'];
  }
  
  public function __construct($id){
    $pdo = Data_Connecter::get_connection();
    //first get the basics: id, customer, reservation
    $stmt = $pdo->prepare("SELECT * FROM folios WHERE id = :id");
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
    while($obj = $stmt->fetch(PDO::FETCH_OBJ)){
      $this->id = $obj->id;
      $this->customer = $obj->customer;
      $this->reservation = $obj->reservation;
      
    }
    //second, get the sales (charges)
    /*
    $this->sales = Sale::loadSalesByFolioId($id);
    */

    //payments
    /*
    $this->payments = Payment::getPaymentsByFolioId($id);
    */
  }

  public function get_id(){
    return $this->id;
  }
  
  public function to_array(){
    $arr = array();
    $arr['id'] = $this->id;
    $arr['customer'] = $this->customer;
    $arr['reservation'] = $this->reservation;
    $arr['sales'] = $this->sales;
    $arr['payments'] = $this->payments;
    return $arr;
  }
}