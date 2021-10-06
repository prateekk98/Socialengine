<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>

<?php
$title = Engine_Api::_()->getApi('settings', 'core')->getSetting('core_general_site_title', $this->translate('_SITE_TITLE'));
$title = $this->translate($title);
$logo  = $this->logo;
$route = $this->viewer()->getIdentity()
             ? array('route'=>'user_general', 'action'=>'home')
             : array('route'=>'default');
if($this->disableLink){
    $route = "javascript:;";
}
echo ($logo)
     ? $this->htmlLink($route, $this->htmlImage(Engine_Api::_()->core()->getFileUrl($logo), array('alt'=>$title)))
     : $this->htmlLink($route, $title);

