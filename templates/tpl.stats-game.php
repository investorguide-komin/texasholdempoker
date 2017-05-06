
  <style type="text/css">
    .tabular-encloser{padding:25px 50px 50px;}
    .tabular{border:1px solid #eee;width:100%; text-align:left;}
    .tabular-header{background:#eee; font-weight: bold; color:#777; text-transform: uppercase;}
    .tabular td, .tabular th{border:1px solid #ddd; padding:5px 7px;text-align:center;}
    .tabular td{font-size:16px; padding:10px 15px;}
    .game-log{padding:25px 0; font-size:20px;}
  </style>


  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
    <? if(isset($this->games) && count($this->games)){ ?>
        <div class="tabular-encloser">
          <table class="tabular">
            <tr class="tabular-header text-center">
              <th>Game</th>
              <th>GameLog Link</th>
            </tr>
            <? foreach($this->games as $game){ ?>
              <tr>
                <td><? echo $game->name;?></td>
                <td>
                  <a href="stats-game.php?id=<? echo $game->id;?>" class="btn red-btn">
                    Game Log
                  </a>
                </td>
              </tr>
            <? } ?>
          </table>
        </div>
    <? }
    else if(isset($this->game) && ($this->game)){ ?>
      <div class="game-log">
        <? echo $this->game->get_game_log_asc(); ?>
      </div>
    <? }
    else{ ?>
      <h4>
        ERROR: No game stats available for this selection
      </h4>
    <? } ?>
  </div>
