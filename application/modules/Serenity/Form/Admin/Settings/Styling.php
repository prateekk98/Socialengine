<?php

class Serenity_Form_Admin_Settings_Styling extends Engine_Form {

  public function init() {

    $description = "Here, you can manage the color schemes of your website. <br /><div class='tip'><span>Once you switch color schemes or make any changes to the new color schemes you added, please change the mode of your website from Production to Development. This has to be done everytime, and you can switch to production instantly or as soon you are done configuring the color scheme of your website.</span></div>";
    
    // Decorators
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);
    $this->setTitle('Manage Color Schemes')
        ->setDescription($description);

    $settings = Engine_Api::_()->getApi('settings', 'core');
    $api = Engine_Api::_()->serenity();
    $contrast_mode = $api->getContantValueXML('contrast_mode') ? $api->getContantValueXML('contrast_mode') : 'dark_mode';
    $this->addElement('Radio', 'contrast_mode', array(
      'label' => 'Contrast Mode?',
      'description' => 'Choose the Contrast mode for the accessibility widget on your website. You can choose Dark Mode or Light Mode as per the default theme on your website.',
      'multiOptions' => array(
        'light_mode' => 'Light Mode',
        'dark_mode' => 'Dark Mode'
      ),
      'value'=>$contrast_mode
    ));

    $customThemes = Engine_Api::_()->getDbTable('customthemes', 'serenity')->getCustomThemes(array('all' => 1));
    foreach($customThemes as $customTheme) {
      if(in_array($customTheme['theme_id'], array(1,2,3))) {
        $themeOptions[$customTheme['theme_id']] = '<img src="./application/modules/Serenity/externals/images/color-scheme/'.$customTheme['theme_id'].'.png" alt="" />';
      } else {
        $themeOptions[$customTheme['theme_id']] = '<img src="./application/modules/Serenity/externals/images/color-scheme/custom.png" alt="" /> <span class="custom_theme_name">'. $customTheme->name.'</span>';
      }
    }

    $this->addElement('Radio', 'theme_color', array(
      'label' => 'Color Schemes',
      'multiOptions' => $themeOptions,
      'onclick' => 'changeThemeColor(this.value, "")',
      'escape' => false,
      'value' => $api->getContantValueXML('theme_color'),
    ));

    $this->addElement('dummy', 'custom_themes', array(
      'decorators' => array(array('ViewScript', array(
        'viewScript' => 'application/modules/Serenity/views/scripts/custom_themes.tpl',
        'class' => 'form element',
      )))
    ));

