<?php

class Serenity_Form_Admin_Settings_Global extends Engine_Form {

  public function init() {
  
    $this->setTitle('Global Settings')
        ->setDescription('These settings affect all members in your community.');

    $settings = Engine_Api::_()->getApi('settings', 'core');

    $this->addElement('Radio', 'serenity_changelanding', array(
      'label' => 'Set Landing Page of Serenity Theme',
      'description' => 'Do you want to set the Landing Page from this theme and replace the current Landing page with one of the landing page design from this theme? (If you choose Yes and save changes, then later you can manually make changes in the Landing page from Layout Editor. Back up page of your current landing page will get created with the name "Backup - Landing Page".)',
      'onclick' => 'confirmChangeLandingPage(this.value)',
      'multiOptions' => array(
        '1' => 'Yes, Landing Page Design 1',
        '2' => 'Yes, Landing Page Design 2',
        '0' => 'No',
      ),
      'value' => $settings->getSetting('serenity.changelanding', 0),
    ));

    $this->addElement('MultiCheckbox', 'serenity_headerloggedinoptions', array(
      'label' => 'Header Options for Logged in Members',
      'description' => 'Choose from the below options to be available in the header to logged in members on your website.',
      'multiOptions' => array(
          'search' => 'Search',
          'miniMenu' => 'Mini Menu',
          'mainMenu' =>'Main Menu',
          'logo' =>'Logo',
      ),
      'value' => unserialize($settings->getSetting('serenity.headerloggedinoptions', 'a:4:{i:0;s:6:"search";i:1;s:8:"miniMenu";i:2;s:8:"mainMenu";i:3;s:4:"logo";}')),
    ));

    $this->addElement('MultiCheckbox', 'serenity_headernonloggedinoptions', array(
      'label' => 'Header Options for Non-Logged in Members',
      'description' => 'Choose from the below options to be available in the header to non-logged in members on your website.',
      'multiOptions' => array(
          'search' => 'Search',
          'miniMenu' => 'Mini Menu',
          'mainMenu' =>'Main Menu',
          'logo' =>'Logo',
      ),
      'value' => unserialize($settings->getSetting('serenity.headernonloggedinoptions', 'a:4:{i:0;s:6:"search";i:1;s:8:"miniMenu";i:2;s:8:"mainMenu";i:3;s:4:"logo";}')),
    ));

    $this->addElement('Text', 'theme_widget_radius', array(
      'label' => 'Widget Corner Radius',
      'description' => 'Enter the corner radius of widgets on your website in px. Enter 0px if you do not want to give radius at all.',
      'value' => Engine_Api::_()->serenity()->getContantValueXML('theme_widget_radius'),
    ));


    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
    ));
  }
}
