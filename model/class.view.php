<?php

  class view{
    function __construct(){
    }

    function render($template){
      require_once("../view/template/tpl.header.php");
      require_once("../view/template/".$template);
      require_once("../view/template/tpl.footer.php");
    }

  }
