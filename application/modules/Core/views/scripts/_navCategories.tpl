<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: _navCategories.tpl 9747 2016-12-14 02:08:08Z john $
 * @author     John
 */
?>

<?php
// Render the categories
$module = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
echo $this->navigation()
  ->menu()
  ->setContainer($this->categories)
  ->setUlClass($this->type . '_categories' . ' category_options generic_list_widget')
  ->render();
