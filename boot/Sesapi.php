<?php
class Engine_Boot_Sesapi extends Engine_Boot_Abstract{
  public function beforeBoot()
  {
    if( !empty($_GET['restApi']) ) {
      $this->_boot->setRootBootDir('apps');
      $this->_boot->setRootBootFileName('sesapi.php');
    }
  }
}