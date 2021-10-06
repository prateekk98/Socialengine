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
class Core_Api_Core extends Core_Api_Abstract
{
    /**
     * @var Core_Model_Item_Abstract|mixed The object that represents the subject of the page
     */
    protected $_subject;

    /**
     * Set the object that represents the subject of the page
     *
     * @param Core_Model_Item_Abstract|mixed $subject
     * @return Core_Api_Core
     */
    public function setSubject($subject)
    {
        if( null !== $this->_subject ) {
            throw new Core_Model_Exception("The subject may not be set twice");
        }

        if( !($subject instanceof Core_Model_Item_Abstract) ) {
            throw new Core_Model_Exception("The subject must be an instance of Core_Model_Item_Abstract");
        }

        $this->_subject = $subject;
        return $this;
    }

    /**
     * Get the previously set subject of the page
     *
     * @return Core_Model_Item_Abstract|null
     */
    public function getSubject($type = null)
    {
        if( null === $this->_subject ) {
            throw new Core_Model_Exception("getSubject was called without first setting a subject.  Use hasSubject to check");
        } else if( is_string($type) && $type !== $this->_subject->getType() ) {
            throw new Core_Model_Exception("getSubject was given a type other than the set subject");
        } else if( is_array($type) && !in_array($this->_subject->getType(), $type) ) {
            throw new Core_Model_Exception("getSubject was given a type other than the set subject");
        }

        return $this->_subject;
    }

    /**
     * Checks if a subject has been set
     *
     * @return bool
     */
    public function hasSubject($type = null)
    {
        if( null === $this->_subject ) {
            return false;
        } else if( null === $type ) {
            return true;
        } else {
            return ( $type === $this->_subject->getType() );
        }
    }

    public function clearSubject()
    {
        $this->_subject = null;
        return $this;
    }

    public function getCaptchaOptions(array $params = array())
    {
        $spamSettings = Engine_Api::_()->getApi('settings', 'core')->core_spam;
        $recaptchaVersionSettings = Engine_Api::_()->getApi('settings', 'core')->core_spam_recaptcha_version;
        
        if(empty($spamSettings['recaptchapublic']) && empty($spamSettings['recaptchaprivate']) && empty($spamSettings['recaptchapublicv3']) && empty($spamSettings['recaptchaprivatev3'])) {
            // Image captcha
            return array_merge(array(
                'label' => 'Human Verification',
                'description' => 'Please type the characters you see in the image.',
                'captcha' => 'image',
                'required' => true,
                'captchaOptions' => array(
                    'wordLen' => 6,
                    'fontSize' => '30',
                    'timeout' => 300,
                    'imgDir' => APPLICATION_PATH . '/public/temporary/',
                    'imgUrl' => Zend_Registry::get('Zend_View')->baseUrl() . '/public/temporary',
                    'font' => APPLICATION_PATH . '/application/modules/Core/externals/fonts/arial.ttf',
                ),
            ), $params);
        } else if($recaptchaVersionSettings == 1 && $spamSettings['recaptchaprivate'] && $spamSettings['recaptchapublic']) {
            // Recaptcha v2
            return array_merge(array(
                'label' => 'Human Verification',
                'captcha' => 'ReCaptcha2',
                'required' => true,
                'captchaOptions' => array(
                    'privkey' => $spamSettings['recaptchaprivate'],
                    'pubkey' => $spamSettings['recaptchapublic'],
                    'theme' => 'light',
                    'size' => (isset($params['size']) ? $params['size'] : 'normal' ),
                    'lang' => Zend_Registry::get('Locale')->getLanguage(),
                    'tabindex' => (isset($params['tabindex']) ? $params['tabindex'] : null ),
                    'ssl' => constant('_ENGINE_SSL'),   // Fixed Captcha does not work well when ssl is enabled on website
                    //'onload' => 'en4CoreReCaptcha',
                    'render' => 'explicit',
                    //'loadJs' => 'en4.core.reCaptcha.loadJs'
                ),
            ), $params);
        } else if($recaptchaVersionSettings == 0  && $spamSettings['recaptchaprivatev3'] && $spamSettings['recaptchapublicv3']) {
            $script = "scriptJquery(document).ready(function() {
            scriptJquery('#captcha-wrapper').hide();
              scriptJquery('<input>').attr({ 
                  name: 'recaptcha_response', 
                  id: 'recaptchaResponse', 
                  type: 'hidden', 
              }).appendTo('.global_form'); 
            });";
            $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
            $view->headScript()->appendScript($script);
            
