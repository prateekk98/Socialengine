<?php

class Serenity_Plugin_Core extends Zend_Controller_Plugin_Abstract {

	public function onRenderLayoutDefault(){

    $request = Zend_Controller_Front::getInstance()->getRequest();
    $module = $request->getModuleName();
    $controller = $request->getControllerName();
    $action = $request->getActionName();
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $changelanding = $settings->getSetting('serenity.changelanding', 0);
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    if(!empty($changelanding) && $module == 'core' && $controller == 'index' && $action == 'index') {
      $script = '
        en4.core.runonce.add(function() {
          $(document).getElement("body").addClass("serenity_landingpage");
        });';
      $view->headScript()->appendScript($script);
		}
		
    //Google Font Work
    $usegoogleFont = $settings->getSetting('serenity.googlefonts', 1);
    if(!empty($usegoogleFont)) {
      $string = 'https://fonts.googleapis.com/css?family=';

      $bodyFontFamily = Engine_Api::_()->serenity()->getContantValueXML('serenity_body_fontfamily');
      $string .= str_replace('"', '', $bodyFontFamily);

      $headingFontFamily = Engine_Api::_()->serenity()->getContantValueXML('serenity_heading_fontfamily');
      $string .= '|'.str_replace('"', '', $headingFontFamily);
      
      $mainmenuFontFamily = Engine_Api::_()->serenity()->getContantValueXML('serenity_mainmenu_fontfamily');
      $string .= '|'.str_replace('"', '', $mainmenuFontFamily);

      $tabFontFamily = Engine_Api::_()->serenity()->getContantValueXML('serenity_tab_fontfamily');
      $string .= '|'.str_replace('"', '', $tabFontFamily);;

      $view->headLink()->appendStylesheet($string);

    }
	}
}
