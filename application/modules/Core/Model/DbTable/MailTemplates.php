<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: MailTemplates.php 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Model_DbTable_MailTemplates extends Engine_Db_Table {

    protected $_rowClass = 'Core_Model_MailTemplate';

    public function getEmailTypes() {

        $enabledModuleNames = Engine_Api::_()->getDbtable('modules', 'core')->getEnabledModuleNames();
        $enabledModuleNames = array_diff($enabledModuleNames,array('payment', 'invite', 'core'));

        $excludeMailTypes = array('header', 'footer', 'header_member', 'footer_member', 'core_lostpassword');

        $name = $this->info('name');

        $select = $this->select()
            ->from($name, array('type', 'module'))
            ->where('module IN(?)', $enabledModuleNames)
            ->where('type NOT IN(?)', $excludeMailTypes);

        $emailTypes = $this->fetchAll($select);

        return $emailTypes;
    }

    public function getDefaultEmails()
    {
        $excludeMailTypes = array('header', 'footer', 'header_member', 'footer_member');

        $select = $this->select()
            ->from($this->info('name'), 'type')
            ->where('`default` = ?', 1)
            ->where('type NOT IN(?)', $excludeMailTypes);

        $types = $select
            ->query()
            ->fetchAll(Zend_Db::FETCH_COLUMN);

        return $types;
    }

    public function setDefaultEmails($values)
    {
        if( !is_array($values) ){
            throw new Core_Model_Exception('setDefaultEmails requires an array of emails');
        }

        $values = array_merge($values, array('header', 'footer', 'header_member', 'footer_member'));

        $types = $this->select()
            ->from($this->info('name'), 'type')
            ->query()
            ->fetchAll(Zend_Db::FETCH_COLUMN);

        $defaults = array();
        foreach( $types as $value ){
            if( in_array($value, $values) ){
                $defaults[] = $value;
            }
        }

        if( !empty($defaults) ){

            $this->update(array('default' => '1',), array('`type` IN(?)' => $defaults));

            $this->update(array('default' => '0',), array('`type` NOT IN(?)' => $defaults));
        } else {
            $this->update(array('default' => '0'), array('`default`' => '1'));
        }
    }
}
