<?php

class Serenity_Form_Admin_Settings_Fonts extends Engine_Form {

  public function init() {

    $settings = Engine_Api::_()->getApi('settings', 'core');

    $this->setTitle('Manage Fonts')
        ->setDescription('Here, you can configure the font settings for this theme on your website. You can also choose to enable the Google Fonts.');

    $url = "https://www.googleapis.com/webfonts/v1/webfonts?key=AIzaSyDczHMCNc0JCmJACM86C7L8yYdF9sTvz1A";
    
    $ch = curl_init();
  
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $data = curl_exec($ch);
    curl_close($ch);

    $results = json_decode($data,true);
    
    $googleFontArray = array();
    foreach($results['items'] as $re) {
      $googleFontArray['"'.$re["family"].'"'] = $re['family'];
    }

    $this->addElement('Select', 'serenity_googlefonts', array(
      'label' => 'Choose Fonts',
      'description' => 'Choose from below the Fonts which you want to enable in this theme.',
      'multiOptions' => array(
        '0' => 'Web Safe Font Combinations',
        '1' => 'Google Fonts',
      ),
      'onchange' => "usegooglefont(this.value)",
      'value' => $settings->getSetting('serenity.googlefonts', 1),
    ));
    
    $font_array = array(
      'Georgia, serif' => 'Georgia, serif',
      '"Palatino Linotype", "Book Antiqua", Palatino, serif' => '"Palatino Linotype", "Book Antiqua", Palatino, serif',
      '"Times New Roman", Times, serif' => '"Times New Roman", Times, serif',
      'Arial, Helvetica, sans-serif' => 'Arial, Helvetica, sans-serif',
      '"Arial Black", Gadget, sans-serif' => '"Arial Black", Gadget, sans-serif',
      '"Comic Sans MS", cursive, sans-serif' => '"Comic Sans MS", cursive, sans-serif',
      'Impact, Charcoal, sans-serif' => 'Impact, Charcoal, sans-serif',
      '"Lucida Sans Unicode", "Lucida Grande", sans-serif' => '"Lucida Sans Unicode", "Lucida Grande", sans-serif',
      'Tahoma, Geneva, sans-serif' => 'Tahoma, Geneva, sans-serif',
      '"Trebuchet MS", Helvetica, sans-serif' => '"Trebuchet MS", Helvetica, sans-serif',
      'Verdana, Geneva, sans-serif' => 'Verdana, Geneva, sans-serif',
      '"Courier New", Courier, monospace' => '"Courier New", Courier, monospace',
      '"Lucida Console", Monaco, monospace' => '"Lucida Console", Monaco, monospace',
    );
    
    
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    $link = '<a href="http://www.w3schools.com/cssref/css_websafe_fonts.asp" target="_blank">here</a>.';
    $bodyDes = sprintf('You can see the web safe fonts %s',$link);
    $headingDes = sprintf('You can see the web safe fonts %s',$link);
    $mainmenuDes = sprintf('You can see the web safe fonts %s',$link);
    $tabDes = sprintf('You can see the web safe fonts %s',$link);
    
    //Google Font Work
    $link = '<a href="https://www.google.com/fonts" target="_blank">here</a>.';
    $bodygoogleDes = sprintf('You can see the google fonts %s',$link);
    $headinggoogleDes = sprintf('You can see the google fonts %s',$link);
    $mainmenugoogleDes = sprintf('You can see the google fonts %s',$link);
    $tabgoogleDes = sprintf('You can see the google fonts %s',$link);
    
    //Body Settings

    $this->addElement('Select', 'serenity_body_fontfamily', array(
      'label' => 'Body - Font Family',
      'description' => "Choose font family for the text under Body Styling.",
      'multiOptions' => $font_array,
      'value' => Engine_Api::_()->serenity()->getContantValueXML('serenity_body_fontfamily'),
    ));
    $this->getElement('serenity_body_fontfamily')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
    
    $this->addDisplayGroup(array('serenity_body_fontfamily'), 'serenity_bodygrp', array('disableLoadDefaultDecorators' => true));
    $serenity_bodygrp = $this->getDisplayGroup('serenity_bodygrp');
    $serenity_bodygrp->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'serenity_bodygrp'))));

    //Google Font work
    $this->addElement('Select', 'serenity_googlebody_fontfamily', array(
      'label' => 'Body - Font Family',
      'description' => "Choose font family for the text under Body Styling.",
      'multiOptions' => $googleFontArray,
      'value' => Engine_Api::_()->serenity()->getContantValueXML('serenity_body_fontfamily'),
    ));
    $this->getElement('serenity_googlebody_fontfamily')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
    
    $this->addDisplayGroup(array('serenity_googlebody_fontfamily'), 'serenity_googlebodygrp', array('disableLoadDefaultDecorators' => true));
    $serenity_googlebodygrp = $this->getDisplayGroup('serenity_googlebodygrp');
    $serenity_googlebodygrp->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'serenity_googlebodygrp'))));

    //Heading Settings
    $this->addElement('Select', 'serenity_heading_fontfamily', array(
      'label' => 'Heading - Font Family',
      'description' => "Choose font family for the text under Heading Styling.",
      'multiOptions' => $font_array,
      'value' => Engine_Api::_()->serenity()->getContantValueXML('serenity_heading_fontfamily'),
    ));
    $this->getElement('serenity_heading_fontfamily')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
    
    $this->addDisplayGroup(array('serenity_heading_fontfamily'), 'serenity_headinggrp', array('disableLoadDefaultDecorators' => true));
    $serenity_headinggrp = $this->getDisplayGroup('serenity_headinggrp');
    $serenity_headinggrp->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'serenity_headinggrp'))));
    
    
    //Google Font work
    $this->addElement('Select', 'serenity_googleheading_fontfamily', array(
      'label' => 'Heading - Font Family',
      'description' => "Choose font family for the text under Heading Styling.",
      'multiOptions' => $googleFontArray,
      'value' => Engine_Api::_()->serenity()->getContantValueXML('serenity_heading_fontfamily'),
    ));
    $this->getElement('serenity_googleheading_fontfamily')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
    
    $this->addDisplayGroup(array('serenity_googleheading_fontfamily'), 'serenity_googleheadinggrp', array('disableLoadDefaultDecorators' => true));
    $serenity_googleheadinggrp = $this->getDisplayGroup('serenity_googleheadinggrp');
    $serenity_googleheadinggrp->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'serenity_googleheadinggrp'))));

   //Main Menu Settings
     $this->addElement('Select', 'serenity_mainmenu_fontfamily', array(
       'label' => 'Main Menu - Font Family',
       'description' => "Choose font family for the text under Main Menu Styling.",
       'multiOptions' => $font_array,
       'value' => Engine_Api::_()->serenity()->getContantValueXML('serenity_mainmenu_fontfamily'),
     ));
     $this->getElement('serenity_mainmenu_fontfamily')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
     
     $this->addDisplayGroup(array('serenity_mainmenu_fontfamily'), 'serenity_mainmenugrp', array('disableLoadDefaultDecorators' => true));
     $serenity_mainmenugrp = $this->getDisplayGroup('serenity_mainmenugrp');
     $serenity_mainmenugrp->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'serenity_mainmenugrp'))));
    
     //Google Font work
     $this->addElement('Select', 'serenity_googlemainmenu_fontfamily', array(
       'label' => 'Main Menu - Font Family',
       'description' => "Choose font family for the text under Main Menu Styling.",
       'multiOptions' => $googleFontArray,
       'value' => Engine_Api::_()->serenity()->getContantValueXML('serenity_mainmenu_fontfamily'),
     ));
     $this->getElement('serenity_googlemainmenu_fontfamily')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
     
     $this->addDisplayGroup(array('serenity_googlemainmenu_fontfamily'), 'serenity_googlemainmenugrp', array('disableLoadDefaultDecorators' => true));
     $serenity_googlemainmenugrp = $this->getDisplayGroup('serenity_googlemainmenugrp');
     $serenity_googlemainmenugrp->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'serenity_googlemainmenugrp'))));

    //Tab Settings
    $this->addElement('Select', 'serenity_tab_fontfamily', array(
      'label' => 'Tab - Font Family',
      'description' => "Choose font family for the text under Tab Styling.",
      'multiOptions' => $font_array,
      'value' => Engine_Api::_()->serenity()->getContantValueXML('serenity_tab_fontfamily'),
    ));
    $this->getElement('serenity_tab_fontfamily')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
    
    $this->addDisplayGroup(array('serenity_tab_fontfamily'), 'serenity_tabgrp', array('disableLoadDefaultDecorators' => true));
    $serenity_tabgrp = $this->getDisplayGroup('serenity_tabgrp');
    $serenity_tabgrp->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'serenity_tabgrp'))));
    
    
    //Google Font work
    $this->addElement('Select', 'serenity_googletab_fontfamily', array(
      'label' => 'Tab - Font Family',
      'description' => "Choose font family for the text under Tab Styling.",
      'multiOptions' => $googleFontArray,
      'value' => Engine_Api::_()->serenity()->getContantValueXML('serenity_tab_fontfamily'),
    ));
    $this->getElement('serenity_googletab_fontfamily')->getDecorator('Description')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
    
    $this->addDisplayGroup(array('serenity_googletab_fontfamily'), 'serenity_googletabgrp', array('disableLoadDefaultDecorators' => true));
    $serenity_googletabgrp = $this->getDisplayGroup('serenity_googletabgrp');
    $serenity_googletabgrp->setDecorators(array('FormElements', 'Fieldset', array('HtmlTag', array('tag' => 'div', 'id' => 'serenity_googletabgrp'))));

    // Add submit button
    $this->addElement('Button', 'submit', array(
        'label' => 'Save Changes',
        'type' => 'submit',
        'ignore' => true
    ));
  }
}
