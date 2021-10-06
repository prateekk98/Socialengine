<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: install.php 9878 2013-02-13 03:18:43Z shaun $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Music
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Payment_Installer extends Engine_Package_Installer_Module
{


  public function onInstall()
  {
    $db = $this->getDb();
            // Check for levels column
    try {
        $cols = $db->describeTable('engine4_payment_subscriptions');
        if( !isset($cols['main_package_id']) ) {
            $db->query('ALTER TABLE `engine4_payment_subscriptions` ADD `main_package_id` INT NOT NULL DEFAULT "0";');
        }
    } catch( Exception $e ) {}

    if($this->_databaseOperationType != 'upgrade'){
        $select = new Zend_Db_Select($db);
        // profile page
        $select
          ->from('engine4_core_pages')
          ->where('name = ?', 'user_profile_index')
          ->limit(1);
        $pageId = $select->query()->fetchObject()->page_id;
        if($pageId) {
            // container_id (will always be there)
            $select = new Zend_Db_Select($db);
            $select
                ->from('engine4_core_content')
                ->where('page_id = ?', $pageId)
                ->where('type = ?', 'container')
                ->limit(1);
            $containerId = $select->query()->fetchObject()->content_id;

            $select = new Zend_Db_Select($db);
            $select
                ->from('engine4_core_content')
                ->where('parent_content_id = ?', $containerId)
                ->where('type = ?', 'container')
                ->where('name = ?', 'middle')
                ->limit(1);
            $middleId = $select->query()->fetchObject()->content_id;

            // tab_id (tab container) may not always be there
            $select
                ->reset('where')
                ->where('type = ?', 'widget')
                ->where('name = ?', 'core.container-tabs')
                ->where('page_id = ?', $pageId)
                ->limit(1);
            $tabId = $select->query()->fetchObject();
            if( $tabId && @$tabId->content_id ) {
                $tabId = $tabId->content_id;
            } else {
                $tabId = null;
            }
            if($tabId || $middleId) {
                
              $select = new Zend_Db_Select($db);
              $select_content = $select
                  ->from('engine4_core_content')
                  ->where('page_id = ?', $pageId)
                  ->where('type = ?', 'widget')
                  ->where('name = ?', 'payment.subscribe-plan')
                  ->limit(1);
              $content_id = $select_content->query()->fetchObject()->content_id;
              
              if(empty($content_id)) {
                // payment tab on profile
                $db->insert('engine4_core_content', array(
                    'page_id' => $pageId,
                    'type'    => 'widget',
                    'name'    => 'payment.subscribe-plan',
                    'parent_content_id' => ($tabId ? $tabId : $middleId),
                    'order'   => 10,
                    'params'  => '{"show_criteria":["planTitle","expiryDate","nextPayment","currentMember","daysleft"],"paymentButton":"1","title":"Subscription Plan Info","nomobile":"0","name":"payment.subscribe-plan"}',
                ));
              }
            }
        }
    }
    
    //Upgrade Work
    $table_exist_packages = $db->query("SHOW TABLES LIKE 'engine4_payment_packages'")->fetch();
    if (!empty($table_exist_packages)) {
      $extra_day = $db->query("SHOW COLUMNS FROM engine4_payment_packages LIKE 'extra_day'")->fetch();
      if (empty($extra_day)) {
        $db->query('ALTER TABLE `engine4_payment_packages` ADD `extra_day` INT(8) NOT NULL;');
      }
      
      $reminder_email = $db->query("SHOW COLUMNS FROM engine4_payment_packages LIKE 'reminder_email'")->fetch();
      if (empty($reminder_email)) {
        $db->query('ALTER TABLE `engine4_payment_packages` ADD `reminder_email` INT(8) NOT NULL;');
      }
      
      $reminder_email_type = $db->query("SHOW COLUMNS FROM engine4_payment_packages LIKE 'reminder_email_type'")->fetch();
      if (empty($reminder_email_type)) {
        $db->query('ALTER TABLE `engine4_payment_packages` ADD `reminder_email_type` ENUM("day","week","month","year") NOT NULL');
      }
    }
    
    parent::onInstall();
  }
}
