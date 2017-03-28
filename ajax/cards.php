<?php
  require_once(dirname(__FILE__)."/../__config.php");

  $type = isset($_GET['type'])?$_GET['type']:false;
  $json = array();
  $code = 404;

  if($type == "deals"){
    $num_players  = 2;
    $game_id      = 1;

    $game   = game::load_by_id($game_id);
    $cards["player1"]  =  $game->get_cards_dealt();
    $cards["player2"]  =  $game->get_cards_dealt();
    $code = 200;
  }
  else if($type == "community"){
    $turn_type  = "flop"; //turn or river
    $game->get_cards($turn_type);
  }

  $cards_dealt   =  array();
  $cards_dealt["player1"] = array();
  $cards_dealt["player1"][0] = "7|S";
  $cards_dealt["player1"][1] = "10|D";

  $cards_dealt["player2"] = array();
  $cards_dealt["player2"][0] = "J|H";
  $cards_dealt["player2"][1] = "Q|C";

  $code = 200;

  $json["cards_dealt"]  = $cards_dealt;


  $json["code"] = $code;
  echo json_encode($json);
