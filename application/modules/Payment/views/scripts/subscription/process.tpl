<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: process.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>
<?php if(method_exists($this->gateway->getPlugin(),'getGatewayUserForm')): ?>
  <?php 
    $form = $this->gateway->getPlugin()->getGatewayUserForm();
    $form->setAction($this->returnUrl);
    echo $form->render();
  ?>
  <?php if($form->getSettings()['receipt']): ?>
  <script type="text/javascript">
    window.addEvent('load', function(){
      $('file').setAttribute('required',true);
    });
  </script>
  <?php endif; ?>
<?php else: ?>
  <script type="text/javascript">
    window.addEvent('load', function(){
      var url = '<?php echo $this->transactionUrl ?>';
      var data = <?php echo Zend_Json::encode($this->transactionData) ?>;
      var request = new Request.Post({
        url : url,
        data : data
      });
      request.send();
    });
  </script>
<?php endif; ?>
