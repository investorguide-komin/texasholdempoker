<?php
  require_once(dirname(__FILE__)."/../__config.php");

  $type = isset($_GET['type'])?$_GET['type']:false;
  $json = array();
  $code = 404;

  if($type == "deals"){
    $num_players  = 2;
    $game_id      = 1;

    $game   = game::load_by_id($game_id);
    $cards  = $game->deal();
    $cards["player1"]  =  $game->get_cards_dealt();
    $cards["player2"]  =  $game->get_cards_dealt();
    $code = 200;
  }
  else if($type == "community"){
    $card_type  = isset($_POST['card_type'])?$_POST['card_type']:null;
    $cards      = array();
    if($card_type == "flop"){
      $cards[0] = "K|S";
      $cards[1] = "10|D";
      $cards[2] = "2|C";
    }
    else if($card_type == "turn"){
      $cards[0] = "7|H";
    }
    else if($card_type == "river"){
      $cards[0] = "10|H";
    }
    $json["cards"]  = $cards;
    //$game->get_cards($turn_type);
  }else{
    $cards_dealt   =  array();
    $cards_dealt["player1"] = array();
    $cards_dealt["player1"][0] = "7|S";
    $cards_dealt["player1"][1] = "10|D";

    $cards_dealt["player2"] = array();
    $cards_dealt["player2"][0] = "J|H";
    $cards_dealt["player2"][1] = "Q|C";
    $json["cards_dealt"]  = $cards_dealt;
  }

  $code = 200;
  $json["code"] = $code;
  echo json_encode($json);
