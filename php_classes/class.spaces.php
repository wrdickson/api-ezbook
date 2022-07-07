<?php
Class Spaces{
  public static function get_all_space_ids(){
    $pdo= Data_Connecter::get_connection();
    $stmt = $pdo->prepare("SELECT id FROM root_spaces");
    $stmt->execute();
    $cArr = array();
    while( $obj = $stmt->fetch(PDO::FETCH_OBJ)){
      array_push($cArr, $obj->id);
    } 
    return $cArr;
  }

  public static function get_subspaces($space_id){
    $pdo = DataConnector::getConnection();
    $stmt = $pdo->prepare("SELECT space_code FROM root_spaces WHERE id = :id");
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