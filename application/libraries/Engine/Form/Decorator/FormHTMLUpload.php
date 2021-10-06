<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Form
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: FormHTMLUpload.php 9881 2013-02-13 20:07:49Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Form
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Form_Decorator_FormHTMLUpload extends Zend_Form_Decorator_Abstract
{
  public function render($content)
  {
    $data = $this->getElement()->getAttrib('data');
    if ($data) {
        $this->getElement()->setAttrib('data', null);
    }
    $element = $this->getElement();
    $view = $this->getElement()->getView();

    $view->headScript()->appendFile(
      $view->layout()->staticBaseUrl . 'externals/uploader/uploader.js'
    );

    $view->headLink()->appendStylesheet(
      $view->layout()->staticBaseUrl . 'externals/uploader/uploader.css'
    );

    $context = 'name="' . $element->getName() . '"';
    $context .= ' data-url="' . $element->url . '"';
    if (!empty($element->{'form'})) {
      $context .= ' data-form-id="' . $element->{'form'} . '"';
    }
    if (!empty($element->{'multi'})) {
      $context .= ' multiple="multiple"';
    }
    if (!empty($element->{'accept'})) {
      $context .= ' accept="' . $element->{'accept'} . '"';
    }

    return $view->partial('upload/upload.tpl', 'core', [
      'name' => $element->getName(),
      'data' => $data,
      'element' => $element,
      'context' => $context
    ]);
  }
}
