<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Gateways.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Payment_Model_DbTable_Gateways extends Engine_Db_Table
{
    protected $_rowClass = 'Payment_Model_Gateway';

    protected $_serializedColumns = array('config');

    protected $_cryptedColumns = array('config');

    private static $_cryptKey;

    public function getEnabledGatewayCount()
    {
        return $this->select()
            ->from($this, new Zend_Db_Expr('COUNT(*)'))
            ->where('enabled = ?', 1)
            ->query()
            ->fetchColumn()
            ;
    }

    public function getEnabledGateways()
    {
        return $this->fetchAll($this->select()->where('enabled = ?', true));
    }



    // Inline encryption/decryption

    public function insert(array $data)
    {
        // Serialize
        $data = $this->_serializeColumns($data);

        // Encrypt each column
        foreach ($this->_cryptedColumns as $col) {
            if (!empty($data[$col])) {
                $data[$col] = self::_encrypt($data[$col]);
            }
        }

        return parent::insert($data);
    }

    public function update(array $data, $where)
    {
        // Serialize
        $data = $this->_serializeColumns($data);

        // Encrypt each column
        foreach ($this->_cryptedColumns as $col) {
            if (!empty($data[$col])) {
                $data[$col] = self::_encrypt($data[$col]);
            }
        }

        return parent::update($data, $where);
    }

    protected function _fetch(Zend_Db_Table_Select $select)
    {
        $rows = parent::_fetch($select);
        foreach ($rows as $index => $data) {
            // Decrypt each column
            foreach ($this->_cryptedColumns as $col) {
                if (!empty($rows[$index][$col])) {
                    $rows[$index][$col] = self::_decrypt($rows[$index][$col]);
                }
            }
            // Unserialize
            $rows[$index] = $this->_unserializeColumns($rows[$index]);
        }

        return $rows;
    }



    // Crypt Utility

    private static function _encrypt($data)
    {
        if (!extension_loaded('mcrypt')) {
            return $data;
        }

        $key = self::_getCryptKey();

        if (version_compare(phpversion(), '7.1', '>=')) {
            return $data;
        }

        $cryptData = mcrypt_encrypt(MCRYPT_DES, $key, $data, MCRYPT_MODE_ECB);

        return $cryptData;
    }

    private static function _decrypt($data)
    {
        if (!extension_loaded('mcrypt')) {
            return $data;
        }

        $key = self::_getCryptKey();

        if (version_compare(phpversion(), '7.1', '>=') && is_string($data) && substr($data, -1) != '=') {
            return $data;
        }

        $cryptData = mcrypt_decrypt(MCRYPT_DES, $key, $data, MCRYPT_MODE_ECB);
        $cryptData = rtrim($cryptData, "\0");

        return $cryptData;
    }

    private static function _getCryptKey()
    {
        if (null === self::$_cryptKey) {
            $key = Engine_Api::_()->getApi('settings', 'core')->core_secret
                . '^'
                . Engine_Api::_()->getApi('settings', 'core')->payment_secret;
            self::$_cryptKey  = substr(md5($key, true), 0, 8);
        }

        return self::$_cryptKey;
    }
}
