<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Service_Iframely_Host_Self
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Exception.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Service_Iframely_Host_Self
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Service_Iframely_Host_Self extends Engine_Service_Iframely_Host
{
  /**
   * Constructor
   *
   * @param array $options
   */
  public function __construct($options = array())
  {
    if( empty($options['baseUrl']) ) {
      throw new Engine_Service_Iframely_Exception('Base URL does not exist.');
    }
    parent::__construct($options);
  }

}
