<?php

  require_once(dirname(__FILE__)."/__config.php");

  $view = new view();

  $user = new user();
  $user->try_login(false);

  if($user->is_not_logged_in())
  {
    if(isset($_POST["register"])){
      // process the $_POST
      // register the user based on entered username and password
      // make sure to do following:
      // 1) check for existing username and prevent
      // 2) salted hash passwords
      // 3) encrypted channel for password

      if($user->register($_POST)){
        // register succeeded
        $view->redirect("home.php");
      }else{
        // send an error message back to index page
        $view->message  = "Registration failed. Please make sure the username and password match the required formats. Also, the username might be already taken.";
      }
    }
    else if(isset($_POST["login"])){
      // process the $_POST
      // login the user based on entered username and password
      // 1) salted hash passwords
      // 2) encryted channel for password
      // 3) create new session for every login

      if($user->login($_POST)){
        // login succeeded
        $view->redirect("home.php");
      }else{
        $view->message  = "Login failed. Please make sure the username and password entered are accurate";
      }
    }
  }else{
    $view->redirect("home.php");
  }

  $view->render("tpl.index.php");
