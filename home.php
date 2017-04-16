<?php

  require_once(dirname(__FILE__)."/__config.php");

  $view = new view();
  $user = new user();
  $user->try_login();
  $view->render("tpl.home.php");
