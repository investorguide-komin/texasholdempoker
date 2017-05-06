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
    $result       = new container();
    $game_id      = $user->get_active_game();
    $game         = game::load_by_id($game_id);

    if($game->phase != "done")  // no need to keep polling for a game that has been completed
    {
      $player_other = $game->get_other_player($user);
      if($player_other && ($player_other->id != $user->id)) // make sure there is another player that is not the same user
      {
        $result->players[] = $user;
        $result->players[] = $player_other;

        // game players
        $result->player_you  = new container();
        $result->player_other= new container();
        $result->player_you->name   = $user->username;
        $result->player_other->name = $player_other->username;

        // at the start of each round, randomize and give two cards to each player
        // game player cards
        $result->player_you->cards  = $game->get_cards_dealt_for_user($user);
        $result->player_other->cards= array();
        if(!count($result->player_you->cards) && ($user->id > $player_other->id)){
          // deal the cards if not dealt already -
          // only deal when the user_id is greater than the other id (this is to prevent race conditions)
          $game->deal_cards($user->id, $player_other->id);
          $result->player_you->cards  = $game->get_cards_dealt_for_user($user);
          $result->player_other->cards= array();
        }

        $current_pot_amount = 0;
        $current_pot_number = game_move::get_current_pot_number($game->id);
        $current_move       = game_move::get_current_move($game->id, $current_pot_number);
        // this means last round has ended
        if($current_move->round == 5){
          $current_pot_number++;
          $current_move = null;
        }

        // process the player move if this is a "trigger" action
        // make sure only the user with the current active move can make a change though - reject all inputs from the other player at this time
        if(($type === "trigger") && ($game->get_user_id_with_active_move($current_pot_number) === $user->id)){
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
            $current_round_number = isset($current_move->round) ? $current_move->round : -1;
            if(($current_round_number === -1) && ($game->get_total_moves($current_pot_number) === 0)){
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
              $current_round_number = -1;
            }

            game_move::create_move(
                $game->id,
                $next_player_id,
                game_move::get_suitable_round_number($game->id, $current_round_number),
                $current_pot_number
              );
            $current_move = game_move::get_current_move($game->id, $current_pot_number);
          }
        }

        if(($current_move->round == 4) || (isset($game->round_won)))
        {
          // game ends at round 4
          $pot_amount         =   game_move::get_current_pot_amount($game->id, $current_pot_number);
          $current_move       =   game_move::get_current_move($game->id, $current_pot_number);
          $game_round_result  =   $game->get_result($current_pot_number);

          if($game_round_result->result_type == "win"){
            $winner       =   user::load_by_id($game_round_result->players[0]);
            $round_detail =   "Round was won by ".$winner->username." because ".$game_round_result->reason;
            $user_amount  = $game->get_amount($winner->id);

            game_move::create_and_close_move($game->id, $winner->id, 5, $current_pot_number, 0,
                                             $round_detail." ; Wins $".$pot_amount." in the pot.");
            $game->update_amount($winner->id, ($user_amount + $pot_amount));
          }
          else{
              // this was a draw - give 50% of the pot to yourselves if the result is a draw
              $won_pot_share  = 0.5 * $pot_amount;
              $round_detail   = "Round was drawn because of equal hands";
              foreach($game_round_result->players as $player_id){
                $player       = user::load_by_id($player_id);
                $user_amount  = $game->get_amount($player->id);
                game_move::create_and_close_move($game->id, $player->id, 5, $current_pot_number, 0,
                                                 $round_detail." ; ".$game_player->username." wins $".$won_pot_share." in the pot.");
                $game->update_amount($player->id, ($user_amount + $won_pot_share));
              }
          }

          // show the cards for other user now
          $result->player_other->cards  = $game->get_cards_dealt_for_user($player_other);
          $result->round_complete       = true;
        }
        else if($user->id > $player_other->id)  // to avoid race condition, just allowing card dealing for one user
        {
          // see if we can calculate the current phase of the game
          if(($current_move->round === 1) && (!$game->has_cards("community", $current_pot_number))){
              $game->update_phase("community", $current_pot_number);
          }
          else if(($current_move->round === 2) && (!$game->has_cards("turn", $current_pot_number))){
            // update game phase to turn
            $game->update_phase("turn", $current_pot_number);
          }
          else if(($current_move->round === 3) && (!$game->has_cards("river", $current_pot_number))){
            // update game phase to river
            $game->update_phase("river", $current_pot_number);
          }
        }

        if(($user->id > $player_other->id) && ($game->has_been_won($result->round_complete))){
          $description        = "Game was won by ".$game->get_winner()->username;
          $move->description  = $description;
          $move->game_complete= 1;

          $description  = $description."; Final win amount: ".$game->get_winner()->amount;
          game_move::create_and_close_move($game->id, $game->get_winner()->id, 5, $current_pot_number, 0, $description);
          $game->update_phase("done", $current_pot_number);
        }
        else if(isset($result->round_complete)){
          $move->description  = $round_detail.". Next round in ... ";
          $move->show_controls= 0;
          $move->time_left    = 5;
          $game->update_phase("unstarted", 0);
        }
        else{
          // make sure the player knows it is his/her move
          if($current_move->user_id === $user->id){
            $move->description  = "Your move";
            $move->show_controls= 1;
          }else{
            $move->description  = "Your opponent's move";
            $move->show_controls= 0;
          }
          $move->time_left    = $current_move->get_time_left();
          $move->stop_timer   = 0;
          $move->game_complete= 0;
        }
        $result->move     = $move;

        // game community cards
        if(in_array($game->phase, array("community", "turn", "river"))){
          $result->community_cards    = $game->get_community_cards();
        }
        // get player money details last
        $result->player_you->amount     = $game->get_amount($user->id);
        $result->player_you->pot_amount = game_move::get_user_pot_amount($game->id, $user->id, $current_pot_number);

        $result->player_other->amount   = $game->get_amount($player_other->id);
        $result->player_other->pot_amount = game_move::get_user_pot_amount($game->id, $player_other->id, $current_pot_number);

        $result->pot_amount = game_move::get_current_pot_amount($game->id, $current_pot_number);
        $code = 200;
      }
    }
    $json->game   = $result;
  }
  else if($type == "gamelog"){
    $game_id      = $user->get_active_game();
    $game         = game::load_by_id($game_id);
    $json->gamelog= $game->get_game_log();
    $code = 200;
  }
  else if($type == "test"){
    $game   = game::load_by_id(1);
    $result = $game->get_result(0);
    var_dump($result);
  }

  $json->code = $code;
  echo $json->json_encode();
