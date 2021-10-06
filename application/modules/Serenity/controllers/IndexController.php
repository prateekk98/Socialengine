<?php

class Serenity_IndexController extends Core_Controller_Action_Standard
{
  public function indexAction()
  {
    $this->view->someVar = 'someVal';
  }
  function fontAction(){
      if(!count($_POST)){
          echo false;die;
      }
      $font = $this->_getParam('size','');
      $_SESSION['font_theme'] = $font;
      echo true;die;
  }
    function modeAction(){
        if(!count($_POST)){
            echo false;die;
        }
        $font = $this->_getParam('mode','');
        $_SESSION['mode_theme'] = $font;
        echo true;die;
    }
}
