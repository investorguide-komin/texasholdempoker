<?php

  class game extends container{
    function __construct($args = false){
      parent::__construct($args);
    }

    // create a game and get the game id
    function create($name){
      $db     = database::get_db();
      $query  = $db->prepare("INSERT INTO `game`(`name`) VALUES(?)");
      $query->bind_param("s", $name);
      $query->execute();

      if($query->lastInsertId() > 0){
        $this->insert_cards();
        return true;
      }
      return false;
    }

    function load_games($active = 1){
      $games  = array();

      $db     = database::get_db();
      $query  = $db->prepare("SELECT * FROM `game` WHERE is_active=?");
      $query->bind_param("i", $active);
      $query->execute();
      $result = $query->get_result();
      while ($row = $result->fetch_assoc()){
          $games[]  = new game($row);
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
      $query->bindParam(1, $game_id, PDO::PARAM_INT);
      $query->execute();

      $game   = $query->fetchAll();
      var_dump($game);
      return $game;
    }

    // update the player2 who can join
    function join($player_id){
      $db     = database::get_db();
    }

    function get_players(){
      if(!isset($this->players)){
        $players= array();
        $db     = database::get_db();
        $query  = $db->prepare("SELECT user_id FROM `game_players` WHERE `game_id`=?");
        $query->bind_param("i", $this->get_id());
        $query->execute();

        foreach($query->get_result() as $row){
          print_r($row);
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

      var_dump($query->get_result());
    }

    function get_cards_player($player_id, $limit){
      $card_ids = $this->get_card_ids_player($player_id, $limit);

      foreach($card_ids as $card_id){

      }

    }

    // deal cards for both players
    function deal(){
      $cards    = array();
      $players  = $this->get_players();
      foreach($players as $player){
        $cards[$player->get_id()] = $game->get_cards_player($player->get_id(), 2);
        $cards[$player->get_id()] = $game->get_cards_player($player->get_id(), 2);
      }
    }

    function community_card($type){

    }

  }
