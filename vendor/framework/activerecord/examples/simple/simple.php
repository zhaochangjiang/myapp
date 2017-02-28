<?php

  require_once __DIR__ . '/../../ActiveRecord.php';
// initialize ActiveRecord
// change the connection settings to whatever is appropriate for your mysql server 
  ActiveRecord\Config::initialize(function($cfg)
  {
      $cfg->set_model_directory('.');
      $cfg->set_connections(array(
          'development' => 'mysql://root:@127.0.0.1/test'));
  });

// assumes a table named "books" with a pk named "id"
// see simple.sql
  class Book extends ActiveRecord\Model
  {
      
  }
  print_r(Book::last()->attributes());
  