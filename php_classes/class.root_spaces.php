<?php
Class RootSpaces {

  public static function createRootSpace ($title, $childOf, $displayOrder, $showChildren, $spaceType, $people, $beds) {
    $pdo = Data_Connecter::get_connection();
    $stmt = $pdo->prepare("INSERT INTO root_spaces (title, child_of, display_order, show_children, space_type, people, beds) VALUES (:t, :co, :do, :sc, :st, :p, :b)");
    $stmt->bindParam(":t", $title);
    $stmt->bindParam(":co", $childOf);
    $stmt->bindParam(":do", $displayOrder);
    $stmt->bindParam(":sc", $showChildren);
    $stmt->bindParam(":st", $spaceType);
    $stmt->bindParam(":p", $people);
    $stmt->bindParam(":b", $beds);
    $execute = $stmt->execute();
    $id = $pdo->lastInsertId();
    return $id;
  }

  public static function deleteRootSpace ($rootSpaceId) {
    $pdo = Data_Connecter::get_connection();
    $stmt = $pdo->prepare("DELETE FROM root_spaces WHERE id = :rsi");
    $stmt->bindParam(":rsi", $rootSpaceId);
    $execute = $stmt->execute();
    return $execute;
  }

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

  public static function getRootSpaces () {
    $pdo = Data_Connecter::get_connection();
    $stmt = $pdo->prepare("SELECT * FROM root_spaces ORDER BY display_order ASC");
    $execute = $stmt->execute();
    $returnArr = array();
    while ($iObj = $stmt->fetch(PDO::FETCH_OBJ)) {
      $iArr = array();
      $iArr['id'] = $iObj->id;
      $iArr['title'] = $iObj->title;
      $iArr['childOf'] = $iObj->child_of;
      $iArr['displayOrder'] = $iObj->display_order;
      $iArr['showChildren'] = (string)$iObj->show_children;
      $iArr['spaceType'] = $iObj->space_type;
      $iArr['people'] = $iObj->people;
      $iArr['beds'] = $iObj->beds;
      array_push($returnArr, $iArr);
    }
    return $returnArr;
  }

  public static function getRootSpaceChildren($rootSpaceId){
    //  recursive getChildren()
    if( !function_exists('getChildren') ){
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
    }

    //  get the root spaces . . .
    $rootSpaces = RootSpaces::getRootSpaces();

    $children = getChildren($rootSpaceId, $rootSpaces);
    return $children;
  }

  public static function getRootSpaceParents ( $rootSpaceId ) {

    //  recursive getParents()
    if( !function_exists('getParents') ){
      function getParents($rootSpaceId, $rootSpaces) {
        $parents = [];
        foreach($rootSpaces as $rootSpace) {
          if($rootSpace['id'] == $rootSpaceId && $rootSpace['childOf'] > 0 ){
            array_push($parents, $rootSpace['childOf']);
            if($rootSpace['childOf'] > 0 ){
              $p = getParents($rootSpace['childOf'], $rootSpaces);
              $parents = array_merge($parents, $p);
            }
          }
        }
        return $parents;
      }
    }

    //  get the root spaces . . .
    $rootSpaces = RootSpaces::getRootSpaces();

    $parents = getParents($rootSpaceId, $rootSpaces);
    return $parents;

  }

  public static function updateRootSpace ($id, $title, $childOf, $displayOrder, $showChildren, $spaceType, $people, $beds) {
    /* 
    *  what we need to do:
    *  load up the all spaces array
    *  modify the space in question with the new data
    *  run getChildren() on everything
    *  if it works:
    *    save everything off
    *  if it doesnt:
    *    return an error
    */

    $pdo = Data_Connecter::get_connection();
    $stmt = $pdo->prepare("UPDATE root_spaces SET title = :t, display_order = :d, child_of = :c, show_children = :s, space_type = :st, people= :p, beds = :b WHERE id = :i");
    $stmt->bindParam(":i", $id);
    $stmt->bindParam(":t", $title);
    $stmt->bindParam(":d", $displayOrder);
    $stmt->bindParam(":c", $childOf);
    $stmt->bindParam(":s", $showChildren);
    $stmt->bindParam(":st", $spaceType);
    $stmt->bindParam(":p", $people);
    $stmt->bindParam(":b", $beds);
    $execute = $stmt->execute();
    return $execute;
  }

  public function generateSpaceCodes () {

  }

}