            // Recaptcha v3
            return array_merge(array(
                //'label' => 'Human Verification',
                'captcha' => 'ReCaptcha3',
                'required' => true,
                'captchaOptions' => array(
                    'privkey' => $spamSettings['recaptchaprivatev3'],
                    'pubkey' => $spamSettings['recaptchapublicv3'],
                    //'theme' => 'light',
                    //'size' => (isset($params['size']) ? $params['size'] : 'normal' ),
                    //'lang' => Zend_Registry::get('Locale')->getLanguage(),
                    //'tabindex' => (isset($params['tabindex']) ? $params['tabindex'] : null ),
                    'ssl' => constant('_ENGINE_SSL'),   // Fixed Captcha does not work well when ssl is enabled on website
                    //'onload' => 'en4CoreReCaptchaV3',
                    //'render' => 'explicit',
                    //'loadJs' => 'en4.core.reCaptcha.loadJs'
                ),
            ), $params);
        }
    }

    public function smileyToEmoticons($string = null)
    {
        $emoticonsTag = Engine_Api::_()->activity()->getEmoticons(true);
        if (empty($emoticonsTag)) {
            return $string;
        }

        $string = str_replace("&lt;:o)", "<:o)", $string);
        $string = str_replace("(&amp;)", "(&)", $string);

        return strtr($string, $emoticonsTag);
    }
    public  function floodCheckMessage($data = array(),$view){
        if(count($data)){
            $duration = $data[0];
            $type = $data[1];
            //$time = $duration.' '.($duration == 1 ? $type : $type."s");
            $time =  "1 ".$type;
            return $view->translate('You have reached maximum limit of posting in %s. Try again after this duration expires.',$time);
        }
        return "";
    }
    public function clearLogs() {

        $logfileSize = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.logfile.size', 50);

        if(file_exists(APPLICATION_PATH . '/temporary/log/main.log')) {
            $mainLogSize = filesize(APPLICATION_PATH . '/temporary/log/main.log');
            $mainLogSize = number_format($mainLogSize / 1048576, 2);
            if($logfileSize < $mainLogSize) {
                file_put_contents(APPLICATION_PATH . '/temporary/log/main.log', '');
            }
        }

        if(file_exists(APPLICATION_PATH . '/temporary/log/warnings.log')) {
            $warningLogSize = filesize(APPLICATION_PATH . '/temporary/log/warnings.log');
            $warningLogSize = number_format($warningLogSize / 1048576, 2);
            if($logfileSize < $warningLogSize) {
                file_put_contents(APPLICATION_PATH . '/temporary/log/warnings.log', '');
            }
        }
    }
    public function getFileUrl($image) {
        $table = Engine_Api::_()->getDbTable('files', 'core');
        $result = $table->select()
                    ->from($table->info('name'), 'storage_file_id')
                    ->where('storage_path =?', $image)
                    ->query()
                    ->fetchColumn();
        if(!empty($result)) {
          $storage = Engine_Api::_()->getItem('storage_file', $result);
          return $storage->map();
        } else {
          return $image;
        }
    }
    public function isMobile()
    {
        // No UA defined?
        if( !isset($_SERVER['HTTP_USER_AGENT']) ) {
          return false;
        }

        // Windows is (generally) not a mobile OS
        if( false !== stripos($_SERVER['HTTP_USER_AGENT'], 'windows') &&
            false === stripos($_SERVER['HTTP_USER_AGENT'], 'windows phone os')) {
          return false;
        }

        // Sends a WAP profile header
        if( isset($_SERVER['HTTP_PROFILE']) ||
            isset($_SERVER['HTTP_X_WAP_PROFILE']) ) {
          return true;
        }

        // Accepts WAP as a valid type
        if( isset($_SERVER['HTTP_ACCEPT']) &&
            false !== stripos($_SERVER['HTTP_ACCEPT'], 'application/vnd.wap.xhtml+xml') ) {
          return true;
        }

        // Is Opera Mini
        if( isset($_SERVER['ALL_HTTP']) &&
            false !== stripos($_SERVER['ALL_HTTP'], 'OperaMini') ) {
          return true;
        }

        if( preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android)/i', $_SERVER['HTTP_USER_AGENT']) ) {
          return true;
        }

        $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
        $mobile_agents = array(
          'w3c ', 'acs-', 'alav', 'alca', 'amoi', 'audi', 'avan', 'benq', 'bird',
          'blac', 'blaz', 'brew', 'cell', 'cldc', 'cmd-', 'dang', 'doco', 'eric',
          'hipt', 'inno', 'ipaq', 'java', 'jigs', 'kddi', 'keji', 'leno', 'lg-c',
          'lg-d', 'lg-g', 'lge-', 'maui', 'maxo', 'midp', 'mits', 'mmef', 'mobi',
          'mot-', 'moto', 'mwbp', 'nec-', 'newt', 'noki', 'oper', 'palm', 'pana',
          'pant', 'phil', 'play', 'port', 'prox', 'qwap', 'sage', 'sams', 'sany',
          'sch-', 'sec-', 'send', 'seri', 'sgh-', 'shar', 'sie-', 'siem', 'smal',
          'smar', 'sony', 'sph-', 'symb', 't-mo', 'teli', 'tim-', 'tosh', 'tsm-',
          'upg1', 'upsi', 'vk-v', 'voda', 'wap-', 'wapa', 'wapi', 'wapp', 'wapr',
          'webc', 'winw', 'winw', 'xda ', 'xda-'
        );

        if( in_array($mobile_ua, $mobile_agents) ) {
          return true;
        }
        return false;
    }
}
