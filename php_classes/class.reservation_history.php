<?php
Class Reservation_History {
  public $res_id;
  public $history;

  public function __construct( $res_id ){
    $pdo = DataConnector::getConnection();
    $stmt = $pdo->prepare("SELECT * FROM reshistory WHERE res_id = :res_id");
    $stmt->bindParam(':res_id', $res_id, PDO::PARAM_INT);
    $stmt->execute();
    while( $obj = $stmt->fetch(PDO::FETCH_OBJ)){
      $this->res_id = $obj->res_id;
      $this->history = json_decode( $obj->history );
    }
  }

  public function add_history_snapshot( $res_arr, $user_id, $username ){
    $arr = $this->history;
    $new_history = array();
    $new_history['date'] = date('Y-m-j H:i:s');
    $new_history['user'] = $user_id;
    $new_history['username'] = $username;
    $new_history['reservation'] = $res_arr;
    array_push( $arr, $new_history );
    $this->history = $arr;
    $this-> update_to_db();
  }

  public function to_array(){
    $arr = array();
    $arr['res_id'] = $this->res_id;
    $arr['history'] = $this->history;
    return $arr;
  }

  public function update_to_db(){
    $res_id = $this->res_id;
    $json_history = json_encode( $this->history );
    $pdo = DataConnector::getConnection();
    $stmt = $pdo->prepare("UPDATE reshistory SET history = :history WHERE res_id = :res_id");
    $stmt->bindParam(':history', $json_history, PDO::PARAM_STR );
    $stmt->bindParam(':res_id', $res_id, PDO::PARAM_INT);
    return $stmt->execute();
  }

};