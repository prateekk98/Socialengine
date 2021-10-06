<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Announcement
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: edit.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */
?>
<?php
  $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl."externals/selectize/css/normalize.css");
$headScript = new Zend_View_Helper_HeadScript();
$headScript->prependFile($this->layout()->staticBaseUrl.'externals/jQuery/jquery.min.js');
$headScript->appendFile($this->layout()->staticBaseUrl.'externals/selectize/js/selectize.js');
?>
<div class="settings">
  <?php echo $this->form->render($this) ?>
</div>
<style>
  .selectize-input{
    min-width: 200px;
  }
  .selectize-control.multi .selectize-input [data-value]{
    background-color: blue;
    color: white;
  }
</style>
<script type="application/javascript">
    scriptJquery('select[multiple=multiple]').closest('div').css('overflow',"visible");
    scriptJquery('select[multiple=multiple]').selectize({});
</script>
