<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Form_Admin_Settings_Spam extends Engine_Form
{
  protected $_captcha_options = array(
        1 => 'Yes, make members complete the CAPTCHA form.',
        0 => 'No, do not show a CAPTCHA form.',
  );

  public function init()
  {

    $maindescription = $this->getTranslator()->translate(
        'Social networks are often the target of aggressive spam tactics. This most often comes in the form of fake user accounts and spam in comments. On this page, you can manage various anti-spam and censorship features. Note: To turn on the signup image verification feature (a popular anti-spam tool), see the Signup Progress page. <br>');

	$settings = Engine_Api::_()->getApi('settings', 'core');

	if( $settings->getSetting('user.support.links', 0) == 1 ) {
	  $moreinfo = $this->getTranslator()->translate(
        'More Info: <a href="%1$s" target="_blank"> KB Article</a>');
	} else {
	  $moreinfo = $this->getTranslator()->translate(
        '');
	}

    $maindescription = vsprintf($maindescription.$moreinfo, array(
      'https://socialengine.atlassian.net/wiki/spaces/SU/pages/5341468/se-php-spam-and-banning-tools',
    ));

	// Decorators
    $this->loadDefaultDecorators();
	$this->getDecorator('Description')->setOption('escape', false);

    // Set form attributes
    //$this->setTitle('Spam & Banning Tools');
    $this->setDescription($maindescription);

    // init ip-range ban
    $translator = $this->getTranslator();
    if( $translator ) {
      $description = sprintf($translator->translate('CORE_FORM_ADMIN_SETTINGS_SPAM_IPBANS_DESCRIPTION'), Engine_IP::normalizeAddress(Engine_IP::getRealRemoteAddress()));
    } else {
      $description = 'CORE_FORM_ADMIN_SETTINGS_SPAM_IPBANS_DESCRIPTION';
    }
    $this->addElement('Textarea', 'bannedips', array(
      'label' => 'IP Address Ban',
      'description' => $description,
    ));

    // init email bans
    $this->addElement('Textarea', 'bannedemails', array(
      'label' => 'Email Address Ban',
      'description' => 'CORE_FORM_ADMIN_SETTINGS_SPAM_EMAILBANS_DESCRIPTION',
    ));

    // init username bans
    $this->addElement('Textarea', 'bannedusernames', array(
      'label' => 'Profile Address Ban',
      'description' => 'CORE_FORM_ADMIN_SETTINGS_SPAM_USERNAMEBANS_DESCRIPTION',
    ));

    // init censored words
    $this->addElement('Textarea', 'bannedwords', array(
      'label' => 'Censored Words',
      'description' => 'CORE_FORM_ADMIN_SETTINGS_SPAM_CENSOR_DESCRIPTION',
    ));

   $this->addElement('Radio', 'email_antispam_signup', array(
      'label' => 'Enable anti-spamming technique in signup form?',
      'multiOptions' => array(
          1 => 'Yes, enable anti-spamming technique in signup form for email field.',
          0 => 'No, do not enable anti-spamming technique in signup form for email field.',
      ),
      'value' => 1,
    ));

    $this->addElement('Radio', 'email_antispam_login', array(
      'label' => 'Enable anti-spamming technique in login form?',
      'multiOptions' => array(
          1 => 'Yes, enable anti-spamming technique in login form for email field.',
          0 => 'No, do not enable anti-spamming technique in login form for email field.',
      ),
      'value' => 1,
    ));

    $this->addElement('Radio', 'signup', array(
      'label' => 'Require new users to enter validation code when signing up?',
      'multiOptions' => $this->_captcha_options,
      'value' => 0,
    ));

    $this->addElement('Radio', 'invite', array(
      'label' => 'Require users to enter validation code when inviting others?',
      'multiOptions' => $this->_captcha_options,
      'value' => 0,
    ));

    $this->addElement('Radio', 'login', array(
      'label' => 'Require users to enter validation code when signing in?',
      'multiOptions' => $this->_captcha_options,
      'value' => 0,
    ));

    $this->addElement('Radio', 'contact', array(
      'label' => 'Require users to enter validation code when using the contact form?',
      'multiOptions' => array(
        2 => 'Yes, make everyone complete the CAPTCHA form.',
        1 => 'Yes, make visitors complete CAPTCHA, but members are exempt.',
        0 => 'No, do not show a CAPTCHA form to anyone.',
      ),
      'value' => 0,
    ));

    // init profile
    $this->addElement('Radio', 'comment', array(
      'label' => 'Require users to enter validation code when commenting?',
      'multiOptions' => $this->_captcha_options,
      'value' => 0,
    ));


    //lock account

  $this->addElement('Radio', 'lockaccount', array(
      'label' => 'Block Account on Unsuccessful Login Attempts',
      'multiOptions' => array(
          1 => 'Yes, block user account on unsuccessful login attempts.',
          0 => 'No, do not block user account on unsuccessful login attempts.',
      ),
      'onclick'=>'changeLock(this);',
      'value' => 0,
  ));
      $this->addElement('Text', 'lockattempts', array(
          'label' => 'Number of Unsuccessful Attempts',
          'description' => 'Enter the number of unsuccessful login attempts after which user accounts will be blocked for login.',
          'validators' => array(
              array('Int', true),
              array('GreaterThan', true, array(0)),
          ),
          'value' => 3,
      ));

      $this->addElement('Text', 'lockduration', array(
          'label' => 'Account Block Duration',
          'description' => 'CORE_ADMIN_FORM_SETTINGS_SPAM_LOCKDURATION_DESCRIPTION',
          'validators' => array(
              array('Int', true),
              array('GreaterThan', true, array(0)),
          ),
          'value' => 120,
      ));

    $this->addElement('Radio', 'otpfeatures', array(
      'label' => 'Enable Two Step Authentication for Delete Account?',
      'description' => 'If you have selected YES, members will receive a code on their registered mail id and have to enter that code for verification. If you select NO, then users can directly delete their account.',
      'multiOptions' => array(
          1 => 'Yes, allow to receive code for verification',
          0 => 'No, do not enable two step authentication',
      ),
      'value' => 1,
    ));

    $this->addElement('Radio', 'recaptcha_version', array(
      'label' => 'ReCaptcha Version',
      'description' => 'Choose from the below ReCaptcha Version which you want to enable on your site?',
      'multiOptions' => array(
          1 => 'reCAPTCHA v2',
          0 => 'reCAPTCHA v3',
      ),
      'onchange' => 'recaptchaVersion(this.value);',
      'value' => 1,
    ));

    // recaptcha
    if( $translator ) {
      $description = sprintf($translator->translate('You can obtain API credentials at: %1$s'),
          $this->getView()->htmlLink('https://www.google.com/recaptcha',
              'https://www.google.com/recaptcha'));
    } else {
      $description = null;
    }

    //recaptcha v2
    $this->addElement('Text', 'recaptchapublic', array(
      'label' => 'ReCaptcha Public Key for v2',
      'description' => $description,
      'filters' => array(
        'StringTrim',
      ),
    ));
    $this->getElement('recaptchapublic')
        ->getDecorator('Description')
        ->setOption('escape', false);

    $this->addElement('Text', 'recaptchaprivate', array(
      'label' => 'ReCaptcha Private Key for v2',
      'description' => $description,
      'filters' => array(
        'StringTrim',
      ),
    ));
    $this->getElement('recaptchaprivate')
        ->getDecorator('Description')
        ->setOption('escape', false);

    //recaptcha v3
    $this->addElement('Text', 'recaptchapublicv3', array(
      'label' => 'ReCaptcha Public Key for v3',
      'description' => $description,
      'filters' => array(
        'StringTrim',
      ),
    ));
    $this->getElement('recaptchapublicv3')
        ->getDecorator('Description')
        ->setOption('escape', false);

    $this->addElement('Text', 'recaptchaprivatev3', array(
      'label' => 'ReCaptcha Private Key for v3',
      'description' => $description,
      'filters' => array(
        'StringTrim',
      ),
    ));
    $this->getElement('recaptchaprivatev3')
        ->getDecorator('Description')
        ->setOption('escape', false);

    // tokens
//    $this->addElement('Radio', 'tokens', array(
//      'label' => 'Use Tokens?',
//      'multiOptions' => array(
//        1 => 'Yes, use security tokens.',
//        0 => 'No, do not use security tokens.',
//      ),
//    ));

    // comment html
    $this->addElement('Text', 'commenthtml', array(
      'label' => 'Allow HTML in Comments?',
      'description' => 'CORE_ADMIN_FORM_SETTINGS_SPAM_COMMENTHTML_DESCRIPTION'
    ));

    // init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
    ));
  }
}
