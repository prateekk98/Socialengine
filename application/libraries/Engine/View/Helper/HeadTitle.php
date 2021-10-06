<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View_Helper_HeadTitle
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: DateTime.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_View_Helper_HeadTitle
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_View_Helper_HeadTitle extends Zend_View_Helper_HeadTitle
{
    /**
     * @overwrite
     */
    protected function _escape($string)
    {
        $enc = 'UTF-8';
        if ($this->view instanceof Zend_View_Interface
            && method_exists($this->view, 'getEncoding')
        ) {
            $enc = $this->view->getEncoding();
        }
        // Does not encode any quotes form title
        return htmlspecialchars((string) $string, ENT_NOQUOTES, $enc);
    }
}
