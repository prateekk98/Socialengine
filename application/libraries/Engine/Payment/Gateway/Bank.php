<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Bank.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Engine
 * @package    Engine_Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Payment_Gateway_Bank extends Engine_Payment_Gateway
{
  // Support
  protected $_supportedCurrencies = array(
    'USD'=>'USD',
    'AED'=>'AED',
    'AFN'=>'AFN',
    'ALL'=>'ALL',
    'AMD'=>'AMD',
    'ANG'=>'ANG',
    'AOA'=>'AOA',
    'ARS'=>'ARS',
    'AUD'=>'AUD',
    'AWG'=>'AWG',
    'AZN'=>'AZN',
    'BAM'=>'BAM',
    'BBD'=>'BBD',
    'BDT'=>'BDT',
    'BGN'=>'BGN',
    'BIF'=>'BIF',
    'BMD'=>'BMD',
    'BND'=>'BND',
    'BOB'=>'BOB',
    'BRL'=>'BRL',
    'BSD'=>'BSD',
    'BWP'=>'BWP',
    'BZD'=>'BZD',
    'CAD'=>'CAD',
    'CDF'=>'CDF',
    'CHF'=>'CHF',
    'CLP'=>'CLP',
    'CNY'=>'CNY',
    'COP'=>'COP',
    'CRC'=>'CRC',
    'CVE'=>'CVE',
    'CZK'=>'CZK',
    'DJF'=>'DJF',
    'DKK'=>'DKK',
    'DOP'=>'DOP',
    'DZD'=>'DZD',
    'EGP'=>'EGP',
    'ETB'=>'ETB',
    'EUR'=>'EUR',
    'FJD'=>'FJD',
    'FKP'=>'FKP',
    'GBP'=>'GBP',
    'GEL'=>'GEL',
    'GIP'=>'GIP',
    'GMD'=>'GMD',
    'GNF'=>'GNF',
    'GTQ'=>'GTQ',
    'GYD'=>'GYD',
    'HKD'=>'HKD',
    'HNL'=>'HNL',
    'HRK'=>'HRK',
    'HTG'=>'HTG',
    'HUF'=>'HUF',
    'IDR'=>'IDR',
    'ILS'=>'ILS',
    'INR'=>'INR',
    'ISK'=>'ISK',
    'JMD'=>'JMD',
    'JPY'=>'JPY',
    'KES'=>'KES',
    'KGS'=>'KGS',
    'KHR'=>'KHR',
    'KMF'=>'KMF',
    'KRW'=>'KRW',
    'KYD'=>'KYD',
    'KZT'=>'KZT',
    'LAK'=>'LAK',
    'LBP'=>'LBP',
    'LKR'=>'LKR',
    'LRD'=>'LRD',
    'LSL'=>'LSL',
    'MAD'=>'MAD',
    'MDL'=>'MDL',
    'MGA'=>'MGA',
    'MKD'=>'MKD',
    'MMK'=>'MMK',
    'MNT'=>'MNT',
    'MOP'=>'MOP',
    'MRO'=>'MRO',
    'MUR'=>'MUR',
    'MVR'=>'MVR',
    'MWK'=>'MWK',
    'MXN'=>'MXN',
    'MYR'=>'MYR',
    'MZN'=>'MZN',
    'NAD'=>'NAD',
    'NGN'=>'NGN',
    'NIO'=>'NIO',
    'NOK'=>'NOK',
    'NPR'=>'NPR',
    'NZD'=>'NZD',
    'PAB'=>'PAB',
    'PEN'=>'PEN',
    'PGK'=>'PGK',
    'PHP'=>'PHP',
    'PKR'=>'PKR',
    'PLN'=>'PLN',
    'PYG'=>'PYG',
    'QAR'=>'QAR',
    'RON'=>'RON',
    'RSD'=>'RSD',
    'RUB'=>'RUB',
    'RWF'=>'RWF',
    'SAR'=>'SAR',
    'SBD'=>'SBD',
    'SCR'=>'SCR',
    'SEK'=>'SEK',
    'SGD'=>'SGD',
    'SHP'=>'SHP',
    'SLL'=>'SLL',
    'SOS'=>'SOS',
    'SRD'=>'SRD',
    'STD'=>'STD',
    'SZL'=>'SZL',
    'THB'=>'THB',
    'TJS'=>'TJS',
    'TOP'=>'TOP',
    'TRY'=>'TRY',
    'TTD'=>'TTD',
    'TWD'=>'TWD',
    'TZS'=>'TZS',
    'UAH'=>'UAH',
    'UGX'=>'UGX',
    'UYU'=>'UYU',
    'UZS'=>'UZS',
    'VND'=>'VND',
    'VUV'=>'VUV',
    'WST'=>'WST',
    'XAF'=>'XAF',
    'XCD'=>'XCD',
    'XOF'=>'XOF',
    'XPF'=>'XPF',
    'YER'=>'YER',
    'ZAR'=>'ZAR',
    'ZMW'=>'ZMW'
  );
  protected $_supportedLanguages = array(
    'es', 'en', 'de', 'fr', 'nl', 'pt', 'zh', 'it', 'ja', 'pl',
  );
  protected $_supportedRegions = array(
    'AF', 'AX', 'AL', 'DZ', 'AS', 'AD', 'AO', 'AI', 'AQ', 'AG', 'AR', 'AM',
    'AW', 'AU', 'AT', 'AZ', 'BS', 'BH', 'BD', 'BB', 'BY', 'BE', 'BZ', 'BJ',
    'BM', 'BT', 'BO', 'BA', 'BW', 'BV', 'BR', 'IO', 'BN', 'BG', 'BF', 'BI',
    'KH', 'CM', 'CA', 'CV', 'KY', 'CF', 'TD', 'CL', 'CN', 'CX', 'CC', 'CO',
    'KM', 'CG', 'CD', 'CK', 'CR', 'CI', 'HR', 'CU', 'CY', 'CZ', 'DK', 'DJ',
    'DM', 'DO', 'EC', 'EG', 'SV', 'GQ', 'ER', 'EE', 'ET', 'FK', 'FO', 'FJ',
    'FI', 'FR', 'GF', 'PF', 'TF', 'GA', 'GM', 'GE', 'DE', 'GH', 'GI', 'GR',
    'GL', 'GD', 'GP', 'GU', 'GT', 'GG', 'GN', 'GW', 'GY', 'HT', 'HM', 'VA',
    'HN', 'HK', 'HU', 'IS', 'IN', 'ID', 'IR', 'IQ', 'IE', 'IM', 'IL', 'IT',
    'JM', 'JP', 'JE', 'JO', 'KZ', 'KE', 'KI', 'KP', 'KR', 'KW', 'KG', 'LA',
    'LV', 'LB', 'LS', 'LR', 'LY', 'LI', 'LT', 'LU', 'MO', 'MK', 'MG', 'MW',
    'MY', 'MV', 'ML', 'MT', 'MH', 'MQ', 'MR', 'MU', 'YT', 'MX', 'FM', 'MD',
    'MC', 'MN', 'MS', 'MA', 'MZ', 'MM', 'NA', 'NR', 'NP', 'NL', 'AN', 'NC',
    'NZ', 'NI', 'NE', 'NG', 'NU', 'NF', 'MP', 'NO', 'OM', 'PK', 'PW', 'PS',
    'PA', 'PG', 'PY', 'PE', 'PH', 'PN', 'PL', 'PT', 'PR', 'QA', 'RE', 'RO',
    'RU', 'RW', 'SH', 'KN', 'LC', 'PM', 'VC', 'WS', 'SM', 'ST', 'SA', 'SN',
    'CS', 'SC', 'SL', 'SG', 'SK', 'SI', 'SB', 'SO', 'ZA', 'GS', 'ES', 'LK',
    'SD', 'SR', 'SJ', 'SZ', 'SE', 'CH', 'SY', 'TW', 'TJ', 'TZ', 'TH', 'TL',
    'TG', 'TK', 'TO', 'TT', 'TN', 'TR', 'TM', 'TC', 'TV', 'UG', 'UA', 'AE',
    'GB', 'US', 'UM', 'UY', 'UZ', 'VU', 'VE', 'VN', 'VG', 'VI', 'WF', 'EH',
    'YE', 'ZM',
  );
  protected $_supportedBillingCycles = array(
    /* 'Day', */ 'Week', /* 'SemiMonth',*/ 'Month', 'Year',
  );


  // Translation

  protected $_transactionMap = array();

  // General
  /**
   * Constructor
   *
   * @param array $options
   */
  public function  __construct(array $options = null)
  {
    parent::__construct($options);
    
    if( null === $this->getGatewayMethod() ) {
      $this->setGatewayMethod('POST');
    }
  }
  /**
   * Get the service API
   *
   * @return Engine_Service_PayPal
   */
  public function getService()
  {
    if( null === $this->_service ) {
      $this->_service = new Engine_Service_Bank(array_merge(
        $this->getConfig(),
        array(
          'testMode' => $this->getTestMode(),
          'format'=> 'html',
          'log' => ( true ? $this->getLog() : null ),
        )
      ));
    }
    return $this->_service;
  }
  public function getGatewayUrl()
  {
    if (null !== $this->_gatewayUrl) {
      return $this->_gatewayUrl;
    }
    $view = Zend_Registry::isRegistered('Zend_View') ? Zend_Registry::get('Zend_View') : null;
    return $view->url(array('action' => 'setGatewayInfo', 'controller' => 'subscription', 'module' => 'payment'), 'default');
  }
  // IPN
  public function processIpn(Engine_Payment_Ipn $ipn)
  {
    // Validate ----------------------------------------------------------------

    // Get raw data
    $rawData = $ipn->getRawData();

    // Log raw data
    //if( 'development' === APPLICATION_ENV ) {
      $this->_log(print_r($rawData, true), Zend_Log::DEBUG);
    //}

    // Success!
    $this->_log('IPN Validation Succeeded');



    // Process -----------------------------------------------------------------
    $rawData = $ipn->getRawData();

    $data = $rawData;

    return $data;
  }

  // Transaction

  public function processTransaction(Engine_Payment_Transaction $transaction)
  {
    $data = array();
    $rawData = $transaction->getRawData();
    // HACK
    if( !empty($rawData['return_url']) ) {
      $this->_gatewayUrl = $rawData['return_url'];
    }
    $data = $rawData;
    return $data;
  }
  public function test()
  {
    return true;
  }
}
