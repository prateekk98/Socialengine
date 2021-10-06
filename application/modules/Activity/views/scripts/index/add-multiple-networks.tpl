<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: add-multiple-networks.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Steve
 */
?>
<script type="text/javascript">
  function setNetworks() {
    var oldNetworks = window.parent.document.getElementById('auth_view').value;
    var tempNetworks = oldNetworks.split(",");
    for (var i = 0; i < tempNetworks.length; i++) {
      var tempListElement = window.parent.document.getElementById('privacy_list_' + tempNetworks[i] );
      tempListElement.removeClass('activity_tab_active').addClass('activity_tab_unactive');
    }

    var selectedNetworks = getSelectedNetworks();
    var networksArray = selectedNetworks.split(",");
    var label = '';
    for (var i = 0; i < networksArray.length; i++) {
      if (label == '') {
        label = $('network_list-' + networksArray[i]).getParent('li').getElement('label').innerHTML;
      } else {
        label = label + ", " + $('network_list-' + networksArray[i]).getParent('li').getElement('label').innerHTML;
      }

      tempListElement = window.parent.document.getElementById('privacy_list_'+ networksArray[i]);
      tempListElement.addClass('activity_tab_active').removeClass('activity_tab_unactive');
    }

    if (label != '') {
      label = en4.core.language.translate('Share with Multiple Networks');
    }
    window.parent.document.getElementById("privacy_lable_tip").innerHTML = label;
    window.parent.document.getElementById('auth_view').value = networksArray;
    window.parent.document.getElementById('privacy_pulldown_button').innerHTML = '<i class="privacy_pulldown_icon activity_icon_feed_network"></i><span>'
      + en4.core.language.translate('Share with Multiple Networks') +
      ' </span><i class="fa fa-caret-down"></i>';
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
        <button onclick="setNetworks();" type="button" id="add" name="add">
          <?php echo $this->translate("Done") ?>
        </button>
        <button onclick="javascript:parent.Smoothbox.close()" type="button" id="cancel" name="cancel">
          <?php echo $this->translate("Cancel") ?>
        </button>
      </div>
    </div>
  </form>
</div>
