<?php

  class view{
    function __construct(){
    }

    function render($template){
      require_once(dirname(__FILE__)."/../view/templates/tpl.header.php");
      require_once(dirname(__FILE__)."/../view/templates/".$template);
      require_once(dirname(__FILE__)."/../view/templates/tpl.footer.php");
    }

    function redirect($url, $permanent = false){
      header('Location: ' . $url, true, $permanent ? 301 : 302);
      database::disconnect();
      exit();
    }

  }
