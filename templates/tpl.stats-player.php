
  <style type="text/css">
    .tabular-encloser{padding:25px 50px 50px;}
    .tabular{border:1px solid #eee;width:100%; text-align:left;}
    .tabular-header{background:#eee; font-weight: bold; color:#777; text-transform: uppercase;}
    .tabular td, .tabular th{border:1px solid #ddd; padding:5px 7px;text-align:center;}
    .tabular td{font-size:16px; padding:10px 15px;}
  </style>


  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
    <? if(isset($this->users) && count($this->users)){ ?>
        <div class="tabular-encloser">
          <table class="tabular">
            <tr class="tabular-header text-center">
              <th>Username</th>
              <th>Wins</th>
              <th>Draws</th>
              <th>Losses</th>
            </tr>
            <? foreach($this->users as $user){ ?>
              <tr>
                <td><? echo $user->username;?></td>
                <td><? echo $user->get_wins();?></td>
                <td><? echo $user->get_draws();?></td>
                <td><? echo $user->get_losses();?></td>
              </tr>
            <? } ?>
          </table>
        </div>
    <? }
    else{ ?>
      <h4>
        ERROR: No user stats available
      </h4>
    <? } ?>
  </div>
