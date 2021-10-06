<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: edit-multiple-networks.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Steve
 */
?>
<script type="text/javascript">
  var privacyData = '<?php echo $this->privacy; ?>';
  var action_id = '<?php echo $this->action_id; ?>';
  var globalActivityId = 'privacy_'+action_id;
  en4.core.runonce.add(function() {
    checkAll();
  })
  function checkAll() {
    var checks = document.getElementsByName("network_list[]");
    var selectedPrivacyData = privacyData.split(',');
    for (var i=0; i < checks.length; i++) {
      if(selectedPrivacyData.includes(checks[i].value)){
        checks[i].checked = true;
      }
    }
  }
  function setEditNetworks() {
    var selectedNetworks = getSelectedNetworks();
    var networksArray = selectedNetworks.split(",");
    if (networksArray.length > 0) {
      if(selectedNetworks !== "")
        window.parent.document.getElementById(`${globalActivityId}`).value = selectedNetworks;
    }
    parent.Smoothbox.close();
  }

  function getSelectedNetworks() {
    var selectedNets = new Array();
    $$('.network_list').each(function(el) {
      if(el.checked){
        selectedNets.push(el.value);
      }
    });
    return selectedNets.join();
  }
</script>
<div class="global_form_popup">
  <form class="global_form">
    <p class="form-description"><b><?php echo $this->translate("Choose Multiple Networks you want to share with:") ?></b>
    </p>
    <br/>
    <div class="form-elements">
      <div id="network_list_wapper" class="form-wrapper">
        <ul class="form-options-wrapper">
          <?php foreach ($this->networkLists as $list): ?>
            <li>
              <input class="network_list" type="checkbox" name="network_list[]" id="network_list-<?php echo "network_" . $list->getIdentity() ?>" value="network_<?php echo $list->getIdentity() ?>">
              <label for="network_list-network_<?php echo $list->getIdentity() ?>">
                <?php echo $this->translate($list->getTitle()) ?>
              </label>
            </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <br/>
      <div class="form-element" id="add-element">
        <button onclick="setEditNetworks();" type="button" id="add" name="add">
          <?php echo $this->translate("Done") ?>
        </button>
        <button onclick="javascript:parent.Smoothbox.close()" type="button" id="cancel" name="cancel">
          <?php echo $this->translate("Cancel") ?>
        </button>
      </div>
    </div>
  </form>
</div>
