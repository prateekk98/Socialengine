<?php

class Engine_Service_ReCaptcha3 extends Zend_Service_Abstract
{
  /**
   * URI to the secure API
   *
   * @var string
   */
  const API_SECURE_SERVER = 'https://www.google.com/recaptcha/api';

  /**
   * URI to the verify server
   *
   * @var string
   */
  const VERIFY_SERVER = 'https://www.google.com/recaptcha/api/siteverify';

  /**
   * Public key used when displaying the captcha
   *
   * @var string
   */
  protected $_publicKey = null;

  /**
   * Private key used when verifying user input
   *
   * @var string
   */
  protected $_privateKey = null;

  /**
   * Ip address used when verifying user input
   *
   * @var string
   */
  protected $_ip = null;

  /**
   * Response from the verify server
   *
   * @var Engine_Service_ReCaptcha3_Response
   */
  protected $_response = null;

  /**
   * Parameters for the script object
   *
   * @var array
   */
  protected $_params = array(
    'onload' => '',
    'render' => 'onload',
    'hl' => 'en',
    'loadJs' => '',
  );

  /**
   * Attributes for div element
   *
   * @var array
   */
  protected $_attributes = array(
    'class' => 'g-recaptcha',
    'theme' => 'light',
    'type' => 'image',
    'size' => 'normal',
    'tabindex' => 0,
    'callback' => null,
    'expired-callback' => null
  );

  /**
   * Class constructor
   *
   * @param string $publicKey
   * @param string $privateKey
   * @param array $params
   * @param array $options
   * @param string $ip
   * @param array|Zend_Config $params
   */
  public function __construct($publicKey = null, $privateKey = null, $params = null, $attributes = null, $ip = null)
  {
    if( $publicKey !== null ) {
      $this->setPublicKey($publicKey);
    }

    if( $privateKey !== null ) {
      $this->setPrivateKey($privateKey);
    }

    if( $ip !== null ) {
      $this->setIp($ip);
    } elseif( isset($_SERVER['REMOTE_ADDR']) ) {
      $this->setIp($_SERVER['REMOTE_ADDR']);
    }

    if( $params !== null ) {
      $this->setParams($params);
    }

    if( $attributes !== null ) {
      $this->setAttributes($attributes);
    }
  }

  /**
   * Serialize as string
   *
   * When the instance is used as a string it will display the recaptcha.
   * Since we can't throw exceptions within this method we will trigger
   * a user warning instead.
   *
   * @return string
   */
  public function __toString()
  {
    try {
      $return = $this->getHtml();
    } catch( Exception $e ) {
      $return = '';
      trigger_error($e->getMessage(), E_USER_WARNING);
    }

    return $return;
  }

  /**
   * Set the ip property
   *
   * @param string $ip
   * @return Engine_Service_ReCaptcha3
   */
  public function setIp($ip)
  {
    $this->_ip = $ip;

    return $this;
  }

  /**
   * Get the ip property
   *
   * @return string
   */
  public function getIp()
  {
    return $this->_ip;
  }

  /**
   * Get the public key
   *
   * @return string
   */
  public function getPublicKey()
  {
    return $this->_publicKey;
  }

  /**
   * Set the public key
   *
   * @param string $publicKey
   * @return Engine_Service_ReCaptcha3
   */
  public function setPublicKey($publicKey)
  {
    $this->_publicKey = $publicKey;

    return $this;
  }

  /**
   * Get the private key
   *
   * @return string
   */
  public function getPrivateKey()
  {
    return $this->_privateKey;
  }

  /**
   * Set the private key
   *
   * @param string $privateKey
   * @return Engine_Service_ReCaptcha3
   */
  public function setPrivateKey($privateKey)
  {
    $this->_privateKey = $privateKey;

    return $this;
  }

  /**
   * Get a single parameter
   *
   * @param string $key
   * @return mixed
   */
  public function getParam($key)
  {
    return $this->_params[$key];
  }

  /**
   * Set a single parameter
   *
   * @param string $key
   * @param string $value
   * @return Engine_Service_ReCaptcha3
   */
  public function setParam($key, $value)
  {
    $this->_params[$key] = $value;

    return $this;
  }

  /**
   * Get the parameter array
   *
   * @return array
   */
  public function getParams()
  {
    return $this->_params;
  }

