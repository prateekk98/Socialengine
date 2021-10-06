<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Phpfox.php 2015-05-15 00:00:00Z john $
 * @author     John
 */
class Install_Form_Import_Phpfox extends Engine_Form
{
  public function init()
  {
    $this
      ->setAttrib('id', 'form_upload');
    $this
      ->setTitle('PHPfox Import')
      ->setDescription('We will now import your users from PHPfox.');

    $this->setDescription($this->getDescription() . "
<br />
<a style='color:red;' href='javascript:void(0);' onclick='(function(e,obj){ styleVal = $(\"fieldset-advanced\").getStyle(\"display\");
    $(\"fieldset-advanced\").setStyle(\"display\", (styleVal == \"none\" ? \"\" : \"none\"));
    $(\"advancedOption\").set(\"text\",(styleVal == \"none\" ? \"Hide Advanced Options\" : \"Show Advanced Options\"));
    })(event,this)' id='advancedOption'>
  Show Advanced Options
</a>

");
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);

    $this->addElement('Radio', 'phpfox_version', array(
      'label' => 'PHPfox Version',
      'multiOptions' => array(
        '1' => 'v3',
        '2' => "v4",
      ),
      'required' => true,
      'value' => 1
    ));
    $this->addElement('Text', 'path', array(
      'label' => 'PHPfox Path',
      'description' => 'This is the server folder where PHPfox is
        currently installed. It must be properly installed in order to import
        correctly.',
      'value' => realpath($_SERVER['DOCUMENT_ROOT']),
      'required' => true,
      'allowEmpty' => false,
    ));

    $this->addElement('Text', 'emailid_superadmin', array(
      'label' => 'Super Admin Email Address',
      'description' => 'Please enter the new email address if you want to update the current email address of super admin.',
      'required' => true,
      'allowEmpty' => false,
      'allowEmpty' => false,
      'validators' => array(
        array('NotEmpty', true),
        array('EmailAddress', true)
      ),
    ));
    $this->emailid_superadmin->getDecorator('Description')->setOptions(array('placement' => 'PREPEND'));
    $this->emailid_superadmin->getValidator('NotEmpty')->setMessage('Please enter a valid email address.', 'isEmpty');
    $this->emailid_superadmin->getValidator('EmailAddress')->getHostnameValidator()->setValidateTld(false);


    $this->addElement('Text', 'superadminpassword', array(
      'label' => 'Super Admin Password',
      'description' => 'Please enter the new password for super admin if you want to update the default password 123456. This updated password will not affect other members default password.',
      'validators' => array(
        array('NotEmpty', false),
        array('StringLength', false, array(6, 32)),
      ),
    ));

    $sitepageExists = $this->_checkModuleExist('sitepage');
      

      $this->addElement('hidden', 'import_group_sepage', array(
      'value' => 1,
      'order' => '458'
    ));
   
    $this->addElement('hidden', 'import_group_segroup', array(
      'value' => 1,
      'order' => '444'
    ));

    $this->addElement('hidden', 'import_event_seevent', array(
      'value' => 1,
      'order' => '555'
    ));


    $this->addElement('hidden', 'import_blog_seblog', array(
      'value' => 1,
      'order' => '556'
    ));


    $this->addElement('hidden', 'import_classified_seclassified', array(
      'value' => 1,
      'order' => '557'
    ));


    $this->addElement('Text', 'adminUserGroupId', array(
      'label' => 'Administrator User Group ID',
      'description' => 'Please enter the administrator user group id of your PHPFox website. Please leave it blank, if administrator user group name of your PHPFox website is using ‘Administrator’.',
      'value' => '',
    ));
    $this->addElement('Text', 'registeredUserGroupId', array(
      'label' => 'Registered User Group ID',
      'description' => 'Please enter the registered user group id of your PHPFox website. Please leave it blank, if registered user group name of your PHPFox website is using ‘Registered’.',
      'value' => '',
    ));
    $this->addElement('Text', 'guestUserGroupId', array(
      'label' => 'Guest User Group ID',
      'description' => 'Please enter the guest user group id of your PHPFox website. Please leave it blank, if guest user group name of your PHPFox website is using ‘Guest’.',
      'value' => '',
    ));
    $this->addElement('Text', 'staffUserGroupId', array(
      'label' => 'Staff User Group ID',
      'description' => 'Please enter the staff user group id of your PHPFox website. Please leave it blank, if staff user group name of your PHPFox website is using ‘Staff’.',
      'value' => '',
    ));
    $this->addElement('Text', 'bannedUserGroupId', array(
      'label' => 'Banned User Group ID',
      'description' => 'Please enter the banned user group id of your PHPFox website. Please leave it blank, if banned user group name of your PHPFox website is using ‘Banned’.',
      'value' => '',
    ));
    $this->addElement('Radio', 'checkmode', array(
      'label' => 'Mode',
      'description' => 'How do you want to run this PHPfox import script? If you will select Test Mode, you can reset the password for all members, but in Live Mode either random passwords can be generated and emailed to each members or members can reset their passwords using forget password link.',
      'multiOptions' => array(
        '1' => 'Test Mode',
        '2' => 'Live Mode',
      ),
      'value' => 1,
      'onclick' => 'displayOptions();
                ',
    ));


    $this->addElement('Dummy', 'check_mode_dummy', array(
      'description' => '<ul class="form-errors"><li><ul class="errors"><li>It is recommended to put your PHPFox website offline before starting this importer script, because if users are adding content during the migration process, then those content will not migrate and you will lost those data.</li></ul></li></ul>'
    ));
    $this->getElement('check_mode_dummy')->getDecorator('Description')->setOptions(array('placement', 'APPEND', 'escape' => false));
    $this->addDisplayGroup(array('check_mode_dummy'), 'modedummy', array(
      'style' => 'display:block',
    ));

    // Element: password
    $this->addElement('Password', 'password', array(
      'label' => 'Test Password',
      'description' => 'Please enter the password. This password must be at-least six characters in length and it will be set for all the members. You can leave it blank to set default password 123456.',
      'value' => '123456',
      'validators' => array(
        array('NotEmpty', true),
        array('StringLength', false, array(6, 32)),
      ),
    ));
    $this->password->getValidator('NotEmpty')->setMessage('Please enter a valid password.', 'isEmpty');

    $this->addElement('Radio', 'passwordRegeneration', array(
      'label' => 'Password Regeneration',
      'description' => 'PHPfox does not export your member\'s passwords.',
      'multiOptions' => array('random' => 'Email a random password to each member.',
        'none' => 'Do nothing. Members can reset their password using the forgot password link from the login page.',
      ),
      'value' => 'none',
      'required' => true,
      'allowEmpty' => false,
      'onclick' => '$("fieldset-mail").setStyle("display", $(this).get("value") != "random" ? "none" : "")'
    ));
    $this->addElement('Text', 'mailFromAddress', array(
      'label' => 'From Address',
      'value' => 'no-reply@' . $_SERVER['HTTP_HOST']
    ));

    $this->addElement('Text', 'mailSubject', array(
      'label' => 'Subject',
      'value' => 'New password for {siteUrl}',
    ));

    $this->addElement('Textarea', 'mailTemplate', array('label' => 'Message Template',
      'allowEmpty' => false,
      'value' => "
Hello {name},

Your password has been regenerated.

Site: {siteUrl}
Email: {email}
Password: {password}

Site Administration
",
    ));

    $this->addDisplayGroup(array('mailFromAddress', 'mailSubject', 'mailTemplate'), 'mail', array(
      'style' => 'display:none;',
    ));


    $this->addElement('Text', 'email', array(
      'label' => 'Email Address', 'description' => 'Progress will be emailed to this address.',
    ));
    $this->addElement('MultiCheckbox', 'emailOptions', array(
      'label' => 'Email Options',
      'multiOptions' => array(
        'start' => 'On Start (to test email works)',
        'step' => 'Each time a step completes',
        'timeout' => 'Every selected number of minutes',
        'warning' => 'When a warning occurs',
        'error' => 'When a recoverable error occurs',
        'fatal' => 'When a fatal error occurs',
        'complete' => 'On Completion',
      ),
      'value' => array(
        'start',
        'fatal',
        'complete',
      ),
    ));
    $this->addElement('Text', 'emailTimeout', array('description' => 'Duration for "Every selected number of minutes" in "Email Options"',
      'label' => 'Durations',
      'value' => 10,
      'required' => true,
      'allowEmpty' => false,
      'validators' => array('Int',
        array('GreaterThan', false, array(0)),
      ),
    ));

    $this->addElement('Radio', 'mode', array(
      'label' => 'Execution Mode',
      'required' => true,
      'allowEmpty' => false,
      'multiOptions' => array(
        'split' => 'Separate requests for each type of data',
        'all' => 'All-at-once',
      ),
      'value' => 'split',
    ));

    $this->addElement('Radio', 'resizePhotos', array('label' => 'Resize Photos?',
      'description' => 'Note: This will make the import process take much longer.',
      'required' => true,
      'allowEmpty' => false,
      'multiOptions' => array(
        1 => 'Yes',
        0 => 'No',
      ),
      'value' => 1,
    ));

    $this->addElement('Radio', 'skipClearCache', array('label' => 'Skip Clearing the Cache?',
      'required' => true,
      'allowEmpty' => false,
      'description' => 'Note: This may break stuff.',
      'multiOptions' => array(1 => 'Yes',
        0 => 'No',
      ),
      'value' => 0,
    ));

    $this->addElement('Text', 'batchCount', array(
      'label' => 'Rows per request',
      'required' => true,
      'allowEmpty' => false,
      'validators' => array(
        'Int',
        array('GreaterThan', false, array(0)),
      ),
      'value' => 500,
    ));

    $this->addElement('Text', 'selectCount', array('label' => 'Rows per select',
      'required' => true,
      'allowEmpty' => false,
      'validators' => array('Int',
        array('GreaterThan', false, array(0)),
      ),
      'value' => 100,
    ));

    $this->addElement('Text', 'maxAllowedTime', array(
      'label' => 'Max time per request',
      'description' => 'Step will return early if it detects it is going to go over this amount of time (in seconds).',
      'required' => true,
      'allowEmpty' => false,
      'validators' => array('Int',
        array('GreaterThan', false, array(0)),
      ),
      'value' => 240,
    ));

    $this->addElement('Multiselect', 'disabledSteps', array('label' => 'Disable Steps',
      'description' => 'Select to disable.',
      'style' => 'height: 500px; width: 300px;',
    ));

    $formEleArray = array('email',
      'emailOptions',
      'emailTimeout',
      'mode',
      'resizePhotos',
      'skipClearCache',
      'batchCount',
      'selectCount',
      'maxAllowedTime',
      'disabledSteps',
    );

    //
    $this->addDisplayGroup($formEleArray, 'advanced', array('style' => 'display:none',
    ));

    $this->addElement('Button', 'execute', array('label' => 'Import',
      'type' => 'submit',

    ));
  }

