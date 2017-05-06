<?php

  require_once(dirname(__FILE__)."/../__config.php");

  $view = new view();
  $user = new user();
  $user->try_login();

  /*
    Show a list of all games if an id is not passed
    If an id is passed, show the whole log for the game
  */


  $view->render("tpl.stats.php");