  /**
   * Set parameters
   *
   * @param array|Zend_Config $params
   * @return Engine_Service_ReCaptcha3
   * @throws Engine_Service_ReCaptcha3_Exception
   */
  public function setParams($params)
  {
    if( $params instanceof Zend_Config ) {
      $params = $params->toArray();
    }

    if( is_array($params) ) {
      foreach( $params as $k => $v ) {
        $this->setParam($k, $v);
      }
    } else {
      throw new Engine_Service_ReCaptcha3_Exception(
      'Expected array or Zend_Config object'
      );
    }

    return $this;
  }

  /**
   * Get a single attribute
   *
   * @param string $key
   * @return mixed
   */
  public function getAttribute($key)
  {
    return $this->_attributes[$key];
  }

  /**
   * Set a single attribute
   *
   * @param string $key
   * @param string $value
   * @return Engine_Service_ReCaptcha3
   */
  public function setAttribute($key, $value)
  {
    $key = strtolower($key);
    if( !array_key_exists($key, $this->_attributes) ) {
      return $this;
    }
    $this->_attributes[$key] = $value;

    return $this;
  }

  /**
   * Get attributes array
   *
   * @return array
   */
  public function getAttributes()
  {
    return $this->_attributes;
  }

  /**
   * Set attributes array
   *
   * @param array|Zend_Config $attributes
   * @return Engine_Service_ReCaptcha3
   * @throws Engine_Service_ReCaptcha3_Exception
   */
  public function setAttributes($attributes)
  {
    if( $attributes instanceof Zend_Config ) {
      $attributes = $attributes->toArray();
    }

    if( is_array($attributes) ) {
      foreach( $attributes as $k => $v ) {
        $this->setAttribute($k, $v);
      }
    } else {
      throw new Engine_Service_ReCaptcha3_Exception(
      'Expected array or Zend_Config object'
      );
    }

    return $this;
  }

  /**
   * Get HTML for script element
   *
   * @return string
   */
  public function getHtmlHead()
  {
    $host = self::API_SECURE_SERVER;

    $publicKey = $this->_publicKey;
    $renderPart = '?render='.$this->_publicKey;

    $return = <<<HTML
<script type="text/javascript"
   src="{$host}.js{$renderPart}">
</script>
<script type="text/javascript">
  grecaptcha.ready(function () {
    grecaptcha.execute('{$publicKey}', { action: 'login' }).then(function (token) {
      var recaptchaResponse = document.getElementById('recaptchaResponse');
      recaptchaResponse.value = token;
      //console.log(token);
    });
  });
</script>
HTML;
    return $return;
  }

  /**
   * Get the HTML code for the captcha
   *
   * This method uses the public key to fetch a recaptcha form.
   * 
   * @return string
   */
  public function getHtml()
  {
    return $this->getHtmlHead();
  }

  /**
   * Post a solution to the verify server
   *
   * @param string $responseField
   * @return Zend_Http_Response
   * @throws Engine_Service_ReCaptcha3_Exception
   */
  protected function _post($responseField)
  {
    if( $this->_privateKey === null ) {
      throw new Engine_Service_ReCaptcha3_Exception('Missing private key');
    }

    if( $this->_ip === null ) {
      throw new Engine_Service_ReCaptcha3_Exception('Missing ip address');
    }

    /* Fetch an instance of the http client */
    $httpClient = self::getHttpClient();
    $httpClient->resetParameters(true);

    $postParams = array('secret' => $this->_privateKey,
      'remoteip' => $this->_ip,
      'response' => $responseField);

    /* Make the POST and return the response */
    return $httpClient->setUri(self::VERIFY_SERVER)
        ->setParameterPost($postParams)
        ->request(Zend_Http_Client::POST);
  }

  /**
   * Verify the user input
   *
   * This method calls up the post method and returns a
   * Engine_Service_ReCaptcha3_Response object.
   *
   * @param string $responseField
   * @return Engine_Service_ReCaptcha3_Response
   */
  public function verify($responseField)
  {
    if( empty($responseField) ) {
      throw new Engine_Service_ReCaptcha3_Exception('Missing response field');
    }
    $response = $this->_post($responseField);

    return new Engine_Service_ReCaptcha3_Response(null, null, $response);
  }
}
