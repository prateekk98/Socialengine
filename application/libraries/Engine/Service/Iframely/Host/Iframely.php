<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Service_Iframely_Host_Iframely
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Exception.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Service_Iframely_Host_Iframely
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Service_Iframely_Host_Iframely extends Engine_Service_Iframely_Host
{

  protected $_baseUrl = 'http://iframe.ly/api';

  /**
   * Constructor
   *
   * @param array $options
   */
  public function __construct($options = array())
  {
    if( empty($options['secretIframelyKey']) ) {
      throw new Engine_Service_Iframely_Exception('Api key does not exist.');
    }

    $options['secretKey'] = $options['secretIframelyKey'];
    parent::__construct($options);
  }

  protected function _getRequestParams()
  {
    $params = parent::_getRequestParams();
    $params['key'] = md5($this->_secretKey);
    return $params;
  }

}
