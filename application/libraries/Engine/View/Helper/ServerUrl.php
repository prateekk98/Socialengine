<?php

class Engine_View_Helper_ServerUrl extends Zend_View_Helper_ServerUrl
{
  /**
   * override Constructor
   *
   * @return void
   */
  public function __construct()
  {
    parent::__construct();
    switch( true ) {
      case (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] === true)):
      case (isset($_SERVER['HTTP_SCHEME']) && ($_SERVER['HTTP_SCHEME'] == 'https')):
      case (isset($_SERVER['SERVER_PORT']) && ($_SERVER['SERVER_PORT'] == 443)):
      case (isset($_SERVER['HTTP_X_FORWARDED_PORT']) && ($_SERVER['HTTP_X_FORWARDED_PORT'] == 443)):
      case (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https'):
      case (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on'):
        $scheme = 'https';
        break;
      default:
        $scheme = 'http';
    }
    $this->setScheme($scheme);
  }

}
