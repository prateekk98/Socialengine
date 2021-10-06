<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Edit.php 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Form_Admin_Banners_Edit extends Core_Form_Admin_Banners_Create
{
  public function init()
  {
    parent::init();
    $this->setTitle('Edit Banner');
    $this->setDescription('Follow this guide to design banner.');

    $this->submit->setLabel('Save Changes');
  }
}
