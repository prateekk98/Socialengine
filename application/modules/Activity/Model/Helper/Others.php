<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Item.php 9747 2016-12-06 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Activity_Model_Helper_Others extends Activity_Model_Helper_Abstract
{

  /**
   * Generates text representing an similar items
   * 
   * @param array $items The items
   * @return string
   */
  public function direct($items = array())
  {
    if( empty($items) ) {
      return false;
    }
    $count = count($items);
    if( $count === 1 ) {
      $attribs = array('class' => 'feed_item_username');
      return array_pop($items)->toString($attribs);
    }

    if( Zend_Registry::isRegistered('Zend_View') ) {
      $view = Zend_Registry::get('Zend_View');
      $count = $view->locale()->toNumber($count);
    }

    $translate = Zend_Registry::get('Zend_Translate');
    $othersKey = '%s others';
    if( $translate instanceof Zend_Translate ) {
      $othersKey = $translate->translate($othersKey);
    }
    $text = vsprintf($othersKey, $count);
    $link = '<a '
      . 'class="feed_item_username" '
      . 'href="javascript:void()"'
      . '>'
      . $text
      . '</a>';

    return '<span class="tip_container">'
      . $link
      . $this->getListHtml($items)
      . '</span>';
  }

  protected function getListHtml($items)
  {
    $itemList = '<span class="tip_wapper">'
      . '<ul class="tip_body">';
    foreach( $items as $item ) {
      $itemList .= '<li>'
        . $item->toString()
        . '</li>';
    }
    return $itemList . '</ul></span>';
  }
}
