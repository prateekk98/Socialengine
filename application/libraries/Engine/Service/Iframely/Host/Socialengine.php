<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Service_Iframely_Host_Socialengine
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Exception.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Service_Iframely_Host_Socialengine
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Service_Iframely_Host_Socialengine extends Engine_Service_Iframely_Host
{
  protected $_baseUrl = 'https://services.socialengine.com/embed';

  protected $_testUrl = 'https://services.socialengine.com/embed';

  /**
   * Constructor
   *
   * @param array $options
   */
  public function __construct($options = array())
  {
      parent::__construct($options);
      $request = new Zend_Controller_Request_Http();
      if (!defined('_ENGINE_ADMIN_NEUTER'))
        $this->getHttpClient()->setHeaders('host', $request->getHttpHost());
  }
}
