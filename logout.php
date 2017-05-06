<?php

  require_once(dirname(__FILE__)."/../__config.php");

  // logout the user
  // delete session, cookies to prevent sadness

  $user = new user();
  $user->try_login(false);
  $user->logout();

  $view = new view();
  $view->redirect("index.php");
