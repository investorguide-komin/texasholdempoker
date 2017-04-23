<?php

  class game_move extends container{
    function __construct($args = false){
      parent::__construct($args);
    }

    // loads the current move by game_id
    function get_current_move($game_id){
      $db     = database::get_db();
      $query  = $db->prepare("SELECT * FROM `game_moves` WHERE game_id=?");
      $query->bind_param("i", $game_id);
      $query->execute();
      $result = $query->get_result();

      while($row = $result->fetch_assoc()){
        return new game_move($row);
      }
      return false;
    }

    

  }
