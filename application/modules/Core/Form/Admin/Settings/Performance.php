<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Performance.php 9820 2012-11-15 05:03:31Z matthew $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Form_Admin_Settings_Performance extends Engine_Form
{
    public function init()
    {
        $description = $this->getTranslator()->translate(
            'For very large social networks, it may be necessary to enable caching to improve performance. If there is a noticeable decrease in performance on your social network, consider enabling caching below (or upgrading your hardware). Caching takes some load off the database server by storing commonly retrieved data in temporary files (file-based caching) or memcached (memory-based caching). If you are not familiar with caching, we do not recommend that you change any of these settings. <br>');

        $settings = Engine_Api::_()->getApi('settings', 'core');

        if ($settings->getSetting('user.support.links', 0) == 1) {
            $moreinfo = $this->getTranslator()->translate(
                'More Info: <a href="%1$s" target="_blank"> KB Article</a>');
        } else {
            $moreinfo = $this->getTranslator()->translate(
                '');
        }

        $description = vsprintf($description.$moreinfo, array(
            'https://socialengine.atlassian.net/wiki/spaces/SU/pages/5243215/se-php-performance-&-caching',
        ));

        // Decorators
        $this->loadDefaultDecorators();
        $this->getDecorator('Description')->setOption('escape', false);

        $this
            ->setTitle('Email All Members')
            ->setDescription($description);

        // Set form attributes
        $this->setTitle('Performance & Caching');
        $this->setDescription($description);

        // disable form if not in production mode
        $attribs = array();
        if (APPLICATION_ENV != 'production') {
            $attribs = array('disabled' => 'disabled', 'readonly' => 'readonly');
            $this->addError('Note: Caching is disabled when your site is in development mode. Your site must be in production mode to modify the settings below.');
        }

        $this->addElement('Radio', 'enable', array(
            'label' => 'Use Cache?',
            'description' => strtoupper(get_class($this) . '_enable_description'),
            'required' => true,
            'multiOptions' => array(
                1 => 'Yes, enable caching.',
                0 => 'No, do not enable caching.',
            ),
            'attribs' => $attribs,
        ));

        $this->addElement('Text', 'lifetime', array(
            'label' => 'Cache Lifetime',
            'description' => strtoupper(get_class($this) . '_lifetime_description'),
            'size' => 5,
            'maxlength' => 4,
            'required' => true,
            'allowEmpty' => false,
            'validators' => array(
                array('NotEmpty', true),
                array('Int'),
            ),
            'attribs' => $attribs,
        ));

        $typeDescription = $this->getTranslator()->translate(strtoupper(get_class($this) . '_type_description'));
        $typeDescription .= vsprintf(' See <a href="%1$s" target="_blank"> KB Article</a> and contact your hosting provider for assistance configuring memory-based caching.', array(
            'https://socialengine.atlassian.net/wiki/spaces/SU/pages/5243215/se-php-performance-&-caching',
        ));

        $cacheOptions = array(
            'File'      => 'File-based',
            'Memcached' => 'Memcached',
            'Engine_Cache_Backend_Redis' => 'Redis'
        );

        $this->addElement('Radio', 'type', array(
            'label' => 'Caching Feature',
            'description' => $typeDescription,
            'required' => true,
            'allowEmpty' => false,
            'multiOptions' => $cacheOptions,
            'onclick' => 'updateFields();',
            'attribs' => $attribs,
        ));
        $this->type->getDecorator('Description')->setOption('escape', false);
        $this->type->setAttrib('escape', false);

        $this->addElement('Text', 'file_path', array(
            'label' => 'File-based Cache Directory',
            'description' => strtoupper(get_class($this) . '_file_path_description'),
            'attribs' => $attribs,
        ));

        $this->addElement('Checkbox', 'file_locking', array(
            'label' => 'File locking?',
            'attribs' => $attribs,
        ));

        $this->addElement('Text', 'memcache_host', array(
            'label' => 'Memcached Host',
            'description' => 'Can be a domain name, hostname, or an IP address (recommended)',
            'attribs' => $attribs,
        ));

        $this->addElement('Text', 'memcache_port', array(
            'label' => 'Memcached Port',
            'attribs' => $attribs,
        ));

        $this->addElement('Checkbox', 'memcache_compression', array(
            'label' => 'Memcached compression?',
            'title' => 'Title?',
            'description' => 'Compression will decrease the amount of memory used, however will increase processor usage.',
            'attribs' => $attribs,
        ));

        $this->addElement('Text', 'redis_host', array(
            'label' => 'Redis Host',
            'description' => 'Can be a domain name, hostname, or an IP address (recommended)',
            'attribs' => $attribs,
        ));

        $this->addElement('Text', 'redis_port', array(
            'label' => 'Redis Port',
            'attribs' => $attribs,
        ));

        $this->addElement('Text', 'redis_password', array(
            'label' => 'Redis Password (optional)',
            'attribs' => $attribs,
        ));

        $this->addElement('Checkbox', 'flush', array(
            'label' => 'Flush cache?',
            'attribs' => $attribs,
        ));

        $this->addElement('Radio', 'empty_log', array(
            'label' => 'Empty Log Files Automatically?',
            'description' => "Do you want to empty the log files automatically from your server after the log files have reached a specified size? If you choose Yes, then from the below setting you can also choose the size after which log files will be cleared. Note: The log file will be cleared automatically on the entry of new log error after exceeding the specified size.",
            'required' => true,
            'multiOptions' => array(
                1 => 'Yes',
                0 => 'No',
            ),
            'onchange' => 'showLogSize(this.value);',
            'value' => 0,
        ));

        $this->addElement('Text', 'logfile_size', array(
            'label' => 'Log Size',
            'description' => "Enter the log size (in MB) after which it will be auto cleared.",
            'value' => 50,
        ));

        $this->addElement('Checkbox', 'translate_array', array(
            'label' => 'Convert Language Pack CSV files to a PHP Array? (Note: If this setting is already enabled then clicking on "Save Changes" button will regenerate PHP array based file based on the latest language phrases in csv files. This is useful for you in cases when you\'ve installed a new plugin or made some new phrase translations via Language Manager.)',
            'description' => 'Translation Performance',
        ));

        $this->addElement('Checkbox', 'gzip_html', array(
            'label' => 'Send HTML with Gzip compression?',
            'description' => 'HTML Compression',
        ));

        // init submit
        $this->addElement('Button', 'submit', array(
            'label' => 'Save Changes',
            'type' => 'submit',
            'ignore' => true,
            'attribs' => $attribs,
        ));
    }

    public function populate(array $currentCache)
    {
        $enabled = true;
        if (isset($currentCache['frontend']['core']['caching'])) {
            $enabled = $currentCache['frontend']['core']['caching'];
        }
        $this->getElement('enable')->setValue($enabled);

        $backend = Engine_Cache::getDefaultBackend();
        if (isset($currentCache['backend'])) {
            $backend = array_keys($currentCache['backend']);
            $backend = $backend[0];
        }
        $this->getElement('type')->setValue($backend);

        $filePath = $currentCache['default_file_path'];
        if (isset($currentCache['backend']['File']['cache_dir'])) {
            $filePath = $currentCache['backend']['File']['cache_dir'];
        }
        $this->getElement('file_path')->setValue($filePath);

        $fileLocking = 1;
        if (isset($currentCache['backend']['File']['file_locking'])) {
            $fileLocking = $currentCache['backend']['File']['file_locking'];
        }
        $this->getElement('file_locking')->setValue($fileLocking);

        if (isset($currentCache['frontend']['core']['lifetime'])) {
            $lifetime = $currentCache['frontend']['core']['lifetime'];
        } else {
            $lifetime = 300; // 5 minutes
        }
        if (isset($currentCache['frontend']['core']['options']['lifetime'])) {
            $lifetime = $currentCache['frontend']['core']['options']['lifetime'];
        }
        $this->getElement('lifetime')->setValue($lifetime);

        $memcacheHost = '127.0.0.1';
        $memcachePort = '11211';
        $memcacheCompression = 0;

        if (isset($currentCache['backend']['Memcached']['servers'][0]['host'])) {
            $memcacheHost = $currentCache['backend']['Memcached']['servers'][0]['host'];
        }

        if (isset($currentCache["backend"]["Memcached"]["servers"][0]["port"])) {
            $memcachePort = $currentCache["backend"]["Memcached"]["servers"][0]["port"];
        }

        if (isset($currentCache["backend"]["Memcached"]["compression"])) {
            $memcacheCompression = $currentCache["backend"]["Memcached"]["compression"];
        }

        $this->getElement('memcache_host')->setValue($memcacheHost);
        $this->getElement('memcache_port')->setValue($memcachePort);
        $this->getElement('memcache_compression')->setValue($memcacheCompression);

        $redisHost = Engine_Cache_Backend_Redis::DEFAULT_HOST;
        $redisPort = Engine_Cache_Backend_Redis::DEFAULT_PORT;
        $redisPassword = Engine_Cache_Backend_Redis::DEFAULT_PASSWORD;
        $redisBackend = 'Engine_Cache_Backend_Redis';
        if (isset($currentCache['backend'][$redisBackend]['servers'][0]['host'])) {
            $redisHost = $currentCache['backend'][$redisBackend]['servers'][0]['host'];
        }
        if (isset($currentCache["backend"][$redisBackend]["servers"][0]["port"])) {
            $redisPort = $currentCache["backend"][$redisBackend]["servers"][0]["port"];
        }
        if (isset($currentCache["backend"][$redisBackend]["servers"][0]["password"])) {
            $redisPassword = $currentCache["backend"][$redisBackend]["servers"][0]["password"];
        }
        $this->getElement('redis_host')->setValue($redisHost);
        $this->getElement('redis_port')->setValue($redisPort);
        $this->getElement('redis_password')->setValue($redisPassword);

        // Set Existing Value for Translation Performance checkbox
        $db = Engine_Db_Table::getDefaultAdapter();
        $initialTranslateAdapter = $db->select()
            ->from('engine4_core_settings', 'value')
            ->where('`name` = ?', 'core.translate.adapter')
            ->query()
            ->fetchColumn();

        if ($initialTranslateAdapter === false) {
            $db->insert('engine4_core_settings', array(
                'name' => 'core.translate.adapter',
                'value' => 'csv'
            ));
        }

        $translateArray = (int) ($initialTranslateAdapter == 'array');
        $this->getElement('translate_array')->setValue($translateArray);

        //Set Error Log size and automatically delete
        $empty_log = $db->select()
            ->from('engine4_core_settings', 'value')
            ->where('`name` = ?', 'core.empty.log')
            ->query()
            ->fetchColumn();

        $logfile_size = $db->select()
            ->from('engine4_core_settings', 'value')
            ->where('`name` = ?', 'core.logfile.size')
            ->query()
            ->fetchColumn();

        $this->getElement('empty_log')->setValue($empty_log);
        $this->getElement('logfile_size')->setValue($logfile_size);

        // Set Value for HTML Compression
        $gzip = false;
        if (isset($currentCache['frontend']['core']['gzip'])) {
            $gzip = $currentCache['frontend']['core']['gzip'];
        }

        $this->getElement('gzip_html')->setValue($gzip);
    }
}
