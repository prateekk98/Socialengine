<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2017 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Customize.php 10164 2017-02-01 15:35:35Z lucas $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2017 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Form_Admin_Themes_CreateColorVariant extends Engine_Form
{
  protected $_colorVariants;
  protected $_activeTheme;

  public function setColorVariants($colorVariants)
  {
    $this->_colorVariants = $colorVariants;
    return $this;
  }

  public function setActiveTheme($activeTheme)
  {
    $this->_activeTheme = $activeTheme;
    return $this;
  }

  public function init()
  {
    $description = sprintf(
      'Below you can choose any available color variant of any available themes.'
      . ' Doing this will create a new theme in the "application/themes/" directory of your site.'
      . '<br> Note : Selecting a theme which is already created will overwrite all the changes, if done by you.'
      );
    $this
      ->setTitle('Color Variants')
      ->setDescription($description)
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
    ;
    // Decorators
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);

    $themes = Engine_Api::_()->getDbtable('themes', 'core')->fetchAll();
    $masterThemes = array();
    $themeList = array();
    $basePath = APPLICATION_PATH . '/application/themes/';
    foreach( $themes as $theme ) {
      $manifest = include("$basePath/$theme->name/manifest.php");
      if( !empty($manifest['colorVariants']) ) {
        $masterThemes[$theme->name] = $theme->title;
      }
      $themeList[$theme->name] = $theme->title;
    }

    $this->addElement('Select', 'themeName', array(
      'label' => 'Choose theme',
      'multiOptions' => $masterThemes,
      'onchange' => 'javascript:fetchColorVariant(this.value);',
    ));

    $this->addElement('text', 'variants', array(
      'label' => 'Choose Color Variant',
      'ignore' => true,
      'decorators' => array(array('ViewScript', array(
            'viewScript' => '_formThemeVariants.tpl',
            'colorVariants' => $this->_colorVariants,
            'activeTheme' => $this->_activeTheme,
            'themeList' => $themeList,
            'class' => 'form element'
          )))
    ));
  }

}