    $theme_color = $api->getContantValueXML('theme_color');
    if($theme_color == '5') {
      $serenity_header_background_color = $settings->getSetting('serenity.header.background.color');
      $serenity_mainmenu_background_color = $settings->getSetting('serenity.mainmenu.background.color');
      $serenity_mainmenu_links_color = $settings->getSetting('serenity.mainmenu.link.color');
      $serenity_mainmenu_links_hover_color = $settings->getSetting('serenity.mainmenu.link.hover.color');
			$serenity_mainmenu_links_hover_background_color = $settings->getSetting('serenity.mainmenu.links.hover.background.color');
      $serenity_minimenu_links_color = $settings->getSetting('serenity.minimenu.link.color');
      $serenity_minimenu_link_active_color = $settings->getSetting('serenity.minimenu.link.active.color');
      $serenity_footer_background_color = $settings->getSetting('serenity.footer.background.color');
      $serenity_footer_font_color = $settings->getSetting('serenity.footer.font.color');
      $serenity_footer_links_color = $settings->getSetting('serenity.footer.links.color');
			$serenity_footer_copyright_color = $settings->getSetting('serenity.footer.copyright.color');
      $serenity_footer_border_color = $settings->getSetting('serenity.footer.border.color');
      $serenity_theme_color = $settings->getSetting('serenity.theme.color');
      $serenity_body_background_color = $settings->getSetting('serenity.body.background.color');
			$serenity_menu_tip_color = $settings->getSetting('serenity.menu.tip.color');
      $serenity_font_color = $settings->getSetting('serenity.font.color');
      $serenity_font_color_light = $settings->getSetting('serenity.font.color.light');
      $serenity_links_color = $settings->getSetting('serenity.links.color');
      $serenity_links_hover_color = $settings->getSetting('serenity.links.hover.color');
      $serenity_headline_color = $settings->getSetting('serenity.headline.color');
      $serenity_border_color = $settings->getSetting('serenity.border.color');
      $serenity_box_background_color = $settings->getSetting('serenity.box.background.color');
      $serenity_form_label_color = $settings->getSetting('serenity.form.label.color');
      $serenity_input_background_color = $settings->getSetting('serenity.input.background.color');
      $serenity_input_font_color = $settings->getSetting('serenity.input.font.color');
      $serenity_input_border_color = $settings->getSetting('serenity.input.border.colors');
      $serenity_button_background_color = $settings->getSetting('serenity.button.background.color');
      $serenity_button_background_color_hover = $settings->getSetting('serenity.button.background.color.hover');
      $serenity_button_font_color = $settings->getSetting('serenity.button.font.color');
      $serenity_button_border_color = $settings->getSetting('serenity.button.border.color');
      $serenity_comments_background_color = $settings->getSetting('serenity.comments.background.color');
    } else {

      $serenity_header_background_color = $api->getContantValueXML('serenity_header_background_color');
      $serenity_mainmenu_background_color = $api->getContantValueXML('serenity_mainmenu_background_color');
      $serenity_mainmenu_links_color = $api->getContantValueXML('serenity_mainmenu_links_color');
      $serenity_mainmenu_links_hover_color = $api->getContantValueXML('serenity_mainmenu_links_hover_color');
      $serenity_minimenu_links_color = $api->getContantValueXML('serenity_minimenu_links_color');
      $serenity_minimenu_link_active_color = $api->getContantValueXML('serenity_minimenu_link_active_color');
			$serenity_mainmenu_links_hover_background_color = $api->getContantValueXML('serenity_mainmenu_links_hover_background_color');
      $serenity_footer_background_color = $api->getContantValueXML('serenity_footer_background_color');
      $serenity_footer_font_color = $api->getContantValueXML('serenity_footer_font_color');
      $serenity_footer_links_color = $api->getContantValueXML('serenity_footer_links_color');
			$serenity_footer_copyright_color = $api->getContantValueXML('serenity_footer_copyright_color');
      $serenity_footer_border_color = $api->getContantValueXML('serenity_footer_border_color');
      $serenity_theme_color = $api->getContantValueXML('serenity_theme_color');
      $serenity_body_background_color = $api->getContantValueXML('serenity_body_background_color');
			$serenity_menu_tip_color = $api->getContantValueXML('serenity_menu_tip_color');
      $serenity_font_color = $api->getContantValueXML('serenity_font_color');
      $serenity_font_color_light = $api->getContantValueXML('serenity_font_color_light');
      $serenity_links_color = $api->getContantValueXML('serenity_links_color');
      $serenity_links_hover_color = $api->getContantValueXML('serenity_links_hover_color');
      $serenity_headline_color = $api->getContantValueXML('serenity_headline_color');
      $serenity_border_color = $api->getContantValueXML('serenity_border_color');
      $serenity_box_background_color = $api->getContantValueXML('serenity_box_background_color');
      $serenity_form_label_color = $api->getContantValueXML('serenity_form_label_color');
      $serenity_input_background_color = $api->getContantValueXML('serenity_input_background_color');
      $serenity_input_font_color = $api->getContantValueXML('serenity_input_font_color');
      $serenity_input_border_color = $api->getContantValueXML('serenity_input_border_color');
      $serenity_button_background_color = $api->getContantValueXML('serenity_button_background_color');
      $serenity_button_background_color_hover = $api->getContantValueXML('serenity_button_background_color_hover');
      $serenity_button_font_color = $api->getContantValueXML('serenity_button_font_color');
      $serenity_button_border_color = $api->getContantValueXML('serenity_button_border_color');
      $serenity_comments_background_color = $api->getContantValueXML('serenity_comments_background_color');
    }

    $this->addElement('Dummy', 'header_settings', array(
        'label' => 'Header Styling Settings',
    ));
    $this->addElement('Text', "serenity_header_background_color", array(
        'label' => 'Header Background Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_header_background_color,
    ));

    $this->addElement('Text', "serenity_mainmenu_background_color", array(
        'label' => 'Main Menu Background Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_mainmenu_background_color,
    ));

    $this->addElement('Text', "serenity_mainmenu_links_color", array(
        'label' => 'Main Menu Link Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_mainmenu_links_color,
    ));

    $this->addElement('Text', "serenity_mainmenu_links_hover_color", array(
        'label' => 'Main Menu Link Hover Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_mainmenu_links_hover_color,
    ));
    $this->addElement('Text', "serenity_mainmenu_links_hover_background_color", array(
        'label' => 'Main Menu Link Hover Background Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_mainmenu_links_hover_background_color,
    ));
		$this->addElement('Text', "serenity_menu_tip_color", array(
        'label' => 'Vertical Main Menu Mouseover Tip Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_menu_tip_color,
    ));

    $this->addElement('Text', "serenity_minimenu_links_color", array(
        'label' => 'Mini Menu Link Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_minimenu_links_color,
    ));

    $this->addElement('Text', "serenity_minimenu_link_active_color", array(
        'label' => 'Mini Menu Link Active Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_minimenu_link_active_color,
    ));

    $this->addDisplayGroup(array('serenity_header_background_color', 'serenity_mainmenu_background_color', 'serenity_mainmenu_links_color', 'serenity_mainmenu_links_hover_color', 'serenity_mainmenu_links_hover_background_color', 'serenity_menu_tip_color', 'serenity_minimenu_links_color', 'serenity_minimenu_link_active_color'), 'header_settings_group', array('disableLoadDefaultDecorators' => true));
    $header_settings_group = $this->getDisplayGroup('header_settings_group');
    $header_settings_group->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'header_settings_group'))));

    $this->addElement('Dummy', 'footer_settings', array(
        'label' => 'Footer Styling Settings',
    ));
    $this->addElement('Text', "serenity_footer_background_color", array(
        'label' => 'Footer Background Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_footer_background_color,
    ));

    $this->addElement('Text', "serenity_footer_font_color", array(
        'label' => 'Footer Font Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_footer_font_color,
    ));

    $this->addElement('Text', "serenity_footer_links_color", array(
        'label' => 'Footer Link Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_footer_links_color,
    ));

   $this->addElement('Text', "serenity_footer_copyright_color", array(
        'label' => 'Footer Copyright Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_footer_copyright_color,
    ));
    $this->addElement('Text', "serenity_footer_border_color", array(
        'label' => 'Footer Border Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_footer_border_color,
    ));
    $this->addDisplayGroup(array('serenity_footer_background_color', 'serenity_footer_font_color', 'serenity_footer_links_color', 'serenity_footer_copyright_color', 'serenity_footer_border_color'), 'footer_settings_group', array('disableLoadDefaultDecorators' => true));
    $footer_settings_group = $this->getDisplayGroup('footer_settings_group');
    $footer_settings_group->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'footer_settings_group'))));

    $this->addElement('Dummy', 'body_settings', array(
        'label' => 'Body Styling Settings',
    ));
    $this->addElement('Text', "serenity_theme_color", array(
        'label' => 'Theme Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_theme_color,
    ));

    $this->addElement('Text', "serenity_body_background_color", array(
        'label' => 'Body Background Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_body_background_color,
    ));

    $this->addElement('Text', "serenity_font_color", array(
        'label' => 'Font Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_font_color,
    ));

    $this->addElement('Text', "serenity_font_color_light", array(
        'label' => 'Font Light Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_font_color_light,
    ));

    $this->addElement('Text', "serenity_links_color", array(
      'label' => 'Link Color',
      'allowEmpty' => false,
      'required' => true,
      'class' => 'SEcolor',
      'value' => $serenity_links_color,
    ));

    $this->addElement('Text', "serenity_links_hover_color", array(
        'label' => 'Link Hover Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_links_hover_color,
    ));

    $this->addElement('Text', "serenity_headline_color", array(
        'label' => 'Headline Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_headline_color,
    ));

    $this->addElement('Text', "serenity_border_color", array(
        'label' => 'Border Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_border_color,
    ));
    $this->addElement('Text', "serenity_box_background_color", array(
        'label' => 'Box Background Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_box_background_color,
    ));

    $this->addElement('Text', "serenity_form_label_color", array(
        'label' => 'Form Label Font Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_form_label_color,
    ));

    $this->addElement('Text', "serenity_input_background_color", array(
        'label' => 'Input Background Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_input_background_color,
    ));

    $this->addElement('Text', "serenity_input_font_color", array(
        'label' => 'Input Font Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_input_font_color,
    ));

    $this->addElement('Text', "serenity_input_border_color", array(
        'label' => 'Input Border Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_input_border_color,
    ));

    $this->addElement('Text', "serenity_button_background_color", array(
        'label' => 'Button Background Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_button_background_color,
    ));
    $this->addElement('Text', "serenity_button_background_color_hover", array(
        'label' => 'Button Background Hovor Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_button_background_color_hover,
    ));

    $this->addElement('Text', "serenity_button_font_color", array(
        'label' => 'Button Font Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_button_font_color,
    ));
    $this->addElement('Text', "serenity_button_border_color", array(
        'label' => 'Button Border Color',
        'allowEmpty' => false,
        'required' => true,
        'class' => 'SEcolor',
        'value' => $serenity_button_border_color,
    ));
    $this->addElement('Text', "serenity_comments_background_color", array(
      'label' => 'Comments Background Color',
      'allowEmpty' => false,
      'required' => true,
      'class' => 'SEcolor',
      'value' => $serenity_comments_background_color,
    ));

    $this->addDisplayGroup(array('serenity_theme_color','serenity_body_background_color', 'serenity_font_color', 'serenity_font_color_light', 'serenity_links_color', 'serenity_links_hover_color','serenity_headline_color', 'serenity_border_color', 'serenity_box_background_color', 'serenity_form_label_color', 'serenity_input_background_color', 'serenity_input_font_color', 'serenity_input_border_color', 'serenity_button_background_color', 'serenity_button_background_color_hover', 'serenity_button_font_color', 'serenity_button_border_color', 'serenity_dashboard_list_background_color_hover', 'serenity_dashboard_list_border_color', 'serenity_dashboard_font_color', 'serenity_dashboard_link_color', 'serenity_comments_background_color'), 'body_settings_group', array('disableLoadDefaultDecorators' => true));
    $body_settings_group = $this->getDisplayGroup('body_settings_group');
    $body_settings_group->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'body_settings_group'))));

    $this->addElement('Button', 'save', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true,
        'decorators' => array('ViewHelper')
    ));
//     $this->addElement('Button', 'submit', array(
//         'label' => 'Save as Draft',
//         'type' => 'submit',
//         'ignore' => true,
//         'decorators' => array('ViewHelper')
//     ));
//     $this->addDisplayGroup(array('save', 'submit'), 'buttons');
  }
}
