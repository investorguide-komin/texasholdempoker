<?php

  class user{
    function __construct(){
    }

    // try to login the user from existing session if possible
    // if session is expired, redirect to home page (login / register)
    function try_login($redirect_to_login = false){
      if($redirect_to_login){
        view::redirect("index.php");
      }
    }

    // todo this function later
    function is_not_logged_in(){
      return true;
    }

    function is_unregistered_username($username){
      $db = database::get_db();
      $query  = $db->prepare("SELECT count(`id`) as count FROM `users` WHERE username = ?");
      $query->bind_param("s", $username);
      $query->execute();
      $result = $query->get_result();
      if(!($result->fetch_assoc()["count"])){
        return true;
      }
      return false;
    }

    // check the whitelist of allowed characters for username
    // only alphanumeric plus underscores + must be 5-15 characters long
    function is_allowable_username($username){
      if(preg_match('/[A-Za-z0-9_]{5,15}$/', $username)){
        return true;
      }
      return false;
    }

    // check the whitelist of allowed characters for password / see if the rules are matched
    // must be 8 characters long + contain atleast 1 alphabet, number and symbol
    function is_allowable_password($password, $password_confirm){
      if($password === $password_confirm){
        if(preg_match('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[$@$!%*#?&])[A-Za-z\d$@$!%*#?&]{8,}$/', $password)){
          return true;
        }
      }
      return false;
    }

    // salted hash for password
    function register_user($username, $password){
      $salted_pasword = password_hash($password, PASSWORD_DEFAULT);
      $db     = database::get_db();
      $query  = $db->prepare("INSERT INTO `users`(`username`, `password`) VALUES(?,?)");
      $query->bind_param("ss", $username, $salted_pasword);
      $query->execute();
    }

    function register($args){
      if(isset($args["username"]) && isset($args["password"]) && isset($args["password_confirm"])){
        $username = $args["username"];
        $password = $args["password"];
        $password_confirm = $args["password_confirm"];
        if($this->is_allowable_username($username)
          && $this->is_allowable_password($password, $password_confirm)){
            if($this->is_unregistered_username($username)){
              $this->register_user($username, $password);
              return true;
            }
          }
      }
      return false;
    }

    function create_login_session(){

    }

    function login($username, $password){
      $db = database::get_db();

      // get password associated with the user
      //$salted_password  =
      if($salted_password && password_verify($password, $salted_password)){
        return true;
      }
      return false;
    }

  }
