<?php
  require_once(dirname(__FILE__)."/../__config.php");

  $type = isset($_GET['type'])?$_GET['type']:false;
  $json = new container();
  $code = 404;

  if($type == "create"){
      $name = isset($_POST["name"])?$_POST["name"]:null;
      if($name){
        $name = substr($name, 0, 35); // only allow upto 35 chars
        $game = new game();
        $game->create($name);

        $code = 200;
      }
  }
  else if($type == "load"){
    $json->games =  game::load_games();
    $code = 200;
  }

  $json->code = $code;
  echo $json->json_encode();
