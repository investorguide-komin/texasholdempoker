<?php
  require_once(dirname(__FILE__)."/../__config.php");

  $type = isset($_GET['type'])?$_GET['type']:false;
  $json = new container();
  $code = 404;

  $user = new user();
  $user->try_login(false);

  if($type == "create"){
      $name = isset($_POST["name"])?$_POST["name"]:null;
      if($name){
        $name = substr($name, 0, 35); // only allow upto 35 chars
        $game = new game();
        $game->create($name);
        $code = 200;
      }
  }
  else if($type == "load"){
    $json->games =  game::load_games();
    $code = 200;
  }
  else if($type == "sync"){
    $result       = new container();

    $game_id      = $user->get_active_game();
    $game         = game::load_by_id($game_id);
    $player_other = $game->get_other_player($user);

    if($player_other && ($player_other->id != $user->id)){
      $result->players[] = $user;
      $result->players[] = $player_other;

      // game players
      $result->player_you  = new container();
      $result->player_other= new container();
      $result->player_you->name   = $user->username;
      $result->player_other->name = $player_other->username;

      // game player cards
      $result->player_you->cards  = $game->get_cards_dealt_for_user($user);
      $result->player_other->cards= $game->get_cards_dealt_for_user($player_other);
      if(!count($result->player_you->cards)){
        // deal the cards if not dealt already
        $game->deal_cards($user->id, $player_other->id);
        $result->player_you->cards  = $game->get_cards_dealt_for_user($user);
        $result->player_other->cards= $game->get_cards_dealt_for_user($player_other);
      }

      // game log
      // some random logs

      // see if somebody has won

      // decide on the player move
      // if no one has a move yet, create a move for first user who joined
      // if first user already completed a move or the move has expired, create a move for the second user

      // game community cards
      if($game->phase != "setup"){
        $result->community_cards    = $game->get_community_cards();
      }

      $code = 200;
    }
    $json->game   = $result;
  }

  $json->code = $code;
  echo $json->json_encode();
