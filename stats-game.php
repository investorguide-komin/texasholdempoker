<?php

  require_once(dirname(__FILE__)."/../__config.php");

  $view = new view();
  $user = new user();
  $user->try_login();

  /*
    Show a list of all games if an id is not passed
    If an id is passed, show the whole log for the game
  */
  $game_id  = isset($_GET["id"])?$_GET["id"]:null;
  if($game_id){
    $view->game   = game::load_by_id_and_phase($game_id, "done");
  }else{
    $view->games  = game::load_games_by_phase("done");
  }
  $view->render("tpl.stats-game.php");
