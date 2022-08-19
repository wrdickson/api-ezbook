<?php 
Class Sale {
  private $id;
  private $sale_date;
  private $tax_type;
  //from table tax_types
  private $tax_title;
  //from table tax_types
  private $tax_rate;
  private $sales_item;
  //from table sales_items
  private $sales_item_title;
  private $net;
  private $tax;
  private $total;
  private $sold_by;
  private $folio;
  private $shift;

  public static function loadSalesByFolioId( $folioId ){
    $response = array();
    $pdo = DataConnector::getConnection();
    $stmt =$pdo->prepare("SELECT sales.id, sales.sale_date, tax_types.tax_title, tax_types.id AS 'tax_type', tax_types.tax_rate, sales.sales_item, sales_items.sales_item_title, sales.net, sales.tax, sales.total, sales.sold_by, users.username, sales.shift FROM ((( sales INNER JOIN sales_items ON sales.sales_item = sales_items.id )  INNER JOIN tax_types ON sales_items.tax_type = tax_types.id ) INNER JOIN users ON sales.sold_by = users.id) WHERE sales.folio = :folio_id ORDER BY sales.sale_date ASC");
    $stmt->bindParam(':folio_id', $folioId, PDO::PARAM_INT);
    $response['execute'] = $stmt->execute();
    $salesArray = array();
    while( $obj = $stmt->fetch(PDO::FETCH_OBJ)){
      $iArr = array();
      $iArr['id'] = $obj->id;
      $iArr['sale_date'] = $obj->sale_date;
      $iArr['tax_type'] = $obj->tax_type;
      $iArr['tax_title'] = $obj->tax_title;
      $iArr['tax_rate'] = $obj->tax_rate;
      $iArr['sales_item'] = $obj->sales_item;
      $iArr['sales_item_title'] = $obj->sales_item_title;
      $iArr['net'] = $obj->net;
      $iArr['tax'] = $obj->tax;
      $iArr['total'] = $obj->total;
      $iArr['sold_by'] = $obj->sold_by;
      $iArr['username'] = $obj->username;
      $iArr['shift'] = $obj->shift;
      array_push($salesArray, $iArr);
    }
    $response['sales'] = $salesArray;
    //return $response;
    return $salesArray;
  }

  public static function get_sales_by_shift_id( $shift_id ){
    $response = array();
    $pdo = DataConnector::getConnection();
    $stmt =$pdo->prepare("SELECT sales.id, sales.sale_date, tax_types.tax_title, tax_types.id AS 'tax_type', tax_types.tax_rate, sales.sales_item, sales_items.sales_item_title, sales.net, sales.tax, sales.total, sales.sold_by, users.username, sales.shift FROM ((( sales INNER JOIN sales_items ON sales.sales_item = sales_items.id )  INNER JOIN tax_types ON sales_items.tax_type = tax_types.id ) INNER JOIN users ON sales.sold_by = users.id) WHERE sales.shift = :shift_id ORDER BY sales.sale_date ASC");
    $stmt->bindParam(':shift_id', $shift_id);
    $response['execute'] = $stmt->execute();
    $salesArray = array();
    while( $obj = $stmt->fetch(PDO::FETCH_OBJ)){
      $iArr = array();
      $iArr['id'] = $obj->id;
      $iArr['sale_date'] = $obj->sale_date;
      $iArr['tax_type'] = $obj->tax_type;
      $iArr['tax_title'] = $obj->tax_title;
      $iArr['tax_rate'] = $obj->tax_rate;
      $iArr['sales_item'] = $obj->sales_item;
      $iArr['sales_item_title'] = $obj->sales_item_title;
      $iArr['net'] = $obj->net;
      $iArr['tax'] = $obj->tax;
      $iArr['total'] = $obj->total;
      $iArr['sold_by'] = $obj->sold_by;
      $iArr['username'] = $obj->username;
      $iArr['shift'] = $obj->shift;
      array_push($salesArray, $iArr);
    }
    $response['sales'] = $salesArray;
    //return $response;
    return $salesArray;
  }

  public static function post_sale(  $sales_item, $quantity, $net, $tax, $total, $sold_by, $folio, $shift, $notes ){
    $pdo = DataConnector::getConnection();
    $notesJson = json_encode( $notes );
    $stmt = $pdo->prepare("INSERT INTO sales ( sale_date, sales_item, sales_quantity, net, tax, total, sold_by, folio, shift, notes ) VALUES (  NOW(), :si, :qty, :net, :ta, :ttl, :sb, :f, :s, :n )");
    $stmt->bindParam(':si', $sales_item, PDO::PARAM_INT);
    $stmt->bindParam(':qty', $quantity, PDO::PARAM_INT);
    $stmt->bindParam(':net', $net, PDO::PARAM_STR);
    $stmt->bindParam(':ta', $tax, PDO::PARAM_STR);
    $stmt->bindParam(':ttl', $total, PDO::PARAM_STR);
    $stmt->bindParam(':sb', $sold_by, PDO::PARAM_INT);
    $stmt->bindParam(':f', $folio, PDO::PARAM_INT);
    $stmt->bindParam(':s', $shift, PDO::PARAM_INT);
    $stmt->bindParam(':n', $notesJson, PDO::PARAM_STR);
    $success = $stmt->execute();
    return $success;
  }

  public function __construct( $id ){
    $pdo = DataConnector::getConnection();
    $stmt =$pdo->prepare("SELECT * FROM sales WHERE id = :id");
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    while( $obj = $stmt->fetch(PDO::FETCH_OBJ)){
      $this->id = $obj->id;
      $this->sale_date = $obj->sale_date;
      $this->tax_type = $obj->tax_type;
      $this->tax_rate = $obj->tax_rate;
      $this->sales_item = $obj->sales_item;
      $this->net = $obj->net;
      $this->tax = $obj->tax;
      $this->total = $obj->total;
      $this->by = $obj->by;
      $this->folio = $obj->folio;
      $this->shift = $obj->shift;
    };  
  }
}