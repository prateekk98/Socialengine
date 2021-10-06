<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     Jung
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Form_Admin_Widget_Logo extends Core_Form_Admin_Widget_Standard
{
  public function init()
  {
    parent::init();
    
    // Set form attributes
    $this
      ->setTitle('Site Logo')
      ->setDescription('Shows your site-wide main logo or title.  IIImages are uploaded via the File Media Manager.')
      ;

    // Get available files
    $logoOptions = array('' => 'Text-only (No logo)');

    $files = Engine_Api::_()->getDbTable('files', 'core')->getFiles(array('fetchAll' => 1, 'extension' => array('gif', 'jpg', 'jpeg', 'png')));
    foreach( $files as $file ) {
      $logoOptions[$file->storage_path] = $file->name;
    }

    $this->addElement('Select', 'logo', array(
      'label' => 'Site Logo',
      'multiOptions' => $logoOptions,
    ));
  }
}