  protected function _checkModuleExist($moduleName)
  {
    $db = Zend_Registry::get('Zend_Db');

    $pluginEnabled = $db->select()
      ->from('engine4_core_modules', 'enabled')
      ->where('name = ?', $moduleName)
      ->limit(1)
      ->query()
      ->fetchColumn(0);
    if( $pluginEnabled === FALSE )
    //Module is not installed.
      return 0;
    else if( $pluginEnabled == 0 )
    //Module disabled
      return 1;
    // Module is Enable
    return 2;
  }
}
?> 

<script type="text/javascript">
  var form = document.getElementById("form_upload");
  window.addEvent('domready', function () {
    displayOptions();
  });

  function closeSmoothbox()
  {
    $$('.form-errors')[0].style.display = 'none';
    parent.Smoothbox.close();
  }
  function displayOptions() {
    value = $$('input[name=checkmode]:checked')[0].get('value');
    if (form && value == 1) {
      $('modedummy-wrapper').style.display = 'none';
      $('password-wrapper').style.display = 'block';
      $('passwordRegeneration-wrapper').style.display = 'none';
    } else if (form && value == 2) {
      $('modedummy-wrapper').style.display = 'block';
      $('passwordRegeneration-wrapper').style.display = 'block';
      $('password-wrapper').style.display = 'none';
      if (form.elements["passwordRegeneration"].value == 'random') {
        $("fieldset-mail").setStyle("display", "");
      } else if (form.elements["passwordRegeneration"].value == 'none') {
        $("fieldset-mail").setStyle("display", "none");
      }
    }
  }

