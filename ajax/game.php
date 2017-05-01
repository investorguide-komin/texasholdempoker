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
  else if(($type == "sync") || ($type == "trigger")){
    $debug = false;
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
      $result->player_other->cards= array();
      if(!count($result->player_you->cards)){
        // deal the cards if not dealt already
        $game->deal_cards($user->id, $player_other->id);
        $result->player_you->cards  = $game->get_cards_dealt_for_user($user);
        $result->player_other->cards= array();
      }

      $current_pot_amount = 0;
      $current_move       = game_move::get_current_move($game->id);
      $current_pot_number = game_move::get_current_pot_number($game->id);
      // this means last round has ended
      if($current_move->round == 5){
        $current_pot_number++;
      }

      // process the player move if this is a "trigger" action
      // make sure only the user with the current active move can make a change though - reject all inputs from the other player at this time
      if(($type === "trigger") && ($game->get_user_id_with_active_move() === $user->id)){
        $pot_money_bet  = isset($_POST["pot_money_bet"])?intval($_POST["pot_money_bet"]):0;
        $pot_action     = isset($_POST["pot_action"])?$_POST["pot_action"]:"fold";
        $amount         = $game->get_amount($user->id);
        $round_detail   = "Round complete";

        // check if the pot money bet is within valid range for the current user
        // if the money provided is valid, perform the game move
        if($game->check_validity_of_action($pot_action, $pot_money_bet, $amount))
        {
          $current_pot_amount = game_move::get_current_pot_amount($game->id, $current_pot_number);
          $current_move->close(
                $pot_money_bet,
                $pot_action,
                game_move::get_human_readable_action($user->username, $pot_action, $pot_money_bet).
                " Current pot value is at: $".($current_pot_amount+$pot_money_bet)
          );
          // reduce the amount of money bet from the user now that the move has been completed
          $game->update_amount($user->id, ($amount - $pot_money_bet));

          if($pot_action == "fold"){
            $game->round_won = true;
          }
        }
      }
      $current_pot_amount = (!$current_pot_amount) ? game_move::get_current_pot_amount($game->id, $current_pot_number) : $current_pot_amount;

      // decide on the player move
      // if no one has a move yet, create a move for first user who joined
      // if first user already completed a move or the move has expired, create a move for the second user
      $move = new container();
      if((!$current_move) || $current_move->has_expired() || $current_move->has_ended()){
        if($current_move && $current_move->has_expired()){
          // update the value
          $current_move->close(0, "fold", "Folded ".$user->username."'s cards because of inactivity. Current pot value is at: $".$current_pot_amount);
          // end the game now since fold is an immediate loss
          $game->round_won = true;
        }

        if(!isset($game->round_won)){
          // create a new move for the next eligible player if game has not been already won
          $player_ids        =  $game->get_player_ids();
          $current_player_id =  $current_move->user_id;
          $next_player_id    =  $player_ids[array_rand($player_ids)];
          if($current_player_id){
            foreach($player_ids as $player_id){
              if($player_id != $current_player_id){
                $next_player_id = $player_id;
                break;
              }
            }
          }
          // create a move for the new player
          $current_round_number = isset($current_move->round) ? $current_move->round : 0;
          if(($current_round_number === 0) && ($game->get_total_moves($current_pot_number) === 0)){
            // at the start of each round, put money into the pot for each user
            // by default, it is 50 but if some user has less than 50 - the value becomes that amount

            $start_pot_money    = 50;
            $user_amount        = $game->get_amount($user->id);
            $player_other_amount= $game->get_amount($player_other->id);
            if(($user_amount < $start_pot_money) || ($player_other_amount < $start_pot_money)){
              $start_pot_money  = ($user_amount < $start_pot_money) ? $user_amount : $player_other_amount;
            }
            game_move::create_and_close_move($game->id, $user->id, -1, $current_pot_number, $start_pot_money,
                                             "Round start: ".$user->username." puts $".$start_pot_money." in the pot.");
            game_move::create_and_close_move($game->id, $player_other->id, -1, $current_pot_number, $start_pot_money,
                                             "Round start: ".$player_other->username." puts $".$start_pot_money." in the pot.");

            $game->update_amount($user->id, ($user_amount - $start_pot_money));
            $game->update_amount($player_other->id, ($player_other_amount - $start_pot_money));
          }

          game_move::create_move(
              $game->id,
              $next_player_id,
              game_move::get_suitable_round_number($game->id, $current_round_number),
              $current_pot_number
            );
          $current_move = game_move::get_current_move($game->id);
        }
      }

      // see if we can calculate the current phase of the game
      if(($current_move->round === 1) && ($game->phase != "community")){
        // update game phase to community
        $game->update_phase("community");
      }
      else if(($current_move->round === 2) && ($game->phase != "turn")){
        // update game phase to turn
        $game->update_phase("turn");
      }
      else if(($current_move->round === 3) && ($game->phase != "river")){
        // update game phase to river
        $game->update_phase("river");
      }
      else if(($current_move->round >= 4) || (isset($game->round_won))){
        // game ends at round 4
        $round_detail =   "Round ended in draw";
        $winner_hand  =   $game->get_winner();
        $pot_amount   =   game_move::get_current_pot_amount($game->id, $current_pot_number);
        $current_move =   game_move::get_current_move($game->id);

        if($winner_hand){
          $round_detail = "Round was won by ".$winner_hand->player->username;
          $user_amount  = $game->get_amount($winner_hand->player->id);
          if(($current_move->round == 4) || ($current_move->type == "fold")){
            game_move::create_and_close_move($game->id, $winner_hand->player->id, 5, $current_pot_number, 0,
                                             $round_detail." who got ".$winner_hand->description."; wins $".$pot_amount." in the pot.");
            $game->update_amount($winner_hand->player->id, ($user_amount + $pot_amount));
          }
        }
        else{
          // this was a draw - give 50% of the pot to yourselves if the result is a draw
          $won_pot_share  = 0.5*$pot_amount;
          if(($current_move->round == 4) || ($current_move->type == "fold")){
            foreach($game->get_players() as $game_player){
              $user_amount    = $game->get_amount($game_player->id);
              game_move::create_and_close_move($game->id, $game_player->id, 5, $current_pot_number, 0,
                                               $round_detail.": ".$game_player->username." wins $".$won_pot_share." in the pot.");
              $game->update_amount($game_player->id, ($user_amount + $won_pot_share));
            }
          }
        }
        // show the cards for other user now
        $result->player_other->cards= $game->get_cards_dealt_for_user($player_other);
        $game->update_phase("unstarted");
        $result->round_complete = true;
      }

      if(isset($result->round_complete)){
        $move->description  = $round_detail.". Next round in ... ";
        $move->show_controls= 0;
        $move->time_left    = 150;
        $move->stop_timer   = 1;
      }else{
        // make sure the player knows it is his/her move
        if($current_move->user_id === $user->id){
          $move->description  = "Your move";
          $move->show_controls= 1;
        }else{
          $move->description  = "Your opponent's move";
          $move->show_controls= 0;
        }
        $move->time_left  = $current_move->get_time_left();
        $move->stop_timer = 0;
      }
      $result->move     = $move;

      // game community cards
      if(in_array($game->phase, array("community", "turn", "river", "done"))){
        $result->community_cards    = $game->get_community_cards();
      }
      $code = 200;

      // get player money details last
      $result->player_you->amount     = $game->get_amount($user->id);
      $result->player_you->pot_amount = game_move::get_user_pot_amount($game->id, $user->id, $current_pot_number);

      $result->player_other->amount   = $game->get_amount($player_other->id);
      $result->player_other->pot_amount = game_move::get_user_pot_amount($game->id, $player_other->id, $current_pot_number);

      $result->pot_amount = game_move::get_current_pot_amount($game->id, $current_pot_number);
    }
    $json->game   = $result;
  }
  else if($type == "gamelog"){
    $game_id      = $user->get_active_game();
    $game         = game::load_by_id($game_id);
    $json->gamelog= $game->get_game_log();
    $code = 200;
  }

  $json->code = $code;
  echo $json->json_encode();
