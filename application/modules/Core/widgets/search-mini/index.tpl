<?php
/**
 * SocialEngine - Search Widget Smarty Template
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2012 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Matthew
 */
?>

<div id='global_search_form_container'>
  <form id="global_search_form" action="<?php echo $this->url(array('controller' => 'search'), 'default', true) ?>" method="get">
    <input autocomplete="off" type='text' class='text suggested' name='query' id='global_search_field' size='20' maxlength='100' alt='<?php echo $this->translate('Search') ?>'  placeholder='<?php echo $this->translate('Search') ?>'/>
  </form>
</div>
