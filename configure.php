<?php
require_once('singleton.php');

/*
 * Basic database configuration goes here. 
 */
class DatabaseConfig extends Singleton {
  protected $host_ = 'localhost';
  protected $user_ = 'tester';
  protected $database_ = 'orm_test';
  protected $password_ = 'test_passwd';
}

?>