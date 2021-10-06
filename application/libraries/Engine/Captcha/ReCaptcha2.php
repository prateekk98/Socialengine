<?php

class Engine_Captcha_ReCaptcha2 extends Zend_Captcha_Base
{
  
  protected $_submit;
  /**
   * ReCaptcha response field name
   * @var string
   */
  protected $_RESPONSE = 'g-recaptcha-response';

  /**
   * Recaptcha service object
   *
   * @var Engine_Service_ReCaptcha2
   */
  protected $_service;

  /**
   * Parameters defined by the service
   *
   * @var array
   */
  protected $_serviceParams = array();

  /**
   * Options defined by the service
   *
   * @var array
   */
  protected $_serviceAttributes = array();

  /*   * #@+
   * Error codes
   */

  const MISSING_VALUE = 'missingValue';
  const ERR_CAPTCHA = 'errCaptcha';
  const BAD_CAPTCHA = 'badCaptcha';

  /*   * #@- */

  /**
   * Error messages
   * @var array
   */
  protected $_messageTemplates = array(
    self::MISSING_VALUE => 'Missing captcha fields',
    self::ERR_CAPTCHA => 'Failed to validate captcha',
    self::BAD_CAPTCHA => 'Captcha value is wrong: %value%',
  );

  /**
   * Retrieve ReCaptcha Private key
   *
   * @return string
   */
  public function getPrivkey()
  {
    return $this->getService()->getPrivateKey();
  }

  /**
   * Retrieve ReCaptcha Public key
   *
   * @return string
   */
  public function getPubkey()
  {
    return $this->getService()->getPublicKey();
  }

  /**
   * Set ReCaptcha Private key
   *
   * @param string $privkey
   * @return Engine_Captcha_ReCaptcha2
   */
  public function setPrivkey($privkey)
  {
    $this->getService()->setPrivateKey($privkey);
    return $this;
  }

  /**
   * Set ReCaptcha public key
   *
   * @param string $pubkey
   * @return Engine_Captcha_ReCaptcha2
   */
  public function setPubkey($pubkey)
  {
    $this->getService()->setPublicKey($pubkey);
    return $this;
  }

  /**
   * Constructor
   *
   * @param array|Zend_Config $options
   */
  public function __construct($options = null)
  {
    $this->setService(new Engine_Service_ReCaptcha2());
    $this->_serviceParams = $this->getService()->getParams();
    $this->_serviceAttributes = $this->getService()->getAttributes();

    parent::__construct($options);

    if( $options instanceof Zend_Config ) {
      $options = $options->toArray();
    }
    if( !empty($options) ) {
      $this->setOptions($options);
    }
  }

  /**
   * Set service object
   *
   * @param  Engine_Service_ReCaptcha2 $service
   * @return Engine_Captcha_ReCaptcha2
   */
  public function setService(Engine_Service_ReCaptcha2 $service)
  {
    $this->_service = $service;
    return $this;
  }

  /**
   * Retrieve ReCaptcha service object
   *
   * @return Engine_Service_ReCaptcha2
   */
  public function getService()
  {
    return $this->_service;
  }

  /**
   * Set option
   *
   * If option is a service parameter, proxies to the service. The same
   * goes for any service options (distinct from service params)
   *
   * @param  string $key
   * @param  mixed $value
   * @return Zend_Captcha_ReCaptcha
   */
  public function setOption($key, $value)
  {
    $service = $this->getService();
    if( isset($this->_serviceParams[$key]) ) {
      $service->setParam($key, $value);
      return $this;
    }
    if( isset($this->_serviceAttributes[$key]) ) {
      $service->setAttribute($key, $value);
      return $this;
    }
    return parent::setOption($key, $value);
  }

  /**
   * Generate captcha
   *
   * @see Zend_Form_Captcha_Adapter::generate()
   * @return string
   */
  public function generate()
  {
    return "";
  }

  /**
   * Validate captcha
   *
   * @see    Zend_Validate_Interface::isValid()
   * @param  mixed      $value
   * @param  array|null $context
   * @return boolean
   */
  public function isValid($value, $context = null)
  { 
    if($this->_submit != null)
      return $this->_submit;
   
    if( !is_array($_POST) && !is_array($context) ) {
      $this->_error(self::MISSING_VALUE);
       $this->_submit = false;
      return false;
    }

    if( !is_array($_POST) && is_array($context) ) {
      $value = $context;
    }

    if( empty($_POST[$this->_RESPONSE]) ) {
      $this->_error(self::MISSING_VALUE);
       $this->_submit = false;
      return false;
    }

    $service = $this->getService();

    $res = $service->verify($_POST[$this->_RESPONSE]);

    if( !$res ) {
      $this->_error(self::ERR_CAPTCHA);
       $this->_submit = false;
      return false;
    }

    if( !$res->isValid() ) {
      $this->_error(self::BAD_CAPTCHA, $res->getErrorCodes());
      $service->setParam('error', $res->getErrorCodes());
       $this->_submit = false;
      return false;
    }
     $this->_submit = true;
    return true;
  }

  /**
   * Render captcha
   *
   * @param  Zend_View_Interface $view
   * @param  mixed $element
   * @return string
   */
  public function render(Zend_View_Interface $view = null, $element = null)
  {
    $name = null;
    if( $element instanceof Zend_Form_Element ) {
      $name = $element->getBelongsTo();
    }
    return $this->getService()->getHTML($name);
  }

  /**
   * Get captcha decorator
   *
   * @return string
   */
  public function getDecorator()
  {
    return "Captcha_ReCaptcha2";
  }
}