</script>


<style type="text/css">
  .global_form_1 {
    clear:both;
    overflow:hidden;
    margin:15px 0 0 15px;
    width:640px;
  }
  .global_form_1 > div {
    -moz-border-radius:7px 7px 7px 7px;
    background-color:#E9F4FA;
    float:left;
    width:600px;
    overflow:hidden;
    padding:10px;
  }
  .global_form_1 > div > div {
    background:none repeat scroll 0 0 #FFFFFF;
    border:1px solid #D7E8F1;
    overflow:hidden;
    padding:20px;
  }
  .global_form_1 .form-sucess {
    margin-bottom:10px;
  }
  .global_form_1 .form-sucess li {
    -moz-border-radius:4px 4px 4px 4px;
    background:#C8E4B6;
    border:2px solid #95b780;
    color:#666666;
    font-weight:bold;
    padding:0.5em 0.8em;
  }
  table td
  {
    border-bottom:1px solid #f1f1f1; 
    padding:5px;
    vertical-align:top;
  }

  #import_group_sepage_dummy-element .description,  #import_group_segroup_dummy-element .description, #import_event_seevent_dummy-element .description, #import_blog_seblog_dummy-element .description, #import_classified_seclassified_dummy-element .description{
    display:none;
  }

  input[name="import_blog_seblog"] {
    vertical-align:top;
  }

  input[name="import_blog_seblog"] + label {
    width:97%;  
  }

  input[name="import_classified_seclassified"] {
    vertical-align:top;
  }

  input[name="import_classified_seclassified"] + label {
    width:97%;  
  }


</style>
