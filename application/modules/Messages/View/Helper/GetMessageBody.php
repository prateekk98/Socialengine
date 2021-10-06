<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: GetMessageBody.php 9799 2012-10-16 22:11:00Z matthew $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Messages
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Messages_View_Helper_GetMessageBody extends Zend_View_Helper_Abstract
{
  public function getMessageBody($message)
  {
    if (empty($message->body)) {
      return '';
    }

    $bbcode = Zend_Markup::factory('Bbcode');
    return $this->view->getHelper('getActionContent')->smileyToEmoticons(
      html_entity_decode(htmlspecialchars_decode($bbcode->render($message->body)))
    );
  }
}
