<?php
/**
 * SocialEngine
 *
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Engine_Warehouse extends Zend_Service_Abstract
{
    private $url;

    private $endpoint;

    private $cache;

    private $token;

    /**
     * @var Zend_Cache_Core
     */
    private $zendCache;

    public function __construct($endpoint)
    {
        $this->endpoint = $endpoint;
        $this->url = Engine_Settings::get('warehouse.url', 'https://warehouse.socialengine.com');

        $adapter = new Zend_Http_Client_Adapter_Curl();
        $adapter->setCurlOption(CURLOPT_SSL_VERIFYPEER, false);
        $adapter->setCurlOption(CURLOPT_SSL_VERIFYHOST, false);
        $this->getHttpClient()->setAdapter($adapter);
        $this->getHttpClient()->setConfig(array('timeout' => 15));

        if (Zend_Registry::isRegistered('Zend_Cache')) {
            $this->zendCache = Zend_Registry::get('Zend_Cache');
        }
    }

    public function setCache($cache)
    {
        if (!$this->zendCache instanceof Zend_Cache_Core) {
            return $this;
        }

        $this->cache = $cache;
        return $this;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    public function get($options = array())
    {
        return $this->call(Zend_Http_Client::GET, $options);
    }

    private function call($method, $options = array())
    {
        $cacheKey = md5($this->endpoint . $method . print_r($options, true));
        if ($this->cache && ($cachedVersion = $this->zendCache->load($cacheKey))) {
            return $cachedVersion;
        }

        $uri = $this->url . $this->endpoint;

        try {
            $client = $this->getHttpClient()
                ->resetParameters()
                ->setMethod($method)
                ->setHeaders('Client', 'frontend')
                ->setUri($uri);

            if ($this->token) {
                $client->setHeaders('X-Auth-Token', $this->token);
            }

            switch ($method) {
                case Zend_Http_Client::GET:
                    $client->setParameterGet($options);
                    break;
            }

            $response = $client->request();
            $responseData = $response->getBody();
            $responseData = Zend_Json::decode($responseData, Zend_Json::TYPE_ARRAY);
        } catch (Exception $e) {
            if (Zend_Registry::isRegistered('Zend_Log')
                && ($log = Zend_Registry::get('Zend_Log')) instanceof Zend_Log) {
                $log->log($e->__toString(), Zend_Log::CRIT);
            }
            return false;
        }


        if (isset($responseData['error'])) {
            if ($responseData['message'] == '404 Not Found') {
                return false;
            }
            throw new Exception($responseData['message']);
        }

        if ($this->cache) {
            $this->zendCache->save($responseData, $cacheKey, array(), $this->cache);
        }

        return $responseData;
    }
}
