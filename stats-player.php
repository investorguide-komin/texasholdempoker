<?php

  require_once(dirname(__FILE__)."/../__config.php");

  $view = new view();
  $user = new user();
  $user->try_login();

  /* Get all players and show each of their win / loss / draw */
  $view->users  = user::load_all_users();
  $view->render("tpl.stats-player.php");
