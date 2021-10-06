<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Classified.php 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Classified
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Model_Banner extends Core_Model_Item_Abstract
{

  // Properties
  protected $_parent_type = null;
  protected $_searchTriggers = array();
  protected $_parent_is_owner = false;

  /**
   * Disable internal hooks?
   * @var boolean
   */
  protected $_disableHooks = true;

  // General
  /**
   * Gets an absolute URL to the page to view this item
   *
   * @return string
   */
  public function getCTAHref()
  {

    $params = $this->params;
    if( !empty($params['uri']) ) {
      return $params['uri'];
    }
    if( empty($params['route']) ) {
      return;
    }
    $route = $params['route'];
    unset($params['route']);
    $routeParams = array();
    if( isset($params['routeParams']) ) {
      $routeParams = $params['routeParams'];
    }
    return Zend_Controller_Front::getInstance()->getRouter()
        ->assemble($routeParams, $route, true);
  }

  public function getDescription()
  {
    // @todo decide how we want to handle multibyte string functions
    $tmpBody = strip_tags($this->body);
    return ( Engine_String::strlen($tmpBody) > 255 ? Engine_String::substr($tmpBody, 0, 255) . '...' : $tmpBody );
  }

  public function getCTALabel()
  {
    return isset($this->params['label']) ? $this->params['label'] : '';
  }

  public function setPhoto($photo)
  {
    if( $photo instanceof Zend_Form_Element_File ) {
      $file = $photo->getFileName();
      $fileName = $file;
    } elseif( is_array($photo) && !empty($photo['tmp_name']) ) {
      $file = $photo['tmp_name'];
      $fileName = $photo['name'];
    } elseif( is_string($photo) && file_exists($photo) ) {
      $file = $photo;
      $fileName = $photo;
    } else {
      throw new Core_Model_Exception('invalid argument passed to setPhoto');
    }

    if( !$fileName ) {
      $fileName = basename($file);
    }

    $extension = ltrim(strrchr(basename($fileName), '.'), '.');
    $base = rtrim(substr(basename($fileName), 0, strrpos(basename($fileName), '.')), '.');
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';

    $params = array(
      'parent_type' => 'banner',
      'parent_id' => $this->getIdentity(),
      'name' => $fileName,
    );

    // Save
    $filesTable = Engine_Api::_()->getDbtable('files', 'storage');

    // Resize image (main)
    $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_m.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
      ->write($mainPath)
      ->destroy();
    // Store
    $iMain = $filesTable->createFile($mainPath, $params);

    // Remove temp files
    @unlink($mainPath);
    $this->photo_id = $iMain->file_id;
    $this->save();
    return $this;
  }

}
