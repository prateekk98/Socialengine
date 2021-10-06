<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Iframely
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Image.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Iframely
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Iframely
{

  const IFRAMELY_HOST = 'iframely';
  const SOCIALENGINE_HOST = 'socialengine';
  const OWN_HOST = 'self';
  const DEFAULT_HOST = 'socialengine';

  static public function factory($options = array())
  {
    $host = self::DEFAULT_HOST;
    if( !empty($options['host']) ) {
      $host = $options['host'];
      unset($options['host']);
    }

    $class = 'Engine_Service_Iframely_Host_' . ucfirst($host);
    Engine_Loader::loadClass($class);
    if( !class_exists($class, false) ) {
      throw new Engine_Image_Exception(sprintf('Missing class for host "%s"', $host));
    }
    return new $class($options);
  }

  static public function getHostingList()
  {
    return array(
      self::IFRAMELY_HOST,
      self::OWN_HOST,
      self::SOCIALENGINE_HOST,
    );
  }
}
