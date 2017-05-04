<?php

  // class to log things
  class metalog{
    function log($category, $message){
      if($message == "Error"){
        // also log in the system error log for sanity
        error_log($message, 0);
      }
      $db     = database::get_db();
      $query  = $db->prepare("INSERT INTO `metalog`(`category`, `message`) VALUES(?, ?)");
      $query->bind_param("ss", $category, $message);
      $query->execute();
    }
  }
