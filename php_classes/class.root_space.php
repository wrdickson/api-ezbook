<?php
Class Root_Space {

  private $id;
  private $title;
  private $child_of;
  private $space_type;
  private $space_code;
  private $people;
  private $beds;


  public function __construct($root_space_id){
    $pdo = Data_Connecter::get_connection();
    $stmt = $pdo->prepare("SELECT * FROM root_spaces WHERE id = :id");
    $stmt->bindParam(":id", $root_space_id, PDO::PARAM_INT);
    $stmt->execute();
    while($obj = $stmt->fetch(PDO::FETCH_OBJ)){
      $this->id = $obj->id;
      $this->title = $obj->title;
      $this->child_of = $obj->child_of;
      $this->space_type = $obj->space_type;
      $this->space_code = $obj->space_code;
      $this->people = $obj->people;
      $this->beds = $obj->beds;
    }
  }

  public function update_subspaces () {
    //  calculate children . . . 
    $children = RootSpaces::getRootSpaceChildren($this->id);
    return $children;
  }

  public function to_array(){
    $arr = array();
    $arr['id'] = $this->id;
    $arr['title'] = $this->title;
    $arr['child_of'] = $this->child_of;
    $arr['space_type'] = $this->space_type;
    $arr['space_code'] = $this->space_code;
    $arr['people'] = $this->people;
    $arr['beds'] = $this->beds;
    return $arr;
  }

}