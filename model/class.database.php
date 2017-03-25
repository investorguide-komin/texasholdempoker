<?php

  require_once(dirname(__FILE__)."/class.metalog.php");

  //to establish connection to the databse
  class database{
    function connect(){
      $link = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
      if(!$link){
          metalog::log("Error", "Unable to connect to MYSQL / ".mysqli_connect_errno());
          exit;
      }
      return $link;
    }

    function disconnect(){
      unset($GLOBALS['db']);
    }

    function get_db(){
      if(!isset($GLOBALS['db'])){
        $GLOBALS['db']   = self::connect();
      }
      return $GLOBALS['db'];
    }

  }
