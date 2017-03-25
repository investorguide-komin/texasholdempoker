<?php

  // logs the class
  class metalog{
    function log($category, $message){
      if($message == "Error"){
        // also log in the system log
        error_log($message, 0);
      }else{
        $db     = database::get_db();
        $query  = $db->prepare("INSERT INTO `metalog`(`category`, `message`) values(?, ?)");
        $query  = $query->bind_param("ss", $category, $message);
        $query->execute();
      }
    }
  }
