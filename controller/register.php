<?php

  require_once(dirname(__FILE__)."/../__config.php");

  // register the user based on entered username and password
  // make sure to do following:
  // 1) check for existing username and prevent
  // 2) salted hash passwords
  // 3) encrypted channel for password

  $user = new user();
  $user->try_login(false);

  if($user->is_not_logged_in()){
    // process the $_POST
    if($user->register($_POST)){
      // try to register the user
    }else{
      // send an error message back to index page
      var_dump("we sa got some errors");
    }
  }else{
    var_dump("is logged in!");
    //view::redirect("home.php");
  }
