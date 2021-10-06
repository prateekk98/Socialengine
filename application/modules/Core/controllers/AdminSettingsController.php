<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminSettingsController.php 10197 2014-05-05 21:09:21Z andres $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_AdminSettingsController extends Core_Controller_Action_Admin
{
    public function generalAction()
    {
        $this->view->form = $form = new Core_Form_Admin_Settings_General();

        // Get settings
        $global_settings_file = APPLICATION_PATH . '/application/settings/general.php';
        if (file_exists($global_settings_file)) {
            $generalConfig = include $global_settings_file;
        } else {
            $generalConfig = array();
        }

        // Populate form
        $form->populate(Engine_Api::_()->getApi('settings', 'core')->getFlatSetting('core_general', array()));
        if (_ENGINE_ADMIN_NEUTER) {
            return;
        }
        $form->populate(array(
            'maintenance_mode' => !empty($generalConfig['maintenance']['enabled']),
            'maintenance_code' => (!empty($generalConfig['maintenance']['code']) ? $generalConfig['maintenance']['code'] : $this->_createRandomPassword(5)),
            'staticBaseUrl' => Engine_Api::_()->getApi('settings', 'core')->getSetting('core.static.baseurl'),
            'analytics' => Engine_Api::_()->getApi('settings', 'core')->getSetting('core.analytics.code'),
            'site_favicon' => Engine_Api::_()->getApi('settings', 'core')->getSetting('core.site.favicon'),
            'sell_info'=>Engine_Api::_()->getApi('settings', 'core')->getSetting('core.sell.info',1)
        ));

        // Check post/valid
        if (!$this->getRequest()->isPost()) {
            return;
        }
        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        // Process form
        $values = $form->getValues();
        $maintenance = $values['maintenance_mode'];
        $maintenanceCode = $values['maintenance_code'];
        unset($values['maintenance_mode']);
        unset($values['maintenance_code']);
        if (empty($maintenanceCode)) {
            $maintenanceCode = $this->_createRandomPassword(5);
            $form->populate(array(
                'maintenance_code' => $maintenanceCode,
            ));
        }

        if(!@isset($values['site_favicon'])){
            unset($values['site_favicon']);
        }
        // Save settings
        Engine_Api::_()->getApi('settings', 'core')->core_general = $values;

        // Save static base url
        Engine_Api::_()->getApi('settings', 'core')->setSetting('core.static.baseurl', @$values['staticBaseUrl']);

        // Save google analytics code
        Engine_Api::_()->getApi('settings', 'core')->setSetting('core.analytics.code', @$values['analytics']);

        if(@isset($values['sell_info'])){
            Engine_Api::_()->getApi('settings', 'core')->setSetting('core.sell.info', @$values['sell_info']);
        }

        if(@isset($values['site_favicon'])){
            // Save Favicon Upload
            Engine_Api::_()->getApi('settings', 'core')->setSetting('core.site.favicon', @$values['site_favicon']);
        }
        // Save public level view permission
        $publicLevel = Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel();
        Engine_Api::_()->authorization()->levels->setAllowed('user', $publicLevel, 'view', (bool) $values['profile']);

        // Save maintenance mode
        $generalConfig['maintenance']['enabled'] = (bool) $maintenance;
        $generalConfig['maintenance']['code'] = $maintenanceCode;
        if ($generalConfig['maintenance']['enabled']) {
            setcookie('en4_maint_code', $generalConfig['maintenance']['code'], time() + (60 * 60 * 24 * 365), $this->view->baseUrl().'; SameSite=Lax;');
        }

        if ((is_file($global_settings_file) && is_writable($global_settings_file)) ||
            (is_dir(dirname($global_settings_file)) && is_writable(dirname($global_settings_file)))) {
            $file_contents = "<?php defined('_ENGINE') or die('Access Denied'); return ";
            $file_contents .= var_export($generalConfig, true);
            $file_contents .= "; ?>";
            file_put_contents($global_settings_file, $file_contents);
            $form->addNotice('Your changes have been saved.');
        } else {
            return $form->getElement('maintenance_mode')
                ->addError('Unable to configure this setting due to the file /application/settings/general.php not having the correct permissions.
                       Please CHMOD (change the permissions of) that file to 666, then try again.');
        }
    }

    public function localeAction()
    {
        $this->view->form = $form = new Core_Form_Admin_Settings_Locale();

        // Save
        if ($this->getRequest()->isPost()) {
            if ($form->isValid($this->getRequest()->getPost())) {
                Engine_Api::_()->getApi('settings', 'core')->core_locale = $form->getValues();
                $form->addNotice('Your changes have been saved.');
            }
        }

        // Initialize
        else {
            $form->populate(Engine_Api::_()->getApi('settings', 'core')->core_locale);
        }
    }

    public function spamAction()
    {
        // Get navigation
        $this->view->navigation = Engine_Api::_()->getApi('menus', 'core')
            ->getNavigation('core_admin_banning', array(), 'core_admin_banning_general');

        // Get form
        $this->view->form = $form = new Core_Form_Admin_Settings_Spam();

        // Get db
        $db = Engine_Db_Table::getDefaultAdapter();

        // Populate some settings
        $settings = Engine_Api::_()->getApi('settings', 'core');
        $config = (array) $settings->core_spam;
        $config['recaptcha_version'] = $settings->core_spam_recaptcha_version;

        // Load all IPs
        $bannedIpsTable = Engine_Api::_()->getDbtable('BannedIps', 'core');
        $bannedIps = array();
        foreach ($bannedIpsTable->getAddresses() as $bannedIp) {
            if (is_array($bannedIp)) {
                $bannedIps[] = join(' - ', $bannedIp);
            } elseif (is_string($bannedIp)) {
                $bannedIps[] = $bannedIp;
            }
        }
        $config['bannedips'] = join("\n", $bannedIps);

        // Load all emails
        $bannedEmailsTable = Engine_Api::_()->getDbtable('BannedEmails', 'core');
        $bannedEmails = $bannedEmailsTable->getEmails();
        $config['bannedemails'] = join("\n", $bannedEmails);

        // Load all usernames
        $bannedUsernamesTable = Engine_Api::_()->getDbtable('BannedUsernames', 'core');
        $bannedUsernames = $bannedUsernamesTable->getUsernames();
        $config['bannedusernames'] = join("\n", $bannedUsernames);

        // Load all words
        $bannedWordsTable = Engine_Api::_()->getDbtable('BannedWords', 'core');
        $bannedWords = $bannedWordsTable->getWords();
        $config['bannedwords'] = join("\n", $bannedWords);

        // Populate
        if (_ENGINE_ADMIN_NEUTER) {
            $config['recaptchapublic'] = '**********';
            $config['recaptchaprivate'] = '**********';
            $config['recaptchapublicv3'] = '**********';
            $config['recaptchaprivatev3'] = '**********';
        }

        $form->populate($config);

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }


        // Process
        $db = Engine_Api::_()->getDbtable('settings', 'core')->getAdapter();
        $db->beginTransaction();

        $values = $form->getValues();

        // Build banned IPs
        $bannedIpsNew = preg_split('/\s*[,\n]+\s*/', $values['bannedips']);
        foreach ($bannedIpsNew as &$bannedIpNew) {
            if (false !== strpos($bannedIpNew, '-')) {
                $bannedIpNew = preg_split('/\s*-\s*/', $bannedIpNew, 2);
            } elseif (false != strpos($bannedIpNew, '*')) {
                $tmp = $bannedIpNew;
                if (false != strpos($tmp, ':')) {
                    $bannedIpNew = array(
                        str_replace('*', '0', $tmp),
                        str_replace('*', 'ffff', $tmp),
                    );
                } else {
                    $bannedIpNew = array(
                        str_replace('*', '0', $tmp),
                        str_replace('*', '255', $tmp),
                    );
                }
            }
        }

        // Check if they are banning their own address
        if ($bannedIpsTable->isAddressBanned(Engine_IP::getRealRemoteAddress(),
            $bannedIpsTable->normalizeAddressArray($bannedIpsNew))) {
            return $form->addError('One of the IP addresses or IP address ranges you entered contains your own IP address.');
        }

        if (!empty($values['recaptchapublic']) &&
            !empty($values['recaptchaprivate'])) {
            $recaptcha = new Zend_Service_ReCaptcha($values['recaptchapublic'],
                $values['recaptchaprivate']);
            try {
                $resp = $recaptcha->verify('test', 'test');
//        if( false === stripos($resp, 'error') ) {
//          return $form->addError('ReCaptcha Key Invalid: ' . $resp);
//        }
                if (in_array($err = $resp->getErrorCode(), array('invalid-site-private-key', 'invalid-site-public-key'))) {
                    return $form->addError('ReCaptcha Error: ' . $err);
                }
                // Validate public key
                $httpClient = new Zend_Http_Client();
                $httpClient->setUri('http://www.google.com/recaptcha/api/challenge');
                $httpClient->setParameterGet('k', $values['recaptchapublic']);
                $resp = $httpClient->request('GET');
                if (false !== stripos($resp->getBody(), 'Input error')) {
                    return $form->addError('ReCaptcha Error: ' . str_replace(array("document.write('", "\\n');"), array('', ''), $resp->getBody()));
                }
            } catch (Exception $e) {
                return $form->addError('ReCaptcha Key Invalid: ' . $e->getMessage());
            }

            $values['recaptchaenabled'] = true;
        } else {
            $values['recaptchaenabled'] = false;
        }

        try {

            // Save Banned IPs
            $bannedIpsTable->setAddresses($bannedIpsNew);
            unset($values['bannedips']);

            // Save Banned Emails
            $bannedEmailsNew = preg_split('/\s*[,\n]+\s*/', $values['bannedemails']);
            $bannedEmailsTable->setEmails($bannedEmailsNew);
            unset($values['bannedemails']);

            // Save Banned Usernames
            $bannedUsernamesNew = preg_split('/\s*[,\n]+\s*/', $values['bannedusernames']);
            $bannedUsernamesTable->setUsernames($bannedUsernamesNew);
            unset($values['bannedusernames']);

            // Save Banned Words
            $bannedWordsNew = preg_split('/\s*[,\n]+\s*/', $values['bannedwords']);
            $bannedWordsTable->setWords($bannedWordsNew);
            unset($values['bannedwords']);
            // caching
            $cache = Zend_Registry::get('Zend_Cache');
            if ($cache instanceof Zend_Cache_Core) {
                $cache->remove('bannedwords');
            }
            
            $values['recaptcha_version'] = isset($values['recaptcha_version']) ? $values['recaptcha_version'] : 1;
            // Save other settings
            $settings->core_spam = $values;


            $db->commit();
            $form->addNotice('Your changes have been saved.');
        } catch (Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    public function performanceAction()
    {
        $settingFile = APPLICATION_PATH . '/application/settings/cache.php';
        $defaultFilePath = APPLICATION_PATH . '/temporary/cache';

        if (file_exists($settingFile)) {
            $currentCache = include $settingFile;
        } else {
            $currentCache = array(
                'default_backend' => 'File',
                'frontend' => array(
                    'core' => array(
                        'automatic_serialization' => true,
                        'cache_id_prefix' => 'Engine4_',
                        'lifetime' => '300',
                        'caching' => true,
                        'gzip' => 1,
                    ),
                ),
                'backend' => array(
                    'File' => array(
                        'cache_dir' => APPLICATION_PATH . '/temporary/cache',
                    ),
                ),
            );
        }
        $currentCache['default_file_path'] = $defaultFilePath;
        $this->view->form = $form = new Core_Form_Admin_Settings_Performance();

        // pre-fill form with proper cache type
        $form->populate($currentCache);

        // disable caching types not supported
        $disabledTypeOptions = array();
        foreach (array_keys($form->getElement('type')->options) as $backend) {
            $disabledExtension = '';

            if ('Memcached' == $backend && !extension_loaded('memcached')) {
                $disabledTypeOptions[] = $backend;
            }
            
            if ('Engine_Cache_Backend_Redis' == $backend && !extension_loaded('redis')) {
                $disabledTypeOptions[] = $backend;
            }

            if (in_array($backend, $disabledTypeOptions)) {
                $disabledExtension = 'Memcached' == $backend ? 'memcached': strtolower($backend);
                $form->getElement('type')->options[$backend] .= vsprintf(' - <code>%1$s</code> extension is not loaded', array($disabledExtension));
            }
        }

        $form->getElement('type')->setAttrib('disable', $disabledTypeOptions);
        $customBackendNaming = false;
        // set required elements before checking for validity
        switch ($this->getRequest()->getPost('type')) {
            case 'File':
                $form->getElement('file_path')->setRequired(true)->setAllowEmpty(false);
                break;
            case 'Memcached':
                $form->getElement('memcache_host')->setRequired(true)->setAllowEmpty(false);
                $form->getElement('memcache_port')->setRequired(true)->setAllowEmpty(false);
                break;
            case 'Engine_Cache_Backend_Redis':
                $customBackendNaming = true;
                $form->getElement('redis_host')->setRequired(true)->setAllowEmpty(false);
                $form->getElement('redis_port')->setRequired(true)->setAllowEmpty(false);
                break;
        }

        if (is_writable($settingFile) || (is_writable(dirname($settingFile)) && !file_exists($settingFile))) {
            // do nothing
        } else {
            $phrase = Zend_Registry::get('Zend_Translate')->_('Changes made to this form will not be saved.  Please adjust the permissions (CHMOD) of file %s to 777 and try again.');
            $form->addError(sprintf($phrase, '/application/settings/cache.php'));
            return;
        }

        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $this->view->isPost = true;
            $code = "<?php\ndefined('_ENGINE') or die('Access Denied');\nreturn ";

            $doFlush = false;
            foreach ($form->getElement('type')->options as $type => $label) {
                if (array_key_exists($type, $currentCache['backend']) && $type != $this->_getParam('type')) {
                    $doFlush = true;
                }
            }

            $options = array();
            switch ($this->getRequest()->getPost('type')) {
                case 'File':
                    $options['file_locking'] = (bool) $this->_getParam('file_locking');
                    $options['cache_dir'] = $this->_getParam('file_path');
                    if (!is_writable($options['cache_dir'])) {
                        $options['cache_dir'] = $defaultFilePath;
                        $form->getElement('file_path')->setValue($defaultFilePath);
                    }
                    break;
                case 'Memcached':
                    $options['servers'][] = array(
                        'host' => $this->_getParam('memcache_host'),
                        'port' => (int) $this->_getParam('memcache_port'),
                    );
                    $options['compression'] = (bool) $this->_getParam('memcache_compression');
                    break;
                case 'Engine_Cache_Backend_Redis':
                    $options['servers'][] = array(
                        'host' => $this->_getParam('redis_host'),
                        'port' => (int) $this->_getParam('redis_port'),
                        'password' => $this->_getParam('redis_password')
                    );
            }
            $currentCache['backend'] = array($this->_getParam('type') => $options);
            $currentCache['frontend']['core']['lifetime'] = $this->_getParam('lifetime');
            $currentCache['frontend']['core']['caching'] = (bool) $this->_getParam('enable');
            $currentCache['frontend']['core']['gzip'] = (bool) $this->_getParam('gzip_html');

            $code .= var_export($currentCache, true);
            $code .= '; ?>';

            // test write+read before saving to file
            $backend = null;
            if (!$currentCache['frontend']['core']['caching']) {
                $this->view->success = true;
            } else {
                $backend = Zend_Cache::_makeBackend($this->_getParam('type'), $options, $customBackendNaming);
                $backendSuccess = false;
                if ($currentCache['frontend']['core']['caching']) {
                    @$backend->save('test_value', 'test_id');
                    if (@$backend->test('test_id')) {
                        $backendSuccess = true;
                        $this->view->success = true;
                    }
                }

                if (!$backendSuccess) {
                    $this->view->success = false;
                    $form->getElement('type')->setErrors(array('Unable to use this backend. Please check your settings or try another one.'));
                }
            }

            // write settings to file
            if ($this->view->success && file_put_contents($settingFile, $code)) {
                $form->addNotice('Your changes have been saved.');
            } elseif ($this->view->success) {
                $form->addError('Your settings were unable to be saved to the
          cache file.  Please log in through FTP and either CHMOD 777 the file
          <em>/application/settings/cache.php</em>, or edit that file and
          replace the existing code with the following:<br/>
          <code>' . htmlspecialchars($code) . '</code>');
            }

            if ($backend instanceof Zend_Cache_Backend && ($doFlush || $form->getElement('flush')->getValue())) {
                //Public temporary folder files delete
                $files = glob(APPLICATION_PATH . DIRECTORY_SEPARATOR. 'public'.DIRECTORY_SEPARATOR.'temporary'.DIRECTORY_SEPARATOR.'*');
                foreach($files as $file) {
                  $extension = ltrim(strrchr($file, '.'), '.');
                  if(is_file($file) && $extension != 'html')
                    unlink($file);
                }

                $backend->clean();
                $form->getElement('flush')->setValue(0);
                $form->addNotice('Cache has been flushed.');
            }
        }

        /* CHANGE TRANSLATION METHOD TO ARRAY */
        $db = Engine_Db_Table::getDefaultAdapter();
        if ($form->getElement('translate_array')->getValue() == 1 && $this->getRequest()->isPost()) {
            // Check For Array Files
            $languagePath = APPLICATION_PATH.'/application/languages';
            // Get List of Folders
            $languageFolders = array_filter(glob($languagePath . DIRECTORY_SEPARATOR . '*'), 'is_dir');

            // Look inside Folders
            foreach ($languageFolders as $folder) {
                // Get Locale code
                $locale_code = str_replace($languagePath . DIRECTORY_SEPARATOR, "", $folder);
                // If Array files do not exist, Create Them and check syntax
                $array_check = $this->csv_folder_to_array($folder, $locale_code);

                if (!$array_check) {
                    $db->update('engine4_core_settings',
                        array( 'value' => 'csv' ),
                        array( 'name = ?' => 'core.translate.adapter' )
                    );
                    $form->getElement('translate_array')->setValue(0);
                    $form->addError('Language packs could not be converted to PHP Arrays.');
                    return;
                }
            }

            // Update DB Parameter to Array
            $db->update('engine4_core_settings',
                array( 'value' => 'array' ),
                array( 'name = ?' => 'core.translate.adapter' )
            );
            $form->addNotice('Language Packs successfully converted to PHP Arrays.');
        } elseif ($this->getRequest()->isPost()) {
            // Switch DB Parameter back to CSV
            $db->update('engine4_core_settings',
                array( 'value' => 'csv' ),
                array( 'name = ?' => 'core.translate.adapter' )
            );
        }
        /* END OF TRANSLATION CHANGES */

        //Set Error Log size and automatically delete
        if($form->getElement('empty_log')->getValue() == 1 && $this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('settings', 'core')->setSetting('core.empty.log', 1);
            Engine_Api::_()->getApi('settings', 'core')->setSetting('core.logfile.size', $form->getElement('logfile_size')->getValue());
        } else if($this->getRequest()->isPost()) {
            Engine_Api::_()->getApi('settings', 'core')->setSetting('core.empty.log', 0);
            Engine_Api::_()->getApi('settings', 'core')->setSetting('core.logfile.size', $form->getElement('logfile_size')->getValue());
        }
    }

    public function passwordAction()
    {
        // Super admins only?
        $viewer = Engine_Api::_()->user()->getViewer();
        $level = Engine_Api::_()->getItem('authorization_level', $viewer->level_id);
        if (!$viewer || !$level || $level->flag != 'superadmin') {
            return $this->_helper->redirector->gotoRoute(array(), 'admin_default', true);
        }

        $this->view->form = $form = new Core_Form_Admin_Settings_Password();

        if (!$this->getRequest()->isPost()) {
            $form->populate(array(
                'mode' => Engine_Api::_()->getApi('settings', 'core')->getSetting('core.admin.mode', 'none'),
                'timeout' => Engine_Api::_()->getApi('settings', 'core')->getSetting('core.admin.timeout'),
            ));
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        $values = $form->getValues();
        $values['reauthenticate'] = ($values['mode'] == 'none' ? '0' : '1');

        // If auth method is global and password is empty (in db), require them to enter one
        if ($values['mode'] == 'global') {
            if (!Engine_Api::_()->getApi('settings', 'core')->core_admin_password && empty($values['password'])) {
                $form->addError('Please choose a password.');
                return;
            }
        }

        // Verify password
        if (!empty($values['password'])) {
            if ($values['password'] != $values['password_confirm']) {
                $form->addError('Passwords did not match.');
                return;
            }
            if (strlen($values['password']) < 4) {
                $form->addError('Password must be at least four (4) characters.');
                return;
            }
            // Hash password
            $values['password'] = md5(Engine_Api::_()->getApi('settings', 'core')->getSetting('core.secret', 'staticSalt') . $values['password']);
            unset($values['password_confirm']);

            $form->addNotice('Password updated.');
        } else {
            unset($values['password']);
            unset($values['password_confirm']);
        }

        Engine_Api::_()->getApi('settings', 'core')->core_admin = $values;

        $form->addNotice('Your changes have been saved.');
    }

    public function viglinkAction()
    {
        $this->view->form = $form = new Core_Form_Admin_Settings_Viglink();
        $form->populate((array) Engine_Api::_()->getDbtable('settings', 'core')->core_viglink);

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        // Get values
        $values = $form->getValues();

        // Save
        Engine_Api::_()->getDbtable('settings', 'core')->core_viglink = $values;

        // Regenerate form >.>
        $this->view->form = $form = new Core_Form_Admin_Settings_Viglink();
        $form->populate($values);
        $form->addNotice('Your changes have been saved.');
    }

    public function wibiyaAction()
    {
        $this->view->form = $form = new Core_Form_Admin_Settings_Wibiya();
        $form->populate((array) Engine_Api::_()->getDbtable('settings', 'core')->core_wibiya);

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        // Get values
        $values_raw = $form->getValues();
        $values = array_map('trim', $values_raw);

        // Save
        Engine_Api::_()->getDbtable('settings', 'core')->core_wibiya = $values;

        // Regenerate form >.>
        $this->view->form = $form = new Core_Form_Admin_Settings_Wibiya();
        $form->populate($values);
        $form->addNotice('Your changes have been saved.');
    }

    protected function _createRandomPassword($length = 6)
    {
        $chars = "abcdefghijkmnpqrstuvwxyz23456789";
        $charsLen = strlen($chars);
        $pass = '';
        for ($i = 0; $i < $length; $i++) {
            $pass .= substr($chars, mt_rand(0, $charsLen - 1), 1);
        }
        return $pass;
    }
}
