<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Service_Iframely_Host
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Exception.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Service_Iframely_Host
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Service_Iframely_Host extends Zend_Service_Abstract
{

  /**
   * The Iframely API URI, which also provide meta
   *
   * @var string
   */
  protected $_iframelyApiUrl = '/iframely';

  /**
   * The oembed api URI, which only provide embed
   *
   * @var string
   */
  protected $_oembedApiUrl = '/oembed';

  /**
   * The accept header
   *
   * @var string
   */
  protected $_accept = 'application/json';

  /**
   * The base URL for host iframely
   *
   * @var string
   */
  protected $_baseUrl;

  /**
   * The api key for authentication
   *
   * @var string
   */
  protected $_secretKey = '';

  /**
   * The API URL
   *
   * @var string
   */
  protected $_apiUrl = '';

  /**
   * Ignore the error response
   *
   * @var string
   */
  protected $_ignoreError = true;

  /**
   * The API options
   *
   * @var array
   */
  protected $_options = array();
  protected $_testUrl = 'https://iframely.com';

  /**
   * Constructor
   *
   * @param array $options
   */
  public function __construct($options = array())
  {
    $this->_setApiUrl();
    foreach( $options as $key => $value ) {
      $methodName = '_set' . ucfirst($key);
      if( method_exists($this, $methodName) ) {
        $this->$methodName($value);
        continue;
      }
      $this->_options[$key] = $value;
    }
    // Force the curl adapter if it's available
    if( extension_loaded('curl') ) {
      $adapter = new Zend_Http_Client_Adapter_Curl();
      $adapter->setCurlOption(CURLOPT_SSL_VERIFYPEER, false);
      $adapter->setCurlOption(CURLOPT_SSL_VERIFYHOST, false);
      $this->getHttpClient()->setAdapter($adapter);
    }
    $this->getHttpClient()->setConfig(array('timeout' => 15));
  }

  /**
   * Get the response  for URL
   * @param string $url
   *
   * @return self
   */
  public function get($url)
  {

    $cacheResponse = $this->_loadFromCache($url);
    if( $cacheResponse ) {
      return $cacheResponse;
    }
    $response = $this->_sendRequest($url);
    $this->_saveInCache($url, $response);
    return $response;
  }

  /**
   * Test the configuration
   * @param string $url
   *
   * @return self
   */
  public function test()
  {
    return $this->get($this->_testUrl);
  }

  /**
   * Set the secret key base
   * @param string $key
   *
   * @return self
   */
  protected function _setSecretKey($key)
  {
    $this->_secretKey = $key;
    return $this;
  }

  /**
   * Set the base url for vendor
   * @param string $baseUrl
   *
   * @return self
   */
  protected function _setBaseUrl($baseUrl)
  {
    if( empty($this->_baseUrl) ) {
      $this->_baseUrl = rtrim($baseUrl, '/');
    }
    return $this;
  }

  /**
   * Set the type base  apiURL
   * @param string $type
   *
   * @return self
   */
  protected function _setApiUrl($type = 'iframely')
  {
    $this->_apiUrl = $type == 'iframely' ? $this->_iframelyApiUrl : $this->_oembedApiUrl;
    return $this;
  }

  /**
   * Ignore error response
   * @param bool $ignoreError
   *
   * @return self
   */
  protected function _setIgnoreError($ignoreError)
  {
    $this->_ignoreError = $ignoreError;
    return $this;
  }

  /**
   * Send a client request
   * @param string $url
   *
   * @return array | null
   */
  protected function _sendRequest($url)
  {
    $params = $this->_getRequestParams();
    $params['url'] = $url;
    // Send request
    $client = $this->_prepareHttpClient()
      ->setParameterGet($params);
    // Process response
    $response = $client->request();
    $responseData = $this->_processHttpResponse($response);
    return $responseData;
  }

  /**
   * Get the http client and set default parameters
   *
   * @return Zend_Http_Client
   */
  protected function _prepareHttpClient()
  {
    $uri = $this->_baseUrl . $this->_apiUrl;
    return $this->getHttpClient()
        ->resetParameters()
        ->setMethod(Zend_Http_Client::GET)
        ->setHeaders('Accept', $this->_accept)
        ->setUri($uri)
    ;
  }

  /**
   * Process the response
   *
   * @param Zend_Http_Response $response
   * @return array
   * @throws Zend_Service_Exception
   */
  protected function _processHttpResponse($response)
  {
    // Check response body
    $responseData = $response->getBody();
    if( !is_string($responseData) || '' === $responseData ) {
      return !$this->_ignoreError ? array('error' => 'Invalid response') : '';
    }
    try {
      $responseData = Zend_Json::decode($responseData, Zend_Json::TYPE_ARRAY);
    } catch( Exception $e ) {
      $responseData = array('error' => 'Invalid json data or Invalid base url');
    }

    if( !empty($responseData['error']) && $this->_ignoreError ) {
      return;
    }

    return $responseData;
  }

  protected function _getRequestParams()
  {
    $params = array('iframe' => true);
    return $params;
  }

  protected function _loadFromCache($url)
  {
    $hasCache = Zend_Registry::isRegistered('Zend_Cache') && Zend_Registry::get('Zend_Cache') instanceof Zend_Cache_Core;
    if( !$hasCache ) {
      return;
    }
    $cacheId = $this->_makeCacheId($url);
    $cache = Zend_Registry::get('Zend_Cache');
    $response = $cache->load($cacheId);
    return $response;
  }

  protected function _saveInCache($url, $response)
  {
    if( !$response ) {
      return;
    }
    $cacheId = $this->_makeCacheId($url);
    $cache = Zend_Registry::get('Zend_Cache');
    $cache->save($response, $cacheId, array(), 3600);
  }

  protected function _makeCacheId($url)
  {
    return __CLASS__ . md5($url . serialize($this->_getRequestParams()));
  }
}
