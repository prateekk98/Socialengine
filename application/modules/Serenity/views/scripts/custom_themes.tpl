<?php ?>
<div class="serenity_styling_buttons">
  <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'serenity', 'controller' => 'settings', 'action' => 'add'), $this->translate("Add New Custom Theme"), array('class' => 'smoothbox serenity_button add_new_theme fa fa-plus', 'id' => 'custom_themes')); ?>
  <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'serenity', 'controller' => 'settings', 'action' => 'add', 'customtheme_id' => $this->customtheme_id), $this->translate("Edit Custom Theme Name"), array('class' => 'smoothbox serenity_button fa fa-pencil', 'id' => 'edit_custom_themes')); ?>
  <?php echo $this->htmlLink(array('route' => 'admin_default', 'module' => 'serenity', 'controller' => 'settings', 'action' => 'delete', 'customtheme_id' => $this->customtheme_id), $this->translate("Delete Custom Theme"), array('class' => 'smoothbox serenity_button fa fa-close', 'id' => 'delete_custom_themes')); ?>
  <a href="javascript:void(0);" class="serenity_button fa fa-close disabled" id="deletedisabled_custom_themes" style="display: none;"><?php echo $this->translate("Delete Custom Theme"); ?></a>
</div>
