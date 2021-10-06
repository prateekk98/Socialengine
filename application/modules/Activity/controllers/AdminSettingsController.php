<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminSettingsController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Activity_AdminSettingsController extends Core_Controller_Action_Admin
{
    public function indexAction()
    {
        // Make form
        $this->view->form = $form = new Activity_Form_Admin_Settings_General();

        // Populate settings
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $values = $settings->activity;
        unset($values['allowed']);
        $form->populate($values);


        if( !$this->getRequest()->isPost() ) {
            return;
        }
        if( !$form->isValid($this->getRequest()->getPost()) ) {
            return;
        }


        // Process
        $values = $form->getValues();
        $settings = Engine_Api::_()->getApi('settings', 'core');
        if (!empty($settings->getSetting('activity.view.privacy'))) {
            $settings->removeSetting('activity.view.privacy');
        }

        // Save settings
        foreach ($values as $key => $value) {
            if($settings->hasSetting('activity.' . $key)) {
                $settings->removeSetting('activity.' . $key);
            }
        }
        $settings->activity = $values;

        $form->addNotice('Your changes have been saved.');
    }

    public function typesAction()
    {
        $selectedType = $this->_getParam('type');
        $selectedModule = $this->_getParam('plugin');
        // Make form
        $this->view->form = $form = new Activity_Form_Admin_Settings_ActionType();

        // Populate settings
        $actionTypesTable = Engine_Api::_()->getDbTable('actionTypes', 'activity');
        $actionTypes = $actionTypesTable->fetchAll();

        $moduleOptions = $moduleBaseActionTypes = array();
        $moduleTable = Engine_Api::_()->getDbTable('modules', 'core');
        foreach( $actionTypes as $actionType ) {
            $moduleBaseActionTypes[$actionType->module][$actionType->type] = 'ADMIN_ACTIVITY_TYPE_' . strtoupper($actionType->type);
            if( isset($moduleOptions[$actionType->module]) ) {
                continue;
            }
            if( $moduleTable->getModule($actionType->module)->enabled ){
                $moduleOptions[$actionType->module] = $moduleTable->getModule($actionType->module)->title;
            }
        }

        asort($moduleOptions);
        if( !$selectedModule || !isset($moduleBaseActionTypes[$selectedModule]) ) {
            $selectedModule = key($moduleOptions);
        }

        $form->plugin->setMultiOptions($moduleOptions);
        $form->populate(array(
            'plugin' => $selectedModule,
        ));

        $typeOptions = $moduleBaseActionTypes[$selectedModule];

        $form->type->setMultiOptions($typeOptions);
        if( !$selectedType || !isset($typeOptions[$selectedType]) ) {
            $selectedType = key($typeOptions);
        }

        $selectedTypeObject = null;
        foreach( $actionTypes as $actionType ) {
            if( $actionType->type == $selectedType ) {
                $selectedTypeObject = $actionType;
                $form->populate($actionType->toArray());
                // Process mulitcheckbox
                $displayable = array();
                if( 4 & (int) $actionType->displayable ) {
                    $displayable[] = 4;
                }
                if( 2 & (int) $actionType->displayable ) {
                    $displayable[] = 2;
                }
                if( 1 & (int) $actionType->displayable ) {
                    $displayable[] = 1;
                }
                $form->populate(array(
                    'displayable' => $displayable,
                ));
            }
        }


        if( !$this->getRequest()->isPost() ) {
            return;
        }
        if( !$form->isValid($this->getRequest()->getPost()) ) {
            return;
        }


        // Process
        $values = $form->getValues();
        $values['displayable'] = array_sum($values['displayable']);

        // Check type
        if( !$selectedTypeObject ||
            !isset($typeOptions[$selectedTypeObject->type]) ||
            $selectedTypeObject->type != $values['type'] ) {
            return $form->addError('Please select a valid type');
        }

        unset($values['type']);

        // Save
        $selectedTypeObject->setFromArray($values);
        $selectedTypeObject->save();

        $form->addNotice('Your changes have been saved.');
    }

    public function notificationsAction()
    {
        // Build the different notification types
        $modules = Engine_Api::_()->getDbtable('modules', 'core')->getModulesAssoc();
        $notificationTypes = Engine_Api::_()->getDbtable('notificationTypes', 'activity')->getNotificationTypes();
        $notificationSettings = Engine_Api::_()->getDbtable('notificationTypes', 'activity')->getDefaultNotifications();

        $notificationTypesAssoc = array();
        $notificationSettingsAssoc = array();
        foreach( $notificationTypes as $type ) {
            if( in_array($type->module, array('core', 'activity', 'fields', 'authorization', 'messages', 'user')) ) {
                $elementName = 'general';
                $category = 'General';
            } else if( isset($modules[$type->module]) ) {
                $elementName = preg_replace('/[^a-zA-Z0-9]+/', '-', $type->module);
                $category = $modules[$type->module]->title;
            } else {
                $elementName = 'misc';
                $category = 'Misc';
            }

            $notificationTypesAssoc[$elementName]['category'] = $category;
            $notificationTypesAssoc[$elementName]['types'][$type->type] = 'ACTIVITY_TYPE_' . strtoupper($type->type);

            if( in_array($type->type, $notificationSettings) ) {
                $notificationSettingsAssoc[$elementName][] = $type->type;
            }
        }

        ksort($notificationTypesAssoc);

        $notificationTypesAssoc = array_filter(array_merge(array(
            'general' => array(),
            'misc' => array(),
        ), $notificationTypesAssoc));


        $this->view->form = $form = new Engine_Form(array(
            'title' => 'Default Notification Alerts',
            'description' => 'This page allows you to specify the default notification alerts for new users.',
        ));

        foreach( $notificationTypesAssoc as $elementName => $info ) {
            $form->addElement('MultiCheckbox', $elementName, array(
                'label' => $info['category'],
                'multiOptions' => $info['types'],
                'value' => (array) @$notificationSettingsAssoc[$elementName],
            ));
        }

        // init submit
        $form->addElement('Button', 'submit', array(
            'label' => 'Save Changes',
            'type' => 'submit',
            'ignore' => true,
        ));

        // Check method
        if( !$this->getRequest()->isPost() ) {
            return;
        }

        if( !$form->isValid($this->getRequest()->getPost()) ) {
            return;
        }

        $values = array();
        foreach( $form->getValues() as $key => $value ) {
            if( !is_array($value) ) continue;

            foreach( $value as $skey => $svalue ) {
                if( !isset($notificationTypesAssoc[$key]['types'][$svalue]) ) {
                    continue;
                }
                $values[] = $svalue;
            }
        }

        Engine_Api::_()->getDbtable('notificationTypes', 'activity')->setDefaultNotifications($values);
        $form->addNotice('Your changes have been saved.');
    }

    public function manageEmoticonsAction()
    {
        $this->view->hasPermission = is_writable(APPLICATION_PATH . DIRECTORY_SEPARATOR .
            'application/modules/Activity/externals/emoticons/images');
        $this->view->emoticons = $emoticons = Engine_Api::_()->activity()->getEmoticons();
    }

    public function addEmoticonAction()
    {
        $this->_helper->layout->setLayout('admin-simple');
        $this->view->form = $form = new Activity_Form_Admin_Settings_Emoticon_Update();

        if(!$this->getRequest()->isPost()) {
            return;
        }
        if(!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        if ($form->Filedata->getValue() == null) {
            return $form->addError('No file selected');
        }
        $values = $form->getValues();
        $emoticons = Engine_Api::_()->activity()->getEmoticons();
        $emoticonName = str_replace(' ', '_', trim($values['name']));
        $emoticonSymbol = str_replace(' ', '_', trim($values['symbol']));

        if ($this->alreadyHasEmoticon($emoticonName)) {
            return $form->addError('Emoticon name already exists');
        }
        if (array_key_exists($emoticonSymbol, $emoticons)) {
            return $form->addError('Emoticon symbol already exists');
        }

        $file = $this->setEmoticonIcon($form->Filedata, $emoticonName);
        $newEmoticon = array($emoticonSymbol => $emoticonName . '.' . $file->extension);
        $emoticons = array_merge($emoticons, $newEmoticon);

        $this->setEmoticonArray($emoticons);
        return $this->_forward('success', 'utility', 'core', array(
            'smoothboxClose' => true,
            'parentRefresh' => true,
            'messages' => array(Zend_Registry::get('Zend_Translate')->_("Emoticon has been created successfully."))
        ));
    }

    public function editEmoticonAction()
    {
        $symbol = $this->_getParam('symbol');
        $this->_helper->layout->setLayout('admin-simple');
        $emoticons = Engine_Api::_()->activity()->getEmoticons();
        $emoticon = $emoticons[$symbol];
        if(empty($emoticon)) {
            return $this->_forward('notfound', 'error', 'core');
        }

        $values['symbol'] = $symbol;
        $values['name'] = substr($emoticon, 0, strrpos($emoticon, '.'));
        $this->view->form = $form = new Activity_Form_Admin_Settings_Emoticon_Update();

        if( !$this->getRequest()->isPost() ) {
            $form->populate($values);
            return;
        }

        if( !$form->isValid($this->getRequest()->getPost()) ) {
            return;
        }

        $values = $form->getValues();
        $emoticonName = str_replace(' ', '_', trim($values['name']));
        $emoticonSymbol = str_replace(' ', '_', trim($values['symbol']));

        if ($this->alreadyHasEmoticon($emoticonName) && $emoticonName !== substr($emoticon, 0, strrpos($emoticon, '.'))) {
            return $form->addError('Emoticon name already exists');
        }
        if (array_key_exists($emoticonSymbol, $emoticons) && $emoticonSymbol !== $symbol) {
            return $form->addError('Emoticon symbol already exists');
        }

        if ($form->Filedata->getValue() !== null) {
            $file = $this->setEmoticonIcon($form->Filedata, $emoticonName);
            $emoticonName .= '.' . $file->extension;
            //unlink(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application/modules/Activity/externals/emoticons/images/'. $emoticon);
        } else {
            if ($emoticonName !== substr($emoticon, 0, strrpos($emoticon, '.'))) {
                $file = $this->setEmoticonIcon(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application/modules/Activity/externals/emoticons/images/'. $emoticon, $emoticonName);
                $emoticonName .= '.' . $file->extension;
                //unlink(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application/modules/Activity/externals/emoticons/images/'. $emoticon);
            }
        }

        if (false === strrpos($emoticonName, '.')) {
            $emoticonName = $emoticons[$symbol];
        }

        unset($emoticons[$symbol]);
        $newEmoticon = array ($emoticonSymbol => $emoticonName);
        $emoticons = array_merge($emoticons, $newEmoticon);

        $this->setEmoticonArray($emoticons);
        return $this->_forward('success', 'utility', 'core', array(
            'smoothboxClose' => true,
            'parentRefresh' => true,
            'messages' => array(Zend_Registry::get('Zend_Translate')->_("Your emoticon has been updated successfully."))
        ));
    }

    public function deleteEmoticonAction()
    {
        $symbol = $this->_getParam('symbol');
        $this->_helper->layout->setLayout('admin-simple');
        $emoticons = Engine_Api::_()->activity()->getEmoticons();
        $emoticon = $emoticons[$symbol];
        if (empty($emoticon)) {
            return $this->_forward('notfound', 'error', 'core');
        }
        $this->view->form = $form = new Activity_Form_Admin_Settings_Emoticon_Delete();

        if( !$this->getRequest()->isPost() ) {
            return;
        }
        if( !$form->isValid($this->getRequest()->getPost()) ) {
            return;
        }

        unset($emoticons[$symbol]);
        unlink(APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application/modules/Activity/externals/emoticons/images/'. $emoticon);

        $this->setEmoticonArray($emoticons);
        return $this->_forward('success', 'utility', 'core', array(
            'smoothboxClose' => true,
            'parentRefresh' => true,
            'messages' => array(Zend_Registry::get('Zend_Translate')->_("Your emoticon has been deleted successfully."))
        ));
    }

    private function setEmoticonIcon($emoticon, $icon)
    {
        if ($emoticon instanceof Zend_Form_Element_File) {
            $file = $emoticon->getFileName();
            $fileName = $file;
        } else if ($emoticon instanceof Storage_Model_File) {
            $file = $emoticon->temporary();
            $fileName = $emoticon->name;
        } else if ($emoticon instanceof Core_Model_Item_Abstract && !empty($emoticon->file_id)) {
            $tmpRow = Engine_Api::_()->getItem('storage_file', $emoticon->file_id);
            $file = $tmpRow->temporary();
            $fileName = $tmpRow->name;
        } else if (is_array($emoticon) && !empty($emoticon['tmp_name'])) {
            $file = $emoticon['tmp_name'];
            $fileName = $emoticon['name'];
        } else if (is_string($emoticon) && file_exists($emoticon)) {
            $file = $emoticon;
            $fileName = $emoticon;
        } else {
            throw new Core_Model_Exception('invalid argument passed to setPhoto');
        }

        if (!$fileName) {
            $fileName = basename($file);
        }

        $extension = ltrim(strrchr(basename($fileName), '.'), '.');
        $base = rtrim(substr(basename($fileName), 0, strrpos(basename($fileName), '.')), '.');
        $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application/modules/Activity/externals/emoticons/images';

        // Save
        $filesTable = Engine_Api::_()->getDbtable('files', 'storage');

        // Resize image (icon)
        $iconPath = $path . DIRECTORY_SEPARATOR . $icon . '.' . $extension;
        $image = Engine_Image::factory();
        $image->open($file)
            ->resize(48, 48)
            ->write($iconPath)
            ->destroy();

        // Store
        $emoticonIcon = $filesTable->createSystemFile($iconPath);
        chmod($iconPath, 0777);
        // Remove base file
        //unlink($path . DIRECTORY_SEPARATOR . $base . '.' . $extension);

        if (!empty($tmpRow)) {
            $tmpRow->delete();
        }
        return $emoticonIcon;
    }

    private function setEmoticonArray($emoticons)
    {
        $filePath = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'application' . DIRECTORY_SEPARATOR
            . 'modules' . DIRECTORY_SEPARATOR
            . "Activity/externals/emoticons/emoticons.php";
        $fileContent = '<?php return array (' . PHP_EOL;
        foreach(array_unique($emoticons) as $symbol => $icon) {
            $fileContent .= '  "' . $symbol . '" => "' . $icon. '",' . PHP_EOL;
        }
        $fileContent .= ');' . PHP_EOL;
        file_put_contents($filePath, $fileContent);
        chmod($filePath, 0777);
    }

    private function alreadyHasEmoticon($emoticonName)
    {
        $found = false;
        foreach (Engine_Api::_()->activity()->getEmoticons() as $symbol => $icon) {
            if ($emoticonName == substr($icon, 0, strrpos($icon, '.'))) {
                $found = true;
                break;
            }
        }
        return $found;
    }

}
