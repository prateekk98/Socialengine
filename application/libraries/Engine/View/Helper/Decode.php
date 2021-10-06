<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

/**
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_View_Helper_Decode extends Zend_View_Helper_HtmlElement
{
    public function decode($text)
    {
        $text = Engine_Text_Emoji::decode($text);

        return $text;
    }
}
