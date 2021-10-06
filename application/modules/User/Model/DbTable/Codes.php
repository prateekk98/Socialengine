<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Block.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class User_Model_DbTable_Codes extends Engine_Db_Table {

  public function isExist($code, $email) {
    
    return $this->select()
                ->from($this->info('name'), 'code_id')
                ->where('code =?',$code)
                ->where('email =?',$email)
                ->order('code_id DESC')
                ->limit(1)
                ->query()
                ->fetchColumn();
  }
  
  public function isEmailExist($email) {
    $select = $this->select()
              ->from($this->info('name'))
              ->where('email =?', $email)
              ->limit(1);
    return $this->fetchRow($select);
  }
}
