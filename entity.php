<?php
require_once('database.php');

/*
 * Basic ORM class on matching table.
 *
 * @constructor: Entity($table)
 * @$table: string, matching table name
 */
class Entity {
  public $database;
  private $structure = array();
  private $data = array();
  private $data_original;
  private $table;

  /*
   * Constructor of Enitiy class
   *
   * @$table: string, name of table on what we match
   */
  public function __construct($table) {
    $this->database = DatabaseHandle::instance();
    $return = array();
    $result = $this->database->QueryString("DESCRIBE {$table};");

    if ($result) {
      while ($row = $result->fetch_assoc()) {
        $return[]= $row;
      }

      // Get structure of table
      foreach($return as $row) {
        $this->structure[$row['Field']] = array($row['Type'], 
                                                $row['Null'], 
                                                $row['Key']);
      }

      $this->table = $table;
    } else {
      throw new Exception($table . ' does not exist in database!');
    }
  }

  /*
   * Reads attribute of mapped class.
   * Attribute must be set first with get() method or setted manauly.
   *
   * @$var: string, attribute name
   */
  public function __get($var) {
    if (!array_key_exists($var, $this->structure)) {
      throw new Exception($var . ' does not exist!');
    }

    if (!array_key_exists($var, $this->data)) {
      throw new Exception($var . ' is not set!');
    }

    return $this->data[$var];
  }

  /*
   * Assaign value to attribute.
   * Attribute must exist in corresponding table.
   *
   * @$var: attribute name
   * @$value: attribute value
   */
  public function __set($var, $value) {
    if (!array_key_exists($var, $this->structure)) {
      throw new Exception($var . ' does not exist in table structure!');
    }

    $this->data[$var] = $value;
  }

  /*
   * Assaign all values to values of correspondant matching row.
   * If there are more than one matching row, you can define which one with
   * $index value.
   *
   * @$index: integer, select to fetch the $index numbered row
   */
  public function get($index = 0) {
    if (count(array_keys($this->data)) > 0) {
      $query = $this->database->QueryObject($this->table, $this->data);

      if (count($query) > 0) {
        $this->data = $query[$index];
        $this->data_original = $query[$index];
        return true; 
      }
    }
    return false;
  }

  /*
   * Same as get($index) method, but returns all query results in array 
   * of type Entity class. 
   */
  public function getAll() {
    if (count(array_keys($this->data)) > 0) {
      $query = $this->database->QueryObject($this->table, $this->data);

      $return = array();

      for ($i = 0; $i < count($query); $i++) {
        $return []= clone $this;
        $return[$i]->get($i);
      }

      return $return;
    } else {
      $query = $this->database->QueryAllObjects($this->table);

      $return = array();
      for ($i = 0; $i < count($query); $i++) {
        $return []= new Entity($this->table);
        $return[$i]->_set_data($query[$i]);
      }

      return $return;
    }
  }

  /*
   * Update table with corresponding attributes in class
   * Before using set() method you must use get() or insert() method to match
   * the corresponding row in database.
   */
  public function set() {
    if (count(array_keys($this->data)) > 0 && isset($this->data_original)) {
      $this->database->UpdateObject($this->table, 
                                    $this->data_original, 
                                    $this->data);
    }
  }

  /*
   * Insert new row into table corresponding with class attributes
   */
  public function insert() {
    if (count(array_keys($this->data)) > 0) {
      $this->database->Insert($this->table, $this->data);
      $this->data_original = $this->data;
    }
  }

  /* 
   * Delete row from table corresponding with class attributes. 
   * In order for function to succesed the get($index) method must
   * return true, in other word, the matching must be done with get($index)
   * method. 
   *
   * @$index: If more than one row with matched attributes exists, select  
   *          the indexed one.
   */
  public function delete($index = 0) {
    if ($this->get($index) === true) {
      $this->database->DeleteObject($this->table, $this->data);
    }
  }

  /*
   * This function will rewrite the internal data array
   * where attributes are stored.
   * Integrity with corresponding table is not checked!
   *
   * @$data: assoc array, with rule ('attribute_name' => attribute_value)
   */
  public function _set_data($data) {
    $this->data = $data;
  }

  /*
   * Resets the internal data to nothing. 
   */
  public function reset() {
    $this->_set_data(array()); 
  }
}

?>
