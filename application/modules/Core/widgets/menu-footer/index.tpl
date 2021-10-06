<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<div class="footer_left_links">
  <span class="footer_copyright"><?php echo $this->translate('Copyright &copy;%s', date('Y')) ?></span>
  <?php foreach( $this->navigation as $item ):
    $attribs = array_diff_key(array_filter($item->toArray()), array_flip(array(
      'reset_params', 'route', 'module', 'controller', 'action', 'type',
      'visible', 'label', 'href'
    )));
    ?>
    <?php echo $this->htmlLink($item->getHref(), $this->translate($item->getLabel()), $attribs) ?>
  <?php endforeach; ?>
</div>
<?php if( 1 !== count($this->languageNameList) ): ?>
    <form method="post" action="<?php echo $this->url(array('controller' => 'utility', 'action' => 'locale'), 'default', true) ?>" style="display:inline-block" id="footer_language_<?php echo $this->identity; ?>">
      <?php $selectedLanguage = $this->translate()->getLocale() ?>
      <?php echo $this->formSelect('language', $selectedLanguage, array('onchange' => "setLanguage()"), $this->languageNameList) ?>
      <?php echo $this->formHidden('return', $this->url()) ?>
    </form>
<?php endif; ?>
<script>
  function setLanguage() {
    scriptJquery('#footer_language_<?php echo $this->identity; ?>').submit();
  }
</script>
<?php if( !empty($this->affiliateCode) ): ?>
  <div class="affiliate_banner">
    <?php 
      echo $this->translate('Powered by %1$s', 
        $this->htmlLink('http://www.socialengine.com/?source=v4&aff=' . urlencode($this->affiliateCode), 
        $this->translate('SocialEngine Community Software'),
        array('target' => '_blank')))
    ?>
  </div>
<?php endif; ?>

<?php if(!empty($this->viewer_id)) { ?>
<?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('core.sell.info')): ?>
  <div class="footer_donotsell">
    <input type="checkbox" id="donosellinfo" onclick="donotSellInfo()" <?php if($this->viewer->donotsellinfo == 1) { ?> checked <?php } ?>> <?php echo $this->translate("Do Not Sell My Personal Information."); ?>
  </div>
<?php endif; ?>
  <script>
    function donotSellInfo() {
      var checkBox = document.getElementById("donosellinfo");
      (new Request.JSON({
        method: 'post',
        'url': en4.core.baseUrl + 'core/index/donotsellinfo/',
        'data': {
          format: 'json',
          donotsellinfo: checkBox.checked,
        },
        onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
        }
      })).send();
      return false;
    }
  </script>
<?php } ?>
