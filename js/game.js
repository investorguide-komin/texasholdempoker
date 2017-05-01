window.periodic_poll_game = false;

document.addEventListener("DOMContentLoaded", function(event) {
  function load_cards_community(card_type){
    $.ajax({
      url: "ajax/cards.php?type=community",
      async: true,
      cache: false,
      type: "post",
      data: {card_type:card_type},
      success: function(data){
        var data  = $.parseJSON(data);
        if(data != undefined){
          var community_cards = data.cards;
          $.each(community_cards, function(i, community_card){
            // handle the community card (maybe append to a div)
            $(".game-cards").append(get_card(community_card));
          });
        }
      }
    });
  }

  function get_card_suit(suit){
    if(suit == "S"){
      return "&spades;";
    }else if(suit == "H"){
      return "&hearts;";
    }else if(suit == "D"){
      return "&diams;";
    }else if(suit == "C"){
      return "&clubs;";
    }
  }

  function get_card_color(suit){
    if(suit == "D" || suit == "H"){
      return "red";
    }
    return "black";
  }

  function get_card(card){
    return '<div class="card" style="color:'+get_card_color(card.suit)+'">'+
                '<span class="rank">'+card.value+'</span>'+
                '<span class="suit">'+get_card_suit(card.suit)+'</span>'+
            '</div>';
  }

  // update the game log periodically
  function update_game_log(){
    $.ajax({
      url: "ajax/game.php?type=gamelog",
      async: true,
      cache: false,
      type: "post",
      success: function(data){
        var data = $.parseJSON(data);
        if(data.code == 200){
          $(".game-log").html(data.gamelog);
        }
      }
    });
  }

  // update the game periodically with valid values obtained from the server
  function update_game(){
    if(!window.periodic_poll_game){
      $.ajax({
        url: "ajax/game.php?type=sync",
        type: "post",
        success: function(data){
          var data = $.parseJSON(data);
          if(data.code == 200){
            update_game_values(data);
          }
        }
      });
    }
  }

  function update_game_values(data){
    console.log(data);
    var game = data.game;
    var player_you  = game.player_you;
    var player_other= game.player_other;
    var community_cards = game.community_cards;

    $(".game-pot").html(game.pot_amount);
    $(".player-name-you").html(player_you.name);
    $(".player-you .player-money").html(player_you.amount);
    $(".game-player-you-pot").html(player_you.pot_amount);

    $(".player-name-other").html(player_other.name);
    $(".player-other .player-money").html(player_other.amount);
    $(".game-player-other-pot").html(player_other.pot_amount);

    update_button_max(player_you.amount);
    update_check_button(player_you.amount, player_you.pot_amount, player_other.pot_amount);

    var player_you_cards  = "";
    var player_other_cards= "";
    for(var i=0; i<player_you.cards.length;i++){
      player_you_cards+=(get_card(player_you.cards[i]));
    }
    if(player_other.cards.length > 0){
      for(var i=0; i<player_other.cards.length;i++){
        player_other_cards+=(get_card(player_other.cards[i]));
      }
    }else{
      player_other_cards = '<div class="card" style="background:gray;"></div><div class="card" style="background:gray;"></div>';
    }
    $(".player-you.player-cards").html(player_you_cards);
    $(".player-other.player-cards").html(player_other_cards);

    var community_cards = "";
      if(data.game.community_cards != null){
      for(var i=0;i<data.game.community_cards.length;i++){
        community_cards+=get_card(data.game.community_cards[i]);
      }
    }
    $(".game-cards").html(community_cards);
    update_game_log();

    if($(".game-move-description").length){
      $(".game-move-description").html(game.move.description);
      $(".game-move-time-left").html(game.move.time_left);
      if(game.move.show_controls){
        $(".player-actions").show();
      }else{
        $(".player-actions").hide();
      }

      if(parseInt(game.move.stop_timer) == 1){
        console.log("inside stop timer");
        console.log(window.periodic_poll_game);
        window.periodic_poll_game = true;
      }
    }
  }

  if($(".game-table").length){
    update_game();
    setInterval(function(){
      update_game();
    }, 5000);

    setInterval(function(){
      update_timer();
    }, 1000);

    function update_timer(){
      var time  = parseInt($(".game-move-time-left").html().trim());
      time--;
      if(time >= 0){
        $(".game-move-time-left").html(time);
        if(time == 0){
          update_game();
        }
      }
    }
  }

  function trigger_action(pot_action, pot_money_bet)
  {
    $.ajax({
      url: "ajax/game.php?type=trigger",
      type: "post",
      data: {pot_money_bet:pot_money_bet, pot_action: pot_action},
      success: function(data){
        var data = $.parseJSON(data);
        if(data.code == 200){
          update_game_values(data);
        }
      }
    });
  }

  function get_bet_value(bet_money_span){
    return parseInt(bet_money_span.html().replace("$", "").trim());
  }

  // function to automatically update the range of how much a player can raise (0 - MAX_AMOUNT a user has)
  function update_button_max(max_value){
    $("#raise-range").attr("max", max_value);
    $("#all-in .bet-value").html("$"+max_value);
  }

  function update_check_button(your_total, your_pot, other_pot){
    var check_value = other_pot - your_pot;
    if(check_value > your_total){
      check_value = your_total;
    }
    $("#check .bet-value").html("$"+check_value);
  }

  $("body").on("click", "#check", function(){
    var action = "check";
    trigger_action(action, get_bet_value($(this).find(".bet-value")));
  });

  $("body").on("click", "#all-in", function(){
    var action = "all in";
    trigger_action(action, get_bet_value($(this).find(".bet-value")));
  });

  $("body").on("click", "#raise", function(){
    var action = "raise";
    trigger_action(action, get_bet_value($(this).find(".bet-value")));
  });

  $("body").on("click", "#fold", function(){
    var action = "fold";
    trigger_action(action, 0);
  });

  $("body").on("change", "#raise-range", function(){
    $("#raise .bet-value").html("$"+$(this).val());
  });
});
