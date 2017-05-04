
<style type="text/css">
  .game-table{color:white;background:darkgreen; height:500px; width:100% !important; border: 5px solid #966F33;}
  .fa{color:white;} .game-dealer{padding-top: 15px;}
  .card{width:50px;height:75px; border:1px solid black; border-radius:5px; background:white; color:black; font-weight:bold; display: inline-block; margin:7px;}
  .card .rank{font-size: 14px; padding-left: 5px; display: block; text-align: left; height: 25px;}
  .card .suit{font-size: 25px; display: block; height: 50px; text-align: center;}
  .button{font-size:14px; border:1px solid gray; background:lightgray; color:#111; font-weight: bold; border-radius:5px; padding:5px 7px; text-decoration:none; display:inline-block; margin:3px;}
  .player-actions{float:left;text-align:center;width:50%;height:150px;padding:25px 50px;}
  .game-player-you{float:left;;width:25%;} .game-player-other{float:right;width:25%;} .bet-value{color:green;}
  .player-you{padding-left:20px;padding-top:10px;} .player-other{padding-right:20px;padding-top:10px;float:left;}
  .game-pot-details{font-size:24px; padding-bottom:10px;} .game-pot-share-details{font-size:12px;}
  .game-move-description{display: inline-block; margin-top:5px; border: 1px solid #444; padding: 3px 15px; background: #ccc; color: #777; font-weight: bold;}
  .game-move-time-left{display: inline-block; background: #222;padding: 5px; border-radius: 50px; width: 30px; height: 30px;}
  .game-players{background: #966F33;}
  .game-log-encloser{padding: 0; overflow: scroll; height: 500px; }
  #raise-range{display: inline-block; width: 150px; margin-top: 25px;}
</style>

<? if($this->show_game){ ?>
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding:0;">
    <div class="col-md-10 col-md-10 col-sm-12 col-xs-12">
      <div class="game-table center">
        <div class="game-dealer" style="height:100px;">
          <div class="text-center">
            <i class="fa fa-user fa-4x"></i><br/>
            Dealer
          </div>
        </div>
        <div class="game-area" style="height:250px;">
          <div class="game-cards text-center" style="height:125px;"></div>
          <div class="game-details text-center" style="height:125px;">
            <div class="game-pot-details">
              Current Game Pot: $<span class="game-pot">0</span>
              <div class="game-pot-share-details">
                Your share: $<span class="game-player-you-pot">0</span>
                &nbsp;&nbsp;
                Competitor share: $<span class="game-player-other-pot">0</span>
              </div>
            </div>
            <div class="game-move-details">
              <span class="game-move-description">Waiting for another player to join</span>
              <span class="game-move-time-left">N/A</span>
            </div>
          </div>
        </div>
        <div class="game-players"  style="height:150px;">
          <div class="game-player-you" style="height:150px;">
            <div class="player-you player-cards" style="float:right;"></div>
            <div class="player-you text-left">
              <i class="fa fa-user-circle-o fa-3x"></i><br/>
              <div class="player-name-you"></div>
              $<span class="player-money">N/A</span>
            </div>
          </div>

          <div class="player-actions">
            <a href="javascript:;" class="button" id="check">
              CHECK <span class="bet-value"></span>
            </a>
            <a href="javascript:;" class="button" id="all-in">
              ALL IN <span class="bet-value"></span>
            </a>
            <a href="javascript:;" class="button" id="fold">
              FOLD
            </a>
            <div class="raise">
              <input type="range" min="0" max="0" id="raise-range"/>
              <a href="javascript:;" class="button" id="raise">
                RAISE <span class="bet-value"></span>
              </a>
            </div>
          </div>

          <div class="game-player-other" style="height:150px;">
            <div class="player-other player-cards"></div>
            <div class="player-other text-right">
              <i class="fa fa-user-circle-o fa-3x"></i><br/>
              <div class="player-name-other"></div>
              $<span class="player-money">N/A</span>
            </div>
          </div>
          <div style="clear:both"></div>
        </div>
      </div>
    </div>
    <div class="col-md-2 col-md-2 col-sm-12 col-xs-12 game-log-encloser">
      <h4>Game log</h4>
      <div class="game-log"></div>
    </div>
  </div>
<? }else{ ?>
  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
    <h4>
      ERROR: <? if(isset($this->error)){ echo $this->error; }?>
    </h4>
  </div>
<? } ?>
