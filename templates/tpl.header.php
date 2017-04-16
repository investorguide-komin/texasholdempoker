<!DOCTYPE HTML>
<html>
  <head>
    <title>Texas Hold'em Poker Online</title>
    <link href="https://fonts.googleapis.com/css?family=Baloo+Bhaina" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">

    <style type="text/css">
      .header{border-bottom:1px solid #777777; padding:15px 10px 5px; margin:0;}
      .footer{padding:15px; border-top:1px solid #dddddd; background:#eeeeee; text-align:center;}
      .title{font-family: 'Baloo Bhaina', cursive; font-size:24px;}
      .colorful{color:#ff5252 !important;}
      .message{color:#a72005; font-weight:bold;}
      .header-links a{border-right:1px solid #eee; padding:7px 11px;}
      .user-detail{font-weight:bold; margin-right:15px; color:#777;}
      .btn{padding:5px 11px; font-weight:normal; text-decoration: none !important; color:white !important;}
      .green-btn{background:#52981b;}
      .green-btn:hover{background:#3b6d13;}
      .red-btn{background:#d94324;}
      .red-btn:hover{background:#ad351d;}
    </style>
  </head>
  <body>
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 header">
      <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
        <div class="title">
          <span class="colorful text-bold">Texas Hold'em</span> Poker Online
        </div>
      </div>
      <div class="col-lg-8 col-md-8 col-sm-12 col-xs-12">
        <div class="header-links text-right">
          <? if(isset($_SESSION['username']) && ($_SESSION['username'])){?>
          <span class="user-detail">Logged in as <? echo htmlentities($_SESSION['username']);?></span>
          <? } ?>
          <a href="home.php">Join Game</a>
          <a href="javascript:;">See Stats</a>
          <a href="logout.php">Log Out</a>
        </div>
      </div>
      <div style="clear:both"></div>
    </div>

    <div class="container">
