<?php
Class Spaces{
  public static function get_all_space_ids(){
    $pdo= DataConnector::getConnection();
    $stmt = $pdo->prepare("SELECT space_id FROM spaces");
    $stmt->execute();
    $cArr = array();
    while( $obj = $stmt->fetch(PDO::FETCH_OBJ)){
      array_push($cArr, $obj->space_id);
    } 
    return $cArr;
  }

  public static function get_subspaces($space_id){
    $pdo = DataConnector::getConnection();
    $stmt = $pdo->prepare("SELECT space_code FROM spaces WHERE space_id = :id");
    $stmt->bindParam(":id",$space_id,PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch();
    if($result){
      return $result['space_code'];  
    }else{
      return false;
    }
    
    
  }


}