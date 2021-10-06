<?php

class Serenity_Installer extends Engine_Package_Installer_Module {

  public function onInstall() {
    $db = $this->getDb();
    if($this->_databaseOperationType != 'upgrade') {
      $default_constants = array(
        'theme_color'  => '1',
        'custom_theme_color'  => '1',
        'theme_widget_radius' => '1px',
        'serenity_header_background_color'  => '#03598f',
        'serenity_mainmenu_background_color'  => '#fff',
        'serenity_mainmenu_links_color'  => '#000000',
        'serenity_mainmenu_links_hover_color'  => '#fff',
        'serenity_mainmenu_links_hover_background_color'  => '#fe4497',
        'serenity_minimenu_links_color'  => '#FFFFFF',
        'serenity_minimenu_link_active_color'  => '#fe4497',
        'serenity_footer_background_color'  => '#FFFFFF',
        'serenity_footer_font_color'  => '#676767',
        'serenity_footer_links_color'  => '#676767',
				'serenity_footer_copyright_color'  => '#676767',
        'serenity_footer_border_color'  => '#e4e4e4',
        'serenity_theme_color'  => '#03598f',
        'serenity_body_background_color'  => '#e6ecf0',
        'serenity_font_color'  => '#5f727f',
        'serenity_font_color_light'  => '#808D97',
        'serenity_links_color'  => '#444f5d',
        'serenity_links_hover_color'  => '#03598f',
        'serenity_headline_color'  => '#1c2735',
        'serenity_border_color'  => '#e2e4e6',
        'serenity_box_background_color'  => '#FFFFFF',
        'serenity_form_label_color'  => '#455B6B',
        'serenity_input_background_color'  => '#fff',
        'serenity_input_font_color'  => '#5f727f',
        'serenity_input_border_color'  => '#d7d8da',
        'serenity_button_background_color'  => '#03598f',
        'serenity_button_background_color_hover'  => '#fe4497',
        'serenity_button_font_color'  => '#FFFFFF',
        'serenity_button_border_color'  => '#03598f',
        'serenity_comments_background_color'  => '#fff',
  			'serenity_body_fontfamily' => '"Source Sans Pro"',
        'serenity_heading_fontfamily' => '"Source Sans Pro"',
        'serenity_mainmenu_fontfamily' => '"Source Sans Pro"',
        'serenity_tab_fontfamily' => '"Source Sans Pro"',
      );
      $this->readWriteXML('', '', $default_constants);
      
      // landing page
      $select = new Zend_Db_Select($db);
      $select
          ->from('engine4_core_pages')
          ->where('name = ?', 'core_index_index')
          ->limit(1);
      $pageId = $select->query()->fetchObject()->page_id;
      
      $select = new Zend_Db_Select($db);
      $select
          ->from('engine4_core_content')
          ->where('page_id = ?', $pageId)
          ->where('type = ?', 'widget')
          ->where('name = ?', 'core.landing-page-banner');
      $info = $select->query()->fetch();
      if( empty($info) ) {
        // Insert banner
        $db->insert('engine4_core_banners', array(
          'name' => 'core',
          'module' => 'core',
          'title' => 'Welcome to our Social Network!!',
          'body' => 'Love what you see?',
          'photo_id' => 0,
          'params' => '{"label":"Get Started","uri":"signup"}',
          'custom' => 0
        ));
        $bannerId = $db->lastInsertId();
        if( $bannerId ) {
          $db->query('UPDATE `engine4_core_content` SET `params` = \'{"bannerId":"'.$bannerId.'","height":"600","title":"","nomobile":"0","name":"core.landing-page-banner"}\' WHERE `engine4_core_content`.`name` = "core.landing-page-banner" AND `engine4_core_content`.`page_id` = "3";');
        }
      }
    }
    parent::onInstall();
  }
  
  public function onDisable() {

    $db = $this->getDb();

    $db->query("UPDATE  `engine4_core_themes` SET  `active` =  '0' WHERE  `engine4_core_themes`.`name` ='serenity' LIMIT 1");
    $db->query("UPDATE  `engine4_core_themes` SET  `active` =  '1' WHERE  `engine4_core_themes`.`name` ='insignia' LIMIT 1");
    parent::onDisable();
  }

  function onEnable() {

    $db = $this->getDb();

    //Theme Enabled and disabled
    $select = new Zend_Db_Select($db);
    $select->from('engine4_core_themes', 'name')
            ->where('active = ?', 1)
            ->limit(1);
    $themeActive = $select->query()->fetch();
    if($themeActive) {
      $db->query("UPDATE  `engine4_core_themes` SET  `active` =  '0' WHERE  `engine4_core_themes`.`name` ='".$themeActive['name']."' LIMIT 1");
      $db->query("UPDATE  `engine4_core_themes` SET  `active` =  '1' WHERE  `engine4_core_themes`.`name` ='serenity' LIMIT 1");
    }
    parent::onEnable();
  }
  
  function readWriteXML($keys, $value, $default_constants = null) {
    $filePath = APPLICATION_PATH . "/application/settings/constants.xml";
    $results = simplexml_load_file($filePath);

    if (!empty($keys) && !empty($value)) {
        $contactsThemeArray = array($keys => $value);
    } elseif (!empty($keys)) {
        $contactsThemeArray = array($keys => '');
    } elseif ($default_constants) {
        $contactsThemeArray = $default_constants;
    }

    foreach ($contactsThemeArray as $key => $value) {
      $xmlNodes = $results->xpath('/root/constant[name="' . $key . '"]');
      $nodeName = @$xmlNodes[0];
      $params = json_decode(json_encode($nodeName));
      $paramsVal = @$params->value;
      if ($paramsVal && $paramsVal != '' && $paramsVal != null) {
          $nodeName->value = $value;
      } else {
          $entry = $results->addChild('constant');
          $entry->addChild('name', $key);
          $entry->addChild('value', $value);
      }
    }
    return $results->asXML($filePath);
  }
}
