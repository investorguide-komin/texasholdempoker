<?php

  class view extends container{
    function __construct($args = false){
      parent::__construct($args);

      $this->javascript = array();
    }

    function javascript($loading_method = '') {
			foreach($this->javascript as $js) {
				echo('<script type="text/javascript" src="'.$js.'"></script>');
			}
		}

    function render($template){
      require_once(dirname(__FILE__)."/../templates/tpl.header.php");
      require_once(dirname(__FILE__)."/../templates/".$template);
      require_once(dirname(__FILE__)."/../templates/tpl.footer.php");
    }

    function redirect($url, $permanent = false){
      header('Location: ' . $url, true, $permanent ? 301 : 302);
      database::disconnect();
      exit();
    }

  }
