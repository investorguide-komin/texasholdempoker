<?php

  class game extends container{
    function __construct($args = false){
      parent::__construct($args);
    }

    // create a game and get the game id
    function create($name, $user){
      $db     = database::get_db();
      $query  = $db->prepare("INSERT INTO `game`(`name`, `created_by`) VALUES(?,?)");
      $query->bind_param("si", $name, $user->id);
      $query->execute();

      if($query->insert_id > 0){
        return true;
      }
      return false;
    }

    function load_games($active = 1){
      $games  = array();

      $db     = database::get_db();
      $query  = $db->prepare("SELECT id, name, total_spots, created_by FROM `game` WHERE is_active=?");
      $query->bind_param("i", $active);
      $query->execute();
      $result = $query->get_result();

      while ($row = $result->fetch_assoc()){
          $user             = user::load_by_id($row["created_by"]);
          $creator_name     = isset($user) && isset($user->username) ? $user->username : "N/A";
          unset($row["created_by"]);

          $game                   = new game($row);
          $game->creator          = $creator_name;
          $game->available_spots  = $game->get_available_spots();
          $games[]  = $game;
      }
      return $games;
    }


    function load_by_id($game_id){
      $db     = database::get_db();
      $query  = $db->prepare("SELECT * from `game` WHERE id=? LIMIT 1");
      $query->bind_param("i", $game_id);
      $query->execute();

      $result = $query->get_result();
      while ($row = $result->fetch_assoc()){
        $game   = new game($row);
      }
      return $game;
    }

    function exists(){
      if(isset($this->id) && ($this->id)){
        return true;
      }
      return false;
    }

    function get_other_player($user){
      $player = false;

      $db     = database::get_db();
      $query  = $db->prepare("SELECT user_id FROM `game_players` WHERE `game_id`=? AND `user_id`!= ?");
      $query->bind_param("ii", $this->id, $user->id);
      $query->execute();

      $result = $query->get_result();
      while($row = $result->fetch_assoc()){
        $player   = user::load_by_id($row["user_id"]);
      }
      return $player;
    }

    function get_players(){
      if(!isset($this->players)){
        $players= array();
        $db     = database::get_db();
        $query  = $db->prepare("SELECT user_id FROM `game_players` WHERE `game_id`=?");
        $query->bind_param("i", $this->id);
        $query->execute();
        foreach($query->get_result() as $row){
          //print_r($row);
          $players[]  = user::load_by_id($row["user_id"]);
        }
        $this->players  = $players;
      }
      return $this->players;
    }

    function get_card_ids_player($player_id, $limit){
      $db     = database::get_db();
      $query  = $db->prepare("SELECT card_id FROM game_cards WHERE game_id=?
                              AND user_id=0 AND community_card IS NULL
                              ORDER BY RAND() LIMIT ?");
      $query->bind_param("ii", $this->get_id(), $limit);
      $query->execute();
    }

    function get_available_spots(){
      $available_spots  = 0;
      $db     = database::get_db();
      $query  = $db->prepare("SELECT count(*) as taken_spots FROM game_players
                              INNER JOIN game ON
                              game_players.game_id = game.id
                              WHERE game.id = ?");
      $query->bind_param("i", $this->id);
      $query->execute();

      $result = $query->get_result();
      while ($row = $result->fetch_assoc()){
        $available_spots  = $this->total_spots - $row["taken_spots"];
        if($available_spots < 0){$available_spots = 0;}
      }
      return $available_spots;
    }

    function has_spots_available(){
      return $this->get_available_spots() ? true : false;
    }

    function is_active(){
      return (isset($this->is_active) && ($this->is_active)) ? true : false;
    }

    // check if a user has already joined a game
    function already_joined($user){
      $db     = database::get_db();
      $query  = $db->prepare("SELECT count(*) AS count FROM game_players
                              WHERE game_id = ? AND user_id = ?");
      $query->bind_param("ii", $this->id, $user->id);
      $query->execute();

      $already_joined = 0;
      $result         = $query->get_result();
      while ($row = $result->fetch_assoc()){
        $already_joined  = $row["count"];
      }
      if(!$already_joined){
        // update user's active game
        $user->update_active_game($this->id);
      }
      return $already_joined > 0 ? true : false;
    }

    function get_amount($user_id){
      $amount = 0;
      $db     = database::get_db();
      $query  = $db->prepare("SELECT amount FROM game_players WHERE game_id = ? AND user_id = ?");
      $query->bind_param("ii", $this->id, $user_id);
      $query->execute();

      $result = $query->get_result();
      while($row = $result->fetch_assoc()){
        $amount = $row["amount"];
      }
      return $amount;
    }

    function update_amount($user_id, $amount){
      $db     = database::get_db();
      $query  = $db->prepare("UPDATE game_players SET amount=? WHERE game_id = ? AND user_id = ?");
      $query->bind_param("iii", $amount, $this->id, $user_id);
      $query->execute();
    }

    function join($user){
      $amount = 1000; // always 1000 for each player when starting up for this version of the project

      $db     = database::get_db();
      $query  = $db->prepare("INSERT INTO `game_players`(`game_id`, `user_id`, `amount`)
                              VALUES(?,?,?)");
      $query->bind_param("iii", $this->id, $user->id, $amount);
      $query->execute();
      if($query->insert_id > 0){
        if(!$this->has_spots_available()){
          $this->mark_as_inactive();
        }
        return true;
      }
      return false;
    }

    // mark game as inactive
    function mark_as_inactive(){
      $db     = database::get_db();
      $query  = $db->prepare("UPDATE game SET is_active = 0 WHERE id = ?");
      $query->bind_param("i", $this->id);
      $query->execute();
    }

    function deal_cards($user_one_id, $user_two_id){
      $db     = database::get_db();
      $query  = $db->prepare("SELECT cards.id as card_id FROM cards ORDER BY RAND() LIMIT 4");
      $query->execute();
      $result = $query->get_result();

      $index  = 0;
      $user_id= $user_one_id;
      while($row = $result->fetch_assoc()){
        $this->insert_card($row["card_id"], $user_id, "user");
        $index++;

        if(!($index % 2)){
          $user_id  = $user_two_id;
        }
      }
    }

    function insert_card($card_id, $user_id, $community_card){
      $pot_number = game_move::get_current_pot_number($this->id);

      $db     = database::get_db();
      $query  = $db->prepare("INSERT INTO game_cards(`game_id`, `pot_number`, `card_id`, `user_id`, `community_card`)
                              VALUES(?,?,?,?,?)");
      $query->bind_param("iiiis", $this->id, $pot_number, $card_id, $user_id, $community_card);
      $query->execute();
    }

    // for the current game id, get the cards dealt for the user
    function get_cards_dealt_for_user($user){
      $pot_number = game_move::get_current_pot_number($this->id);

      $cards  = array();
      $db     = database::get_db();
      $query  = $db->prepare("SELECT cards.id AS card_id, cards.suit AS suit, cards.value AS value, cards.weight AS weight
                              FROM cards INNER JOIN game_cards ON
                              cards.id = game_cards.card_id
                              WHERE game_cards.game_id=? AND game_cards.user_id=? AND game_cards.pot_number=?
                              ORDER BY cards.weight DESC");
      $query->bind_param("iii", $this->id, $user->id, $pot_number);
      $query->execute();

      $result = $query->get_result();
      while($row = $result->fetch_assoc()){
        $card        = new container();
        $card->id    = $row["card_id"];
        $card->suit  = $row["suit"];
        $card->value = $row["value"];
        $card->weight= $row["weight"];
        $cards[]     = $card;
      }
      return $cards;
    }

    function get_community_cards(){
      $pot_number = game_move::get_current_pot_number($this->id);

      $cards  = array();
      $db     = database::get_db();
      $query  = $db->prepare("SELECT cards.id AS card_id, cards.suit AS suit, cards.value AS value, cards.weight AS weight
                              FROM cards INNER JOIN game_cards ON
                              cards.id = game_cards.card_id
                              WHERE game_cards.game_id=? AND game_cards.community_card!='user' AND game_cards.pot_number=?
                              ORDER BY game_cards.id ASC");
      $query->bind_param("ii", $this->id, $pot_number);
      $query->execute();

      $result = $query->get_result();
      while($row = $result->fetch_assoc()){
        $card        = new container();
        $card->id    = $row["card_id"];
        $card->suit  = $row["suit"];
        $card->value = $row["value"];
        $card->weight= $row["weight"];
        $cards[]     = $card;
      }
      return $cards;
    }

    function get_player_ids(){
      $player_ids = array();
      $db     = database::get_db();
      $query  = $db->prepare("SELECT user_id FROM game_players WHERE game_id=?");
      $query->bind_param("i", $this->id);
      $query->execute();
      $result = $query->get_result();
      while($row = $result->fetch_assoc()){
        $player_ids[] = $row["user_id"];
      }
      return $player_ids;
    }

    function update_phase($phase){
      $db     = database::get_db();
      $query  = $db->prepare("UPDATE game set phase=? WHERE id=?");
      $query->bind_param("si", $phase, $this->id);
      $query->execute();

      $this->deal_community_cards($phase);
    }

    function deal_community_cards($phase){
      $pot_number = game_move::get_current_pot_number($this->id);
      $limit      = ($phase === "community") ? 3:1;

      $db     = database::get_db();
      $query  = $db->prepare("SELECT cards.id AS card_id FROM cards
                              WHERE id NOT IN(SELECT card_id FROM game_cards WHERE pot_number=?)
                              ORDER BY RAND() LIMIT ?");
      $query->bind_param("ii", $pot_number, $limit);
      $query->execute();

      $result = $query->get_result();
      while($row = $result->fetch_assoc()){
        $this->insert_card($row["card_id"], 0, $phase);
      }
    }

    function get_game_log(){
      $game_log = "";

      $db     = database::get_db();
      $query  = $db->prepare("SELECT `end_time`, `description` FROM `game_moves`
                              WHERE game_id=? AND end_time != '0000-00-00 00:00:00'
                              ORDER BY id DESC");
      $query->bind_param("i", $this->id);
      $query->execute();
      $result = $query->get_result();
      while($row = $result->fetch_assoc()){
        $game_log.="<b>".$row["end_time"]."</b> : ".$row["description"]."<br/>";
      }
      return $game_log;
    }

    function get_total_moves($pot_number){
      $total_moves  = 0;
      $db     = database::get_db();
      $query  = $db->prepare("SELECT COUNT(*) AS total_moves FROM game_moves WHERE game_id=? AND pot_number=?");
      $query->bind_param("ii", $this->id, $pot_number);
      $query->execute();
      $result = $query->get_result();
      while($row = $result->fetch_assoc()){
        $total_moves = $row["total_moves"];
      }
      return $total_moves;
    }

    function get_user_id_with_active_move(){
      $game_move  = game_move::get_current_move($this->id);
      if($game_move && isset($game_move->user_id)){
        return $game_move->user_id;
      }
      return 0;
    }


    function check_validity_of_action($pot_action, $pot_money_bet, $amount){
        if(
            is_int($pot_money_bet) &&
            in_array($pot_action, array("fold", "raise", "check", "all in")) &&
            ($pot_money_bet >= 0) &&
            ($pot_money_bet <= $amount)
            // check if raised with 0 money - that should not be allowed
          )
        {
          return true;
        }
        return false;
    }

    // called once the whole set of rounds within a game has ended or a player has folded
    function get_winner(){
      $move= game_move::get_current_move($this->id);
      if($move->type == "fold"){
        foreach($this->get_players() as $player){
          if($player->id != $move->user_id){
            $winner_hand  = new container();
            $winner_hand->player =  $player;
            break;
          }
        }
      }
      else{
        $winner_hand= null;
        $hands      = array();
        $community_cards  = $this->get_community_cards();

        foreach($this->get_players() as $player){
          $cards  = $this->get_cards_dealt_for_user($player);
          $hand   = $this->calculate_hand($cards, $community_cards);
          $hand->player = $player;
          $hands[]= $hand;
        }

        // this is always 2 because we only have 2 players
        if(count($hands) == 2){
          if($hands[0]->hand === $hands[1]->hand){
            if($hands[0]->high_card->weight > $hands[1]->high_card->weight){
              $winner_hand= $hands[0]->hand;
            }else if($hands[0]->high_card->weight < $hands[1]->high_card->weight){
              $winner_hand= $hands[1]->hand;
            }else if($hands[0]->high_card->weight == $hands[1]->high_card->weight){
              $winner_hand = null;
            }
          }
          else if($hands[0]->hand > $hands[1]->hand){
            $winner_hand= $hands[0]->hand;
          }
          else{
            $winner_hand= $hands[1]->hand;
          }
        }
      }
      return $winner_hand;
    }

    function get_card_numerical_value($card_value){
      switch($card_value){
        case "A": $card_value = 1;
                  break;
        case "J": $card_value = 11;
                  break;
        case "Q": $card_value = 12;
                  break;
        case "K": $card_value = 13;
                  break;
      }
      return $card_value;
    }

    function get_card_royal_numerical_value($card_weight, $card_suit){
      $suit = 100;
      if($card_suit === "K"){$suit = 200;}
      else if($card_suit === "C"){$suit = 300;}
      else if($card_suit === "D"){$suit = 400;}
      return ($card_weight + $suit);
    }

    function get_same_counts($cards){
      $same_counts  = array();
      $second_cards = $cards;

      for($i=0; $i<count($cards); $i++){
        for($j=0; $j<count($second_cards); $j++){
          if(($cards[$i]->value === $second_cards[$j]->value) && ($i != $j)){
            if(isset($same_counts[$cards[$i]->value])){
              $same_counts[$cards[$i]->value]  = 1;
            }else{
              $same_counts[$cards[$i]->value]++;
            }
          }
        }
      }
      arsort($same_counts);
      return $same_counts;
    }

    function get_same_count($same_counts){
      return array_slice($same_counts, 0, 1);
    }

    function get_pair_count($same_counts){
      $pair_count = 0;
      foreach($same_counts as $same_count){
        if($same_count >= 2){
          $pair_count++;
        }
      }
      return $pair_count;
    }

    // should be a straight and a flush before using this algorithm
    function is_a_royal_flush($cards){
      $royal_flush_count = 0;
      $card_values = array();
      foreach($cards as $card){
        $card_values[] = $this->get_card_royal_numerical_value($card->weight, $card->suit);
      }
      sort($card_values);
      for($i=0; $i<count($card_values)-1; $i++){
        if(($card_values[$i] - $card_values[$i+1]) === 1){
          $royal_flush_count++;
        }
      }
      return ($royal_flush_count >= MIN_ROYAL_FLUSH_CARDS) ? true : false;
    }

    function is_a_straight($cards){
      $straight_count = 0;
      $card_values = array();
      foreach($cards as $card){
        $card_values[] = $this->get_card_numerical_value($card->value);
      }
      sort($card_values);
      for($i=0; $i<count($card_values)-1; $i++){
        if(($card_values[$i] - $card_values[$i+1]) === 1){
          $straight_count++;
        }
      }
      return ($straight_count >= MIN_STRAIGHT_CARDS) ? true : false;
    }

    function is_a_flush($cards){
      $flush_count    = 0;
      $diamonds_count = 0;
      $hearts_count   = 0;
      $spades_count   = 0;
      $clubs_count    = 0;
      foreach($cards as $card){
        switch($card->suit){
          case "D": $diamonds_count++;
                    break;
          case "H": $hearts_count++;
                    break;
          case "S": $spades_count++;
                    break;
          case "C": $clubs_count++;
                    break;
        }
      }
      if(($diamonds_count >= MIN_FLUSH_CARDS) ||
        ($hearts_count >= MIN_FLUSH_CARDS) ||
        ($spades_count >= MIN_FLUSH_CARDS) ||
        ($clubs_count >= MIN_FLUSH_CARDS))
      {
            return true;
      }
      return false;
    }

    function is_a_fullhouse($cards){
      $counts = array();
      foreach($cards as $card){
        if(isset($counts[$card->value])){
          $counts[$card->value]++;
        }else{
          $counts[$card->value] = 1;
        }
      }
      arsort($counts);
      $first_count    = array_slice($counts, 0, 1);
      $second_count   = array_slice($counts, 1, 1);
      if(($first_count >= MIN_FULLHOUSE_FIRSTCOUNT) && ($second_count >= MIN_FULLHOUSE_SECONDCOUNT)){
        return true;
      }
      return false;
    }


    // for sake of sanity not checking for cases when a user might have a higher straight than the other user
    // in those cases, just using the high card to calculate the winner
    function calculate_hand($cards, $community_cards)
    {
      $is_royal_flush     = false;
      $is_straight_flush  = false;
      $total_cards        = array_merge($cards, $community_cards);

      $is_straight    = $this->is_a_straight($total_cards);
      $is_flush       = $this->is_a_flush($total_cards);
      $is_fullhouse   = $this->is_a_fullhouse($total_cards);

      $same_counts    = $this->get_same_counts($total_cards);
      $pair_count     = $this->get_pair_count($same_counts);
      $same_count     = $this->get_same_count($same_counts);

      if($is_straight && $is_flush){
        $is_straight_flush= true;
        $is_royal_flush   = $this->is_a_royal_flush($total_cards);
      }

      // ******* ORDER OF IMPORTANCE *********
      // royal flush
      // straight flush
      // four of a kind
      // full house
      // flush
      // straight
      // three of a kind
      // two pair
      // pair
      // high card

      $game_hand  = new container();
      if($is_royal_flush){
        $game_hand->hand = HAND_ROYAL_FLUSH;
        $game_hand->description = "Royal Flush";
      }
      else if($is_straight_flush){
        $game_hand->hand = HAND_STRAIGHT_FLUSH;
        $game_hand->description = "Straight Flush";
      }
      else if($same_count == 4){
        $game_hand->hand = HAND_FOUR_OF_A_KIND;
        $game_hand->description = "Four of a kind";
      }
      else if($is_fullhouse){
        $game_hand->hand = HAND_FULL_HOUSE;
        $game_hand->description = "Full House";
      }
      else if($is_flush){
        $game_hand->hand = HAND_FLUSH;
        $game_hand->description = "Flush";
      }
      else if($is_straight){
        $game_hand->hand = HAND_STRAIGHT;
        $game_hand->description = "Straight";
      }
      else if($same_count == 3){
        $game_hand->hand = HAND_THREE_OF_A_KIND;
        $game_hand->description = "Three of a kind";
      }
      else if($pair_count == 2){
        $game_hand->hand = HAND_TWO_PAIR;
        $game_hand->description = "Two pair";
      }
      else if($pair_count == 1){
        $game_hand->hand = HAND_PAIR;
        $game_hand->description = "Pair";
      }
      else{
        $game_hand->hand = HAND_HIGH_CARD;
        $game_hand->description = "High card";
      }
      // high card
      $game_hand->high_card  = $cards[0];  // high card is always cards[0] since the card value is obtained by order of weightage
      return $game_hand;
    }


    // after selecting a winner for current round,
    // see if the game should progress or the game must end
    function plan_next_move(){

    }

  }
