<?php 
class Account {

    private $id;
    private $username;
    private $email;
    private $permission; 
    private $roles;  
    private $registered;
    private $last_login;
    private $last_activity;

    public function __construct($id){
        //handle the case of a non user
        if($id == 0){
        $this->id = 0;
        $this->username = "Guest";
        $this->email = "";
        $this->permission = 1;
        $this->roles = array();
        $this->registered = 0;
        $this->last_login = 0;
        $this->last_activity = 0;
        } else {
          //get properties from db
          $pdo = Data_Connecter::get_connection();
          $stmt = $pdo->prepare("SELECT * FROM accounts WHERE id = :id");
          $stmt->bindParam(":id",$id,PDO::PARAM_INT);
          $stmt->execute();
          while($obj = $stmt->fetch(PDO::FETCH_OBJ)){
            $this->id = $obj->id;
            $this->username = $obj->username;
            $this->email = $obj->email;
            $this->permission = $obj->permission;
            $this->roles = json_decode($obj->roles, true);
            $this->registered = $obj->registered;
            $this->last_login = $obj->last_login;
            $this->last_activity = $obj->last_activity;
          }
        }
    }

    public static function check_login($username, $password) {
      $response = array();
      $pdo = Data_Connecter::get_connection();
      $stmt = $pdo->prepare('SELECT * FROM accounts WHERE username = :u');
      $stmt->bindParam(':u', $username);
      $stmt->execute();
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      if(is_array($result)) {
        if(password_verify($password, $result['password']) == true){
          $response['pass'] = true;
          $response['account_id'] = $result['id'];
          $account = new Account($result['id']);
          //  update the login
          $response['update_login'] = $account->update_login();
          $response['update_activity'] = $account->update_activity();
          //  now reload 
          $account = new Account($result['id']);
          $response['account'] = $account->to_array_secure();
        } else {
          $response['pass'] = false;
          $response['account_id'] = -1;
        }
      } else {
        $response['pass'] = false;
        $response['account_id'] = -1;
      }
      return $response;
    }

    public static function create_account($username, $password, $email){
      //  TODO validate inputs
      $password_hash = password_hash($password, PASSWORD_DEFAULT);
      $permission = 10;
      $roles = '[]';
      $pdo = Data_Connecter::get_connection();
      $stmt = $pdo->prepare('INSERT INTO accounts (username, email, permission, roles, registered, last_activity, last_login, password) VALUES (:u, :e, :p, :ro, NOW(), NOW(), NOW(), :pwd)');
      $stmt->bindParam(':u', $username);
      $stmt->bindParam(':e', $email);
      $stmt->bindParam(':p', $permission);
      $stmt->bindParam(':ro', $roles);
      $stmt->bindParam(':pwd', $password_hash);
      return $stmt->execute();
    }

    public function to_array(){
        $arr = array();
        $arr['id'] = $this->id;
        $arr['username'] = $this->username;
        $arr['email'] = $this->email;
        $arr['permission'] = $this->permission;
        $arr['roles'] = $this->roles;
        $arr['registered'] = $this->registered;
        $arr['last_login'] = $this->last_login;
        $arr['last_activity'] = $this->last_activity;
        return $arr;
    }

    //  this method does NOT return account email
    public function to_array_secure(){
      $arr = array();
      $arr['id'] = $this->id;
      $arr['username'] = $this->username;
      $arr['permission'] = $this->permission;
      $arr['roles'] = $this->roles;
      return $arr;
  }

    public function get_id() {
        return $this->id;
    }

    public function get_username() {
        return $this->username;
    }
      
    public function get_email() {
        return $this->email;
    }

    public function set_password($password) {
      /*
        //hash the new password
        $password = hash('sha256', $password);
        $xid = $this->get_id();
        $pdo = Data_Connector::get_connection();
        $stmt = $pdo->prepare("UPDATE users SET password = :passwd WHERE id = :xid");
        $stmt->bindParam(":passwd", $password, PDO::PARAM_STR);
        $stmt->bindParam(":xid", $xid, PDO::PARAM_INT);
        $result = $stmt->execute();
        //password is not kept on the object, so we don't need to reset
        return $result;
      */
    }

    public function get_permission() {
      return $this->permission;
    }

    public function get_roles() {
      return $this->roles;
    }

    public function set_permission($permission) {
        $xid = $this->get_id();
        $pdo = Data_Connecter::get_connection();
        $stmt = $pdo->prepare("UPDATE account SET permission = :newPerm WHERE id = :xid");
        $stmt->bindParam(":newPerm", $permission, PDO::PARAM_INT);
        $stmt->bindParam(":xid", $xid, PDO::PARAM_INT);
        $result = $stmt->execute();
        if ($result == true){
            $this->permission = $permission;
        }
        return $result;
    }
      
    public function get_registered() {
        return $this->registered;
    }

    private function update_activity(){
      $pdo = Data_Connecter::get_connection();
      $stmt = $pdo->prepare("UPDATE accounts SET last_activity = NOW() WHERE id = :i");
      $stmt->bindParam(":i", $this->id,PDO::PARAM_STR);
      $result = $stmt->execute();
      return $result;
    }

    private function update_login(){
      $pdo = Data_Connecter::get_connection();
      $stmt = $pdo->prepare("UPDATE accounts SET last_login = NOW() WHERE id = :i");
      $stmt->bindParam(":i", $this->id,PDO::PARAM_STR);
      $result = $stmt->execute();
      return $result;
  }
}