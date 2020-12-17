<?php
Class Reservation{
  public $id;
  public $space_id;
  public $space_code;
  public $checkin;
  public $checkout;
  public $people;
  public $beds;
  public $folio;
  public $folio_obj;
  public $status;
  public $history;
  public $notes;
  public $customer;
  public $customer_obj;

  public function __construct($id){
    $pdo = DataConnector::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = :id");
    $stmt->bindParam(":id", $id, PDO::PARAM_INT);
    $stmt->execute();
    while($obj = $stmt->fetch(PDO::FETCH_OBJ)){
      $this->id = $obj->id;
      $this->space_id = $obj->space_id;
      $this->space_code = $obj->space_code;
      $this->checkin = $obj->checkin;
      $this->checkout = $obj->checkout;
      $this->people = $obj->people;
      $this->beds = $obj->beds;
      $this->folio = $obj->folio;
      $iFolio = new Folio( $obj->folio );
      $this->folio_obj = $iFolio->to_array();
      $this->status = $obj->status;
      $this->history = json_decode($obj->history);
      $this->notes = json_decode($obj->notes);
      $this->customer = $obj->customer;
      $iCustomer = new Customer($obj->customer);
      $this->customer_obj = $iCustomer->dumpArray();
    }
  }

  // note is a assoc array 
   public function addNote( $note ){
    $arr = $this->notes;
    $x = array_push($arr, $note);
    $this->notes = $arr;
    return $this->notes;
  }

  public function add_history( $history_text, $user_id, $user_name ){
    $pdo = DataConnector::getConnection();
    $historyArr = array();
    $historyArr['date'] = date('Y-m-j H:i:s');
    $historyArr['userId'] = $user_id;
    $historyArr['username'] = $user_name;
    $historyArr['text'] = $history_text;
    $pdo = DataConnector::getConnection();
    $arrpush = array_push($this->history, $historyArr);
    $updateSuccess = $this->update_to_db();
  }

  public function checkin(){
    $this->status = 3;
    return $this->update_to_db();
  }

  public function checkout(){
    $this->status = 4;
    return $this->update_to_db();
  }

  public function to_array(){
    $arr = array();
    $arr['id'] = $this->id;
    $arr['space_id'] = $this->space_id;
    $arr['space_code'] = $this->space_code;
    $arr['checkin'] = $this->checkin;
    $arr['checkout'] = $this->checkout;
    $arr['people'] = $this->people;
    $arr['beds'] = $this->beds;
    $arr['folio'] = $this->folio;
    $arr['folio_obj'] = $this->folio_obj;
    $arr['status'] = $this->status;
    $arr['history'] = $this->history;
    $arr['notes'] = $this->notes;
    $arr['customer'] = $this->customer;
    $arr['customer_obj'] = $this->customer_obj;
    return $arr;
  }

  public function update_to_db(){
    $historyJson = json_encode($this->history);
    $notesJson = json_encode($this->notes);
    $pdo2 = DataConnector::getConnection();
    $stmt = $pdo2->prepare("UPDATE reservations SET space_id = :si, space_code = :sc, checkin = :ci, checkout = :co, people = :pe, beds = :be, folio = :fo, status = :st, history = :hi, notes = :nt, customer = :cu WHERE id = :id");
    $stmt->bindParam(":si", $this->space_id, PDO::PARAM_INT);
    $stmt->bindParam(":sc", $this->space_code, PDO::PARAM_STR);
    $stmt->bindParam(":ci", $this->checkin, PDO::PARAM_STR);
    $stmt->bindParam(":co", $this->checkout, PDO::PARAM_STR);
    $stmt->bindParam(":pe", $this->people, PDO::PARAM_INT);
    $stmt->bindParam(":be", $this->beds, PDO::PARAM_INT);
    $stmt->bindParam(":fo", $this->folio, PDO::PARAM_INT);
    $stmt->bindParam(":st", $this->status, PDO::PARAM_INT);
    $stmt->bindParam(":hi", $historyJson, PDO::PARAM_STR);
    $stmt->bindParam(":nt", $notesJson, PDO::PARAM_STR);
    $stmt->bindParam(":cu", $this->customer, PDO::PARAM_STR);
    $stmt->bindParam(":id", $this->id, PDO::PARAM_STR);
    $execute = $stmt->execute();
    $error = $stmt->errorInfo(); 
    return $execute;    
  }

  public static function update_from_params( $resId, $space_id, $space_code, $checkin, $checkout, $people, $beds, $folio, $status, $history, $notes, $customer){
    $pdo = DataConnector::getConnection();
    $stmt = $pdo->prepare("UPDATE reservations SET space_id = :si, space_code = :sc, checkin = :ci, checkout = :co, people = :pe, beds = :be, folio = :fo, status=:st, history = :hi, notes = :nt, customer = :cu WHERE id = :id");
    $stmt->bindParam(":si", $space_id, PDO::PARAM_INT);
    $stmt->bindParam(":sc", $space_code, PDO::PARAM_STR);
    $stmt->bindParam(":ci", $checkin, PDO::PARAM_STR);
    $stmt->bindParam(":co", $checkout, PDO::PARAM_STR);
    $stmt->bindParam(":pe", $people, PDO::PARAM_INT);
    $stmt->bindParam(":be", $beds, PDO::PARAM_INT);
    $stmt->bindParam(":fo", $folio, PDO::PARAM_INT);
    $stmt->bindParam(":st", $status, PDO::PARAM_INT);
    $stmt->bindParam(":hi", $history, PDO::PARAM_STR);
    $stmt->bindParam(":nt", $notes, PDO::PARAM_STR);
    $stmt->bindParam(":cu", $customer, PDO::PARAM_STR);
    $stmt->bindParam(":id", $resId, PDO::PARAM_STR);
    $execute = $stmt->execute();
    $error = $stmt->errorInfo(); 
    return $execute;
  }


  public static function createReservation(){

  }

  public static function getReservation($id){
        $pdo = DataConnector::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = :id");
        $stmt->bindParam(":id",$id,PDO::PARAM_INT);
        $stmt->execute();
        $r = array();
        while($obj = $stmt->fetch(PDO::FETCH_OBJ)){
          $r['id'] = $obj->id;
          $r['space_code'] = $obj->space_code;
          $r['space_id'] = $obj->space_id;
          $r['checkin'] = $obj->checkin;
          $r['checkout'] = $obj->checkout;
          $r['people'] = $obj->people;
          $r['beds'] = $obj->beds;
          $r['folio'] = $obj->folio;
          $r['status'] = $obj->status;
          $r['history'] = json_decode($obj->history, true);
          $r['notes'] = json_decode($obj->notes, true);
          $r['customer'] = $obj->customer;
          $iCustomer = new Customer($obj->customer);
          $r['customer_obj'] = $iCustomer->dumpArray();
        }
        $folio = new Folio( $r['folio'] );
        $r['folio_obj'] = $folio->to_array();

        return $r;
  }
}