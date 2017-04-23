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
        $this->insert_cards();
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


    // insert cards associated with the game to the db
    function insert_cards(){
      $db     = database::get_db();
      $query  = $db->prepare("INSERT INTO `game_cards`(`game_id`, `card_id`)
                              SELECT ?, `card_id` FROM cards");
      $query->bind_param("s", $this->id);
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
        $query->bind_param("i", $this->get_id());
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

    function join($user){
      $db     = database::get_db();
      $query  = $db->prepare("INSERT INTO `game_players`(`game_id`, `user_id`)
                              VALUES(?,?)");
      $query->bind_param("ii", $this->id, $user->id);
      $query->execute();

      return ($query->insert_id > 0) ? true : false;
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
      $db     = database::get_db();
      $query  = $db->prepare("INSERT INTO game_cards(`game_id`, `card_id`, `user_id`, `community_card`)
                              VALUES(?,?,?,?)");
      $query->bind_param("iiis", $this->id, $card_id, $user_id, $community_card);
      $query->execute();
    }

    // for the current game id, get the cards dealt for the user
    function get_cards_dealt_for_user($user){
      $cards  = array();
      $db     = database::get_db();
      $query  = $db->prepare("SELECT cards.suit AS suit, cards.value AS value
                              FROM cards INNER JOIN game_cards ON
                              cards.id = game_cards.card_id
                              WHERE game_cards.game_id=? AND game_cards.user_id=?");
      $query->bind_param("ii", $this->id, $user->id);
      $query->execute();

      $result = $query->get_result();
      while($row = $result->fetch_assoc()){
        $card        = new container();
        $card->suit  = $row["suit"];
        $card->value = $row["value"];
        $cards[]     = $card;
      }
      return $cards;
    }

    function get_community_cards($type){
      $cards  = array();
      $db     = database::get_db();
      $query  = $db->prepare("SELECT cards.suit AS suit, cards.value AS value
                              FROM cards INNER JOIN game_cards ON
                              cards.id = game_cards.card_id
                              WHERE game_cards.game_id=? AND game_cards.community_card!=?
                              ORDER BY game_cards.id ASC");
      $query->bind_param("is", $this->id, "user");
      $query->execute();

      $result = $query->get_result();
      while($row = $result->fetch_assoc()){
        $card        = new container();
        $card->suit  = $row["suit"];
        $card->value = $row["value"];
        $cards[]     = $card;
      }
      return $cards;
    }


  }
