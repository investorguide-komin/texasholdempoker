<?php

  require_once(dirname(__FILE__)."/../__config.php");

  $view = new view();
  $view->render("tpl.index.php");
