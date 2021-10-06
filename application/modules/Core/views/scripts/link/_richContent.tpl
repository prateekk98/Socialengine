<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: delete.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */
?>
<div class="item_core_link">
  <div class="item_link_rich_html">
    <?php echo $this->link->params['iframely']['html']; ?>
  </div>
  <div class="item_core_link_info">
    <div class="item_link_title">
      <?php
      echo $this->htmlLink($this->link->getHref(), $this->link->getTitle() ? $this->link->getTitle() : '', array('target' => '_blank'));
      ?>
    </div>
    <div class="item_link_desc">
      <?php echo $this->viewMore($this->link->getDescription()) ?>
    </div>
  </div>
</div>

<?php if( Zend_Controller_Front::getInstance()->getRequest()->isXmlHttpRequest() ): ?>
  <script type="text/javascript">
    en4.core.runonce.add(function() {
      if( iframely ) {
        iframely.load();
      }
    });
  </script>
<?php endif;?>
