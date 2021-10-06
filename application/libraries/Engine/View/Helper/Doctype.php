<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Content.php 9747 2017-12-02 02:08:08Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright  2017-12-02 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Engine_View_Helper_Doctype extends Zend_View_Helper_Doctype
{
  public function isRdfa()
  {
    return (parent::isHtml5() || parent::isRdfa());
  }
}
