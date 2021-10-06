<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Activity.php 9799 2016-10-26 22:11:00Z matthew $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Activity_View_Helper_ActivityFormToken extends Zend_View_Helper_Abstract
{

  protected $_session;
  protected $_timeout = 3600;

  public function activityFormToken()
  {
    $this->_session = new Zend_Session_Namespace('ActivityFormToken');
    return $this;
  }

  public function createToken()
  {
    $this->_session->setExpirationSeconds($this->_timeout);

    $salt = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.secret', 'staticSalt');
    $this->_session->tokens[] = $token = md5(time() . mt_rand(1, 1000000) . $salt . get_class($this) . mt_rand(1, 1000000));
    return $token;
  }

  public function getTokens()
  {
    if( !empty($this->_session->tokens) ) {
      return $this->_session->tokens;
    }
  }

  /**
   * @deprecated
   */
  public function getToken()
  {
    if( !empty($this->_session->tokens) ) {
      return reset($this->_session->tokens);
    }
  }

  public function unsetToken($token)
  {
    $key = array_search($token, $this->_session->tokens);
    if( $key !== false ) {
      unset($this->_session->tokens[$key]);
      return $this;
    }
  }
}
