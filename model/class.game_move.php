<?php

  class game_move extends container{
    function __construct($args = false){
      parent::__construct($args);
    }

    // loads the current move by game_id
    function get_current_move($game_id, $pot_number){
      $db     = database::get_db();
      $query  = $db->prepare("SELECT * FROM `game_moves` WHERE game_id=? AND pot_number=? ORDER BY id DESC LIMIT 1");
      $query->bind_param("ii", $game_id, $pot_number);
      $query->execute();
      $result = $query->get_result();

      while($row = $result->fetch_assoc()){
        return new game_move($row);
      }
      return false;
    }

    // get current pot_number
    function get_current_pot_number($game_id){
      $pot_number = 0;

      $db     = database::get_db();
      $query  = $db->prepare("SELECT pot_number FROM `game_moves` WHERE game_id = ? ORDER BY pot_number DESC LIMIT 1");
      $query->bind_param("i", $game_id);
      $query->execute();
      $result = $query->get_result();
      while($row = $result->fetch_assoc()){
        $pot_number = $row["pot_number"];
      }
      return $pot_number;
    }

    function get_current_pot_amount($game_id, $pot_number){
      $pot_amount = 0;

      $db     = database::get_db();
      $query  = $db->prepare("SELECT SUM(`pot_amount_change`) AS `pot_amount` FROM `game_moves`
                              WHERE `game_id`=? AND `pot_number`=?");
      $query->bind_param("ii", $game_id, $pot_number);
      $query->execute();
      $result = $query->get_result();
      while($row = $result->fetch_assoc()){
        $pot_amount = $row["pot_amount"];
      }
      return $pot_amount;
    }

    function get_user_pot_amount($game_id, $user_id, $pot_number){
      $pot_amount = 0;

      $db     = database::get_db();
      $query  = $db->prepare("SELECT SUM(`pot_amount_change`) AS `pot_amount` FROM `game_moves`
                              WHERE `game_id`=? AND `user_id`=? AND `pot_number`=?");
      $query->bind_param("iii", $game_id, $user_id, $pot_number);
      $query->execute();
      $result = $query->get_result();
      while($row = $result->fetch_assoc()){
        $pot_amount = $row["pot_amount"];
      }
      return $pot_amount;
    }

    function get_time_left(){
      $time_left  = MAX_GAME_TIME - (strtotime("now") - strtotime($this->start_time));
      return (($time_left > 0) ? $time_left : 0);
    }

    function has_ended(){
      if($this->end_time !== "0000-00-00 00:00:00"){
        return true;
      }
      return false;
    }

    function has_expired(){
      date_default_timezone_set('America/New_York');
      if((strtotime("now") - strtotime($this->start_time)) > MAX_GAME_TIME){
        return true;
      }
      return false;
    }

    function create_move($game_id, $user_id, $round_number, $pot_number){
      $db     = database::get_db();
      $query  = $db->prepare("INSERT INTO game_moves(`game_id`, `user_id`, `round`, `pot_number`, `start_time`)
                              VALUES(?,?,?,?,NOW())");
      $query->bind_param("iiii", $game_id, $user_id, $round_number, $pot_number);
      $query->execute();
    }

    function create_and_close_move($game_id, $user_id, $round_number, $pot_number, $pot_amount_change, $description){
      $db     = database::get_db();
      $query  = $db->prepare("INSERT INTO game_moves(`game_id`, `user_id`, `round`, `pot_number`, `pot_amount_change`, `description`, `start_time`, `end_time`)
                              VALUES(?,?,?,?,?,?,NOW(),NOW())");
      $query->bind_param("iiiiis", $game_id, $user_id, $round_number, $pot_number, $pot_amount_change, $description);
      $query->execute();
    }

    function close($pot_amount, $type, $description){
      $db     = database::get_db();
      $query  = $db->prepare("UPDATE game_moves SET end_time=NOW(), pot_amount_change=?, type=?, description=?
                              WHERE id=?");
      $query->bind_param("issi", $pot_amount, $type, $description, $this->id);
      $query->execute();
    }

    function get_suitable_round_number($game_id, $current_round_number = -1){
      $round_number = $current_round_number;
      $db     = database::get_db();
      $query  = $db->prepare("SELECT COUNT(*) as count, type FROM game_moves
                              WHERE game_id=? AND `round`=?
                              ORDER BY id DESC");
      $query->bind_param("ii", $game_id, $round_number);
      $query->execute();
      $result = $query->get_result();
      while($row = $result->fetch_assoc()){
        // since we only allow two players for now, the only way a round won't end normally is if a player makes a raise
        if(($row["count"] >= 2) && ($row["type"] !== "raise")){
          $round_number++;
        }
      }
      return $round_number;
    }

    function get_human_readable_action($username, $pot_action, $pot_money_bet){
      return $username." calls ".$pot_action.", $".$pot_money_bet." added to the pot";
    }

  }
