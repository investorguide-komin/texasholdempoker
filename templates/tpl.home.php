
  <style type="text/css">
    .tabular-encloser{padding:25px 50px 50px;}
    .tabular{border:1px solid #eee;width:100%; text-align:left;}
    .tabular-header{background:#eee; font-weight: bold; color:#777; text-transform: uppercase;}
    .tabular td, .tabular th{border:1px solid #ddd; padding:5px 7px;text-align:center;}
    .tabular td{font-size:16px; padding:10px 15px;}
    h4{text-align:left; color:#444;}
    .create-game{text-align: left; margin-bottom:50px;}
    .create-game input[type="text"]{padding:5px 7px; font-size:14px; border:1px solid #ddd; width:300px;}
  </style>

  <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 text-center tabular-encloser">
    <!-- show the following things
          1) see the win/loss statistics of themselves and others
          2) see game moves of completed games,
          3) start a new game, and
          4) join an existing game that needs players. -->

    <h4>Create New Game</h4>
    <div class="create-game">
      <form id="create-game-form" method="POST">
        <input type="text" name="name" placeholder="Enter Game Name" autofocus maxlength="35" size="35"/>
        <input type="submit" value="Create" class="btn green-btn"/>
    </form>
    </div>

    <h4>Current Games</h4>
    <table class="tabular">
      <tr class="tabular-header text-center">
        <th>Game name</th>
        <th>Created by</th>
        <th>Available spots</th>
        <th>Actions</th>
      </tr>
      <tbody id="tabular-tbody"></tbody>
    </table>
  </div>
