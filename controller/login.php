<?php

  require_once(dirname(__FILE__)."/../__config.php");

  // login the user based on entered username and password
  // 1) salted hash passwords
  // 2) encryted channel for password
  // 3) create new session for every login

  $user = new user();
  $user->try_login(false);

  if($user->not_logged_in()){
  }else{
    $view::redirect("home.php");
  }
