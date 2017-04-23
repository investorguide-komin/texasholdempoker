
<style type="text/css">
  .game-table{color:white;background:darkgreen; height:500px; width:100% !important;}
  .fa{color:white;}
  .card{width:50px;height:75px; border:1px solid black; border-radius:5px; background:white; color:black; font-weight:bold; display: inline-block; margin:7px;}
  .card .rank{font-size: 14px; padding-left: 5px; display: block; text-align: left; height: 25px;}
  .card .suit{font-size: 25px; display: block; height: 50px; text-align: center;}
  .button{border:1px solid gray;background:lightgray; color:#555; border-radius:5px; padding:5px 7px; text-underline:none; display:inline-block; margin:3px;}
  .player-actions{float:left;text-align:center;width:50%;height:150px;padding:10px 50px;}
  .game-player-you{float:left;;width:25%;} .game-player-other{float:right;width:25%;}
  .player-you{padding-left:10px;padding-top:10px;} .player-other{padding-right:10px;padding-top:10px;float:left;}
  .player-cards{width: 75%;}
</style>

<? if($this->show_game){ ?>
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
    <div class="col-md-10 col-md-10 col-sm-12 col-xs-12">
      <div class="game-table center">
        <div class="game-dealer" style="height:100px;">
          <div class="text-center">
            <i class="fa fa-user fa-4x"></i><br/>
            Dealer
          </div>
        </div>
        <div class="game-area" style="height:250px;">
          <div class="game-cards text-center" style="height:200px;"></div>
          <div class="game-pot text-center" style="height:50px;"></div>
        </div>
        <div class="game-players"  style="height:150px;">
          <div class="game-player-you" style="height:150px;">
            <div class="player-you player-cards" style="float:right;"></div>
            <div class="player-you text-left">
              <i class="fa fa-user fa-4x"></i><br/>
              <span class="player-name-you">Player 1</span>
            </div>
          </div>

          <div class="player-actions">
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

          <div class="game-player-other" style="height:150px;">
            <div class="player-other player-cards"></div>
            <div class="player-other text-right">
              <i class="fa fa-user fa-4x"></i><br/>
              <span class="player-name-other">Player 2</span>
            </div>
          </div>
          <div style="clear:both"></div>
        </div>
      </div>
    </div>
    <div class="col-md-2 col-md-2 col-sm-12 col-xs-12">
      <h4>Game log</h4>
    </div>
  </div>
<? }else{ ?>
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
    <? if(isset($this->error)){ echo $this->error; }?>
  </div>
<? } ?>
