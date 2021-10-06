<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: ItemPhoto.php 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_View_Helper_LinkRichContent extends Engine_View_Helper_HtmlImage
{

  protected $_allowTyes = array(
    'player',
    'image',
    'reader',
    'survey',
    'file'
  );

  public function linkRichContent(Core_Model_Link $item)
  {
    if( empty($item->params['iframely']) ) {
      return;
    }
    $iframely = $item->params['iframely'];
    $typeOfContent = array_intersect(array_keys($iframely['links']), $this->_allowTyes);
    if( empty($typeOfContent) || empty($iframely['html']) ) {
      return;
    }
    return $this->view->partial(
        'link/_richContent.tpl', 'core', array('link' => $item)
    );
  }
}
