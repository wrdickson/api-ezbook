<?php
Class RootSpaces {

  public static function createRootSpace ($title, $displayOrder, $childOf) {
    $pdo = Data_Connecter::get_connection();
    $stmt = $pdo->prepare("INSERT INTO root_spaces (title, display_order, child_of) VALUES (:t, :d, :c)");
    $stmt->bindParam(":t", $title);
    $stmt->bindParam(":d", $displayOrder);
    $stmt->bindParam(":c", $childOf);
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
      $iArr['showChildren'] = (bool)$iObj->show_children;
      array_push($returnArr, $iArr);
    }
    return $returnArr;
  }

  public static function updateRootSpace ($id, $title, $displayOrder, $childOf, $showChildren) {
    $pdo = Data_Connecter::get_connection();
    $stmt = $pdo->prepare("UPDATE root_spaces SET title = :t, display_order = :d, child_of = :c, show_children = :s WHERE id = :i");
    $stmt->bindParam(":i", $id);
    $stmt->bindParam(":t", $title);
    $stmt->bindParam(":d", $displayOrder);
    $stmt->bindParam(":c", $childOf);
    $stmt->bindParam(":s", $showChildren);
    $execute = $stmt->execute();
    return $execute;
  }

  public function generateSpaceCodes () {

  }

}
