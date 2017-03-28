
<style type="text/css">
  .game-table{color:white;background:darkgreen; height:500px; width:100% !important;}
  .fa{color:white;}

  .card{width:50px;height:75px; border:1px solid black; border-radius:5px;
    background:white; color:black; font-weight:bold; display: inline-block; margin:7px;}
  .card .rank{font-size: 14px;
    padding-left: 5px;
    display: block;
    text-align: left;
    height: 25px;}
  .card .suit{font-size: 25px;
    display: block;
    height: 50px;
    text-align: center;}
  .button{border:1px solid gray;background:lightgray; color:#555; border-radius:5px; padding:5px 7px; text-underline:none; display:inline-block; margin:3px;}
</style>

<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
  <div class="col-md-10 col-md-10 col-sm-12 col-xs-12">
    <div class="game-table center">
      <div class="game-dealer" style="height:100px;border-bottom:1px solid red;">
        <div class="text-center">
          <i class="fa fa-user fa-4x"></i><br/>
          Dealer
        </div>
      </div>
      <div class="game-cards" style="height:250px;border-bottom:1px solid red;">
      </div>
      <div class="game-players"  style="height:150px;">
        <div class="game-player-1" style="float:left;height:150px;width:25%;">
          <div class="player-1 player-cards" style="float:right;">
          </div>
          <div class="player-1 text-left" style="padding-left:10px;padding-top:10px;">
            <i class="fa fa-user fa-4x"></i><br/>
            Player 1
          </div>
        </div>

        <div class="player-actions" style="float:left;text-align:center;width:50%;height:150px;padding:10px 50px;">
          <a href="javascript:;" class="button">
            CHECK
          </a>
          <a href="javascript:;" class="button">
            FOLD
          </a>
          <a href="javascript:;" class="button">
            ALL IN
          </a>
          <br/><br/>
          <input type="range" min="25" max="100"/>
          <a href="javascript:;" class="button">
            RAISE
          </a>
        </div>

        <div class="game-player-2" style="float:right;height:150px;width:25%;">
          <div class="player-2 player-cards" style="float:left;">
          </div>

          <div class="player-2 text-right" style="padding-right:10px;padding-top:10px;">
            <i class="fa fa-user fa-4x"></i><br/>
            Player 2
          </div>
        </div>
        <div style="clear:both"></div>
      </div>
    </div>
  </div>
  <div class="col-md-2 col-md-2 col-sm-12 col-xs-12">
    <b>Poker log</b>
  </div>
</div>


<script type="text/javascript">
    document.addEventListener("DOMContentLoaded", function(event) {
      function load_cards_dealt(){
        $.ajax({
          url: "ajax/cards.php?type=deal",
          async: true,
          cache: false,
          type: "post",
          data: {game_id:1},
          success: function(data){
            var data  = $.parseJSON(data);
            if(data != undefined){
              var cards_dealt = data.cards_dealt;
              var player1 = cards_dealt.player1;
              var player2 = cards_dealt.player2;

              make_cards("player-1", player1);
              make_cards("player-2", player2);
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

      function make_cards(player_class, cards){
        var target_div = $(".player-cards."+player_class);
        for(var i=0; i<cards.length; i++){
          var card_details = cards[i].split("|");
          target_div.append('<div class="card" style="color:'+get_card_color(card_details[1])+'">'+
                              '<span class="rank">'+card_details[0]+'</span>'+
                              '<span class="suit">'+get_card_suit(card_details[1])+'</span>'+
                          '</div>');
        }
      }

      load_cards_dealt();
    });
</script>
