<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Widget_MenuFooterController extends Engine_Content_Widget_Abstract
{
    public function indexAction()
    {
        $languagePath = APPLICATION_PATH . '/application/languages';
        $this->view->navigation = $navigation = Engine_Api::_()
            ->getApi('menus', 'core')
            ->getNavigation('core_footer');

        // Languages
        $translate    = Zend_Registry::get('Zend_Translate');
        $languageList = $translate->getList();

        //$currentLocale = Zend_Registry::get('Locale')->__toString();

        // Prepare default langauge
        $defaultLanguage = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.locale', 'en');
        if ($defaultLanguage == 'auto') {
            $defaultLanguage = 'en';
        }

        // Init default locale
        $localeObject = Zend_Registry::get('Locale');
        $languages = Zend_Locale::getTranslationList('language', $localeObject);
        $territories = Zend_Locale::getTranslationList('territory', $localeObject);

        $localeMultiOptions = array();
        foreach ($languageList as $key) {
            $dir = $languagePath . '/' . $key;
            if (!is_dir($dir)) {
                continue;
            }

            $languageName = null;
            if (!empty($languages[$key])) {
                $languageName = $languages[$key];
            } else {
                $tmpLocale = new Zend_Locale($key);
                $region = $tmpLocale->getRegion();
                $language = $tmpLocale->getLanguage();
                if (!empty($languages[$language]) && !empty($territories[$region])) {
                    $languageName =  $languages[$language] . ' (' . $territories[$region] . ')';
                }
            }

            if ($languageName) {
                $localeMultiOptions[$key] = $languageName . '';
            }
        }

        if (!isset($localeMultiOptions[$this->view->defaultLanguage])) {
            $defaultLanguage = 'en';
        }

        $this->view->defaultLanguage = $defaultLanguage;
        $this->view->languageNameList = $localeMultiOptions;

        // Get affiliate code
        $this->view->affiliateCode = Engine_Api::_()->getDbtable('settings', 'core')->core_affiliate_code;
        
        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->viewer_id = $viewer->getIdentity();
    }

    public function getCacheKey()
    {
        //return true;
    }

    public function setLanguage()
    {
    }
}
