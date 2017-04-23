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
    console.log(card);
    return '<div class="card" style="color:'+get_card_color(card.suit)+'">'+
                '<span class="rank">'+card.value+'</span>'+
                '<span class="suit">'+get_card_suit(card.suit)+'</span>'+
            '</div>';
  }

  function make_cards(player_class, cards){
    var target_div = $(".player-cards."+player_class);
    for(var i=0; i<cards.length; i++){
      target_div.append(get_card(cards[i]));
    }
  }

  // update the game periodically with valid values
  function update_game(){
    $.ajax({
      url: "ajax/game.php?type=sync",
      type: "post",
      success: function(data){
        var data = $.parseJSON(data);
        if(data.code == 200){
          var game = data.game;
          var player_you  = game.player_you;
          var player_other= game.player_other;
          var community_cards = game.community_cards;

          $(".player-name-you").html(player_you.name);
          $(".player-name-other").html(player_other.name);

          var player_you_cards  = "";
          var player_other_cards= "";
          for(var i=0; i<player_you.cards.length;i++){
            player_you_cards+=(get_card(player_you.cards[i]));
          }
          for(var i=0; i<player_other.cards.length;i++){
            player_other_cards+=(get_card(player_other.cards[i]));
          }
          $(".player-you.player-cards").html(player_you_cards);
          $(".player-other.player-cards").html(player_other_cards);
        }
      }
    });
  }

  update_game();
  setInterval(function(){
    update_game();
  }, 5000);

  $("body").on("click", "#type-flop", function(){
    load_cards_community("flop");
  });

  $("body").on("click", "#type-turn", function(){
    load_cards_community("turn");
  });

  $("body").on("click", "#type-river", function(){
    load_cards_community("river");
  });
});
