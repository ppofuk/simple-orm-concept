<?php
require_once('configure.php');

/*
 * Database class for queries on MySQL database.
 *
 */

class Database extends DatabaseConfig {
  private $connection_;
  private $connected_ = 0;

  /*
   * Connect to MySQL database.
   */
  public function Connect() {

    $this->connection_ = new mysqli($this->host_, 
                                    $this->user_, 
                                    $this->password_, 
                                    $this->database_);

    if ($this->connection_->connect_error) {
      echo $this->connection_->connect_error;
      return false;
    }

    $this->connection_->set_charset("utf8");

    $this->connected_ = 1;
    return true;
  }

  /*
   * Close the database connection
   */
  public function Close() {
    if ($this->connected_) {
      $this->connection_->close();
      $this->connected_ = 0;
    }
  }

  /*
   * Generate a INSERT query and execute it on table
   *
   * @$table: string, table name
   * @$dict: array of type ('row_name' => value)
   */
  public function Insert($table, $dict) {
    $keys = "";
    $values = "";

    foreach($dict as $key => $value) {
      $keys = $keys . "{$key}, ";
    }

    foreach($dict as $key => $value) {
      if (is_string($value)) {
        $value = $this->connection_->escape_string($value);
        $value = "'{$value}'";
      }

      $values = $values . "{$value}, ";
    }

    $keys = substr($keys, 0, strlen($keys) - 2);
    $values = substr($values, 0, strlen($values) - 2);

    $sql = "INSERT INTO {$table} ({$keys}) VALUES ({$values});";

    //echo $sql;

    $this->connection_->query($sql);
  }

  /*
   * Execute an sql query on database
   * @$sql: string, sql query
   */
  public function QueryString($sql) {
    return $this->connection_->query($sql);
  }

  /*
   * Generet a SELECT FROM $table WHERE ... query and execute it on table
   *
   * @$table: string, table name
   * @$object: array of type ('row_name' => value). This is a query object
   * @$clause: string, connection string in WHERE ... query
   *
   * Example: QueryObject('User', array('Name' => 'Petar')); expands to:
   *          SELECT * FROM User WHERE Name = Petar ;
   *
   *          QueryObject('User', array('Name' => 'Petar', 'Name' => 'Pero'), 'OR');
   *          expands to: SELECT * FROM User WHERE Name = Petar OR Name = PERO ;
   */

  public function QueryObject($table, $object, $clause = 'AND') {
    $append = "";

    foreach($object as $key => $value) {
      if (is_string($value)) {
        $value = $this->connection_->escape_string($value);
        $value = "'{$value}'";
      }

      $append .= "{$key} = {$value} {$clause} ";
    }
    $append = substr($append, 0, -strlen($clause) - 1);
    $sql = "SELECT * FROM {$table} WHERE {$append};";

    $return = array();
    $result = $this->connection_->query($sql);

    if ($result) {
      while ($row = $result->fetch_assoc()) {
        $return[]= $row;
      }
    }

    return $return;
  }

  /*
   * Return all objects from table
   * @$table string, table name
   */
  public function QueryAllObjects($table) {
    $sql = "SELECT * FROM {$table};";

    $return = array();
    $result = $this->connection_->query($sql);

    if ($result) {
      while ($row = $result->fetch_assoc()) {
        $return[]= $row;
      }
    }

    return $return;
  }

  // @table Table name, for example 'User'
  // @request All record from table that match the $request
  //  object (assoc array) are applied with $object.
  public function UpdateObject($table, $request, $object, $clause = 'AND') {
    $append_update = "";
    foreach ($object as $key => $value) {
      if (is_string($value)) {
        $value = $this->connection_->escape_string($value);
        $value = "'{$value}'";
      }
      $append_update .= "{$key} = {$value},";
    }
    $append_update = substr($append_update, 0, -1);

    $append_where = "";
    foreach($request as $key => $value) {
      if (!empty($value)) {
        if (is_string($value))
          $value = "'{$value}'";
        $append_where .= "{$key} = {$value} {$clause} ";
      }
    }
    $append_where = substr($append_where, 0, -strlen($clause) - 1);

    $sql = "UPDATE {$table} SET {$append_update} WHERE {$append_where};";

    return $this->connection_->query($sql);
  }


  /*
   * Delete rows from table. Parameters are same as Insert method.
   */
  public function DeleteObject($table, $object, $clause = 'AND') {
    $append = "";

    foreach($object as $key => $value) {
      if (is_string($value)) {
        $value = $this->connection_->escape_string($value);
        $value = "'{$value}'";
      }

      $append .= "{$key} = {$value} {$clause} ";
    }
    $append = substr($append, 0, -strlen($clause) - 1);
    $sql = "DELETE FROM {$table} WHERE {$append};";

    $result = $this->connection_->query($sql);
    return $result;
  }


}

/*
 * Safe access to Database class, e.g. use it without worries about connections
 *
 * Usage: $database = DatabaseHandle::instance();
 */
class DatabaseHandle extends Database {
  public function __construct() {
    $this->Connect();
  }

  public function __destruct() {
    $this->Close();
  }
}

?>
