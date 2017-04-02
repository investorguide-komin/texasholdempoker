<?php

  require_once(dirname(__FILE__)."/__config.php");

  $view = new view();
  $user = new user();
  $user->try_login(false);

  // get some sort of game logic going on now
  

  $view->render("tpl.game.php");
