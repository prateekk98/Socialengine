<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: install.php 9906 2013-02-14 02:54:51Z shaun $
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Install extends Engine_Package_Installer_Module
{
    protected $_dropColumnsOnPreInstall = array(
        '4.9.0' => array(
            'engine4_core_links' => array('params')
        )
    );

    protected function _runCustomQueries()
    {
        $db = $this->getDb();

        // Check for levels column
        try {
            $cols = $db->describeTable('engine4_core_pages');

            if( !isset($cols['levels']) ) {
                $db->query('ALTER TABLE `engine4_core_pages` ' .
                    'ADD COLUMN `levels` text default NULL AFTER `layout`');
            } else if( $cols['levels']['DEFAULT'] != 'NULL' ) {
                $db->query('ALTER TABLE `engine4_core_pages` ' .
                    'CHANGE COLUMN `levels` `levels` text default NULL AFTER `layout`');
            }

        } catch( Exception $e ) {
            throw $e;
        }

        // Get array of levels
        $select = new Zend_Db_Select($db);
        $levels = $select
            ->from('engine4_authorization_levels', 'level_id')
            ->query()
            ->fetchAll(Zend_Db::FETCH_COLUMN)
        ;
        $levels = Zend_Json::encode($levels);

        // assign levels json to any pages missing it
        try {
            $db->update('engine4_core_pages', array(
                'levels' => $levels,
            ), array(
                'custom = ?' => 1,
                'levels = \'\' OR levels = \'[]\' OR levels IS NULL',
            ));
        } catch( Exception $e ) {

        }

        // Remove public column for adcampaigns
        $cols = $db->describeTable('engine4_core_adcampaigns');
        if( isset($cols['public']) ) {
            $publicLevelId = $db->select()
                ->from('engine4_authorization_levels', 'level_id')
                ->where('flag = ?', 'public')
                ->limit(1)
                ->query()
                ->fetchColumn();

            $publicAdCampaigns = $db->select()
                ->from('engine4_core_adcampaigns')
                ->where('public = ?', 1)
                ->query()
                ->fetchAll()
            ;

            if( $publicLevelId && $publicAdCampaigns ) {
                foreach( $publicAdCampaigns as $publicAdCampaign ) {
                    if( empty($publicAdCampaign['level']) ||
                        !($levels = Zend_Json::decode($publicAdCampaign['level'])) ||
                        !is_array($levels) ) {
                        $levels = array();
                    }
                    if( !in_array($publicLevelId, $levels) ) {
                        $levels[] = $publicLevelId;
                        $db->update('engine4_core_adcampaigns', array(
                            'level' => Zend_Json::encode($levels),
                        ), array(
                            'adcampaign_id = ?' => $publicAdCampaign['adcampaign_id'],
                        ));
                    }
                }
            }

            $db->query('ALTER TABLE `engine4_core_adcampaigns` DROP COLUMN `public`');
        }

        $this->_onUpgrade490();

        // Update all ip address to ipv6
        $this->_convertToIPv6($db, 'engine4_core_nodes', 'ip', false);
        $this->_convertToIPv6($db, 'engine4_core_bannedips', 'start', false);
        $this->_convertToIPv6($db, 'engine4_core_bannedips', 'stop', false);

        $this->_addContactPage();
        $this->_addPrivacyPage();
        $this->_addTermsOfServicePage();
        $this->_addHashtagPage();

        try {
            $cols = $db->describeTable('engine4_core_mailtemplates');
            if(!isset($cols['member_level'])) {
                $db->query('ALTER TABLE `engine4_core_mailtemplates` '.
                    'ADD COLUMN `member_level` text default NULL');
            }
        } catch( Exception $e ) {
            throw $e;
        }

        if(@is_dir(APPLICATION_PATH .DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR.'widgets'.DIRECTORY_SEPARATOR.'weather')){
            $this->deleteDir(APPLICATION_PATH .DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR.'widgets'.DIRECTORY_SEPARATOR.'weather');
        }
        
        if(@is_dir(APPLICATION_PATH .DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR.'widgets'.DIRECTORY_SEPARATOR.'rss')){
            $this->deleteDir(APPLICATION_PATH .DIRECTORY_SEPARATOR.'application'.DIRECTORY_SEPARATOR.'widgets'.DIRECTORY_SEPARATOR.'rss');
        }

        if( method_exists($this, '_addGenericPage') ) {
            $this->_addGenericPage('core_error_requireuser', 'Sign-in Required', 'Sign-in Required Page', '');
            $this->_addGenericPage('core_search_index', 'Search Results', 'Search Page', '');
        } else {
            $this->_error('Missing _addGenericPage method');
        }
    }

    protected function _addPrivacyPage()
    {
        $db = $this->getDb();

        // profile page
        $page_id = $db->select()
            ->from('engine4_core_pages', 'page_id')
            ->where('name = ?', 'core_help_privacy')
            ->limit(1)
            ->query()
            ->fetchColumn();

        if (!$page_id) {
            // Insert page
            $db->insert('engine4_core_pages', array(
                'name' => 'core_help_privacy',
                'displayname' => 'Privacy Page',
                'title' => 'Privacy Policy',
                'description' => 'This is the privacy policy page',
                'provides' => 'no-viewer;no-subject',
                'custom' => 0,
            ));
            $page_id = $db->lastInsertId();

            // Insert main
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'main',
                'page_id' => $page_id,
            ));
            $main_id = $db->lastInsertId();

            // Insert middle
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'middle',
                'page_id' => $page_id,
                'parent_content_id' => $main_id,
                'order' => 2,
            ));
            $middle_id = $db->lastInsertId();

            // Insert content
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'core.content',
                'page_id' => $page_id,
                'parent_content_id' => $middle_id,
                'order' => 1,
            ));
        }

        return $this;
    }

    protected function _addTermsOfServicePage()
    {
        $db = $this->getDb();

        // profile page
        $page_id = $db->select()
            ->from('engine4_core_pages', 'page_id')
            ->where('name = ?', 'core_help_terms')
            ->limit(1)
            ->query()
            ->fetchColumn();

        if (!$page_id) {
            // Insert page
            $db->insert('engine4_core_pages', array(
                'name' => 'core_help_terms',
                'displayname' => 'Terms of Service Page',
                'title' => 'Terms of Service',
                'description' => 'This is the terms of service page',
                'provides' => 'no-viewer;no-subject',
                'custom' => 0,
            ));
            $page_id = $db->lastInsertId();

            // Insert main
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'main',
                'page_id' => $page_id,
            ));
            $main_id = $db->lastInsertId();

            // Insert middle
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'middle',
                'page_id' => $page_id,
                'parent_content_id' => $main_id,
                'order' => 2,
            ));
            $middle_id = $db->lastInsertId();

            // Insert content
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'core.content',
                'page_id' => $page_id,
                'parent_content_id' => $middle_id,
                'order' => 1,
            ));
        }

        return $this;
    }

    protected function _addContactPage()
    {
        $db = $this->getDb();

        // profile page
        $page_id = $db->select()
            ->from('engine4_core_pages', 'page_id')
            ->where('name = ?', 'core_help_contact')
            ->limit(1)
            ->query()
            ->fetchColumn();

        if (!$page_id) {
            // Insert page
            $db->insert('engine4_core_pages', array(
                'name' => 'core_help_contact',
                'displayname' => 'Contact Page',
                'title' => 'Contact Us',
                'description' => 'This is the contact page',
                'provides' => 'no-viewer;no-subject',
                'custom' => 0,
            ));
            $page_id = $db->lastInsertId();

            // Insert main
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'main',
                'page_id' => $page_id,
            ));
            $main_id = $db->lastInsertId();

            // Insert middle
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'middle',
                'page_id' => $page_id,
                'parent_content_id' => $main_id,
                'order' => 2,
            ));
            $middle_id = $db->lastInsertId();

            // Insert content
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'core.content',
                'page_id' => $page_id,
                'parent_content_id' => $middle_id,
                'order' => 1,
            ));
        }
        return $this;
    }

    protected function _addHashtagPage()
    {
        $db = $this->getDb();
        if( $this->_databaseOperationType == 'upgrade') {
            return;
        }

        $page_id = $db->select()
            ->from('engine4_core_pages', 'page_id')
            ->where('name = ?', 'core_hashtag_index')
            ->limit(1)
            ->query()
            ->fetchColumn();

        if (!$page_id) {

            $db->insert('engine4_core_pages', array(
                'name' => 'core_hashtag_index',
                'displayname' => 'Hashtag Results Page',
                'title' => 'Hashtag Results Page',
                'description' => 'This page displays searched hashtags.',
                'custom' => 0,
            ));
            $page_id = $db->lastInsertId();

            // Insert main
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'main',
                'page_id' => $page_id,
                'order' => 1,
            ));
            $main_id = $db->lastInsertId();

            // Insert main-middle
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'middle',
                'page_id' => $page_id,
                'parent_content_id' => $main_id,
                'order' => 3,
            ));
            $main_middle_id = $db->lastInsertId();

            // Insert main-right
            $db->insert('engine4_core_content', array(
                'type' => 'container',
                'name' => 'right',
                'page_id' => $page_id,
                'parent_content_id' => $main_id,
                'order' => 1,
            ));
            $main_right_id = $db->lastInsertId();

            // Check if it's already been placed
            $select = new Zend_Db_Select($db);
            $select
                ->from('engine4_core_content')
                ->where('page_id = ?', $page_id)
                ->where('parent_content_id = ?', $main_middle_id)
                ->where('type = ?', 'widget')
                ->where('name = ?', 'core.show-search-hashtags');
            $info = $select->query()->fetch();
            if(empty($info)) {
                $db->insert('engine4_core_content', array(
                    'type' => 'widget',
                    'name' => 'core.show-search-hashtags',
                    'page_id' => $page_id,
                    'parent_content_id' => $main_middle_id,
                    'order' => 1,
                ));
            }

            // middle column
            $db->insert('engine4_core_content', array(
                'page_id' => $page_id,
                'type' => 'widget',
                'name' => 'core.container-tabs',
                'parent_content_id' => $main_middle_id,
                'order' => 2,
                'params' => '{"max":"5"}',
            ));
            $tabId = $db->lastInsertId('engine4_core_content');

            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'core.search-hashtags',
                'page_id' => $page_id,
                'parent_content_id' => $main_right_id,
                'order' => 1,
            ));

            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'core.hashtags-cloud',
                'page_id' => $page_id,
                'parent_content_id' => $main_right_id,
                'order' => 1,
                'params' => '{"title":"Trending Hashtags"}'
            ));

            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'activity.feed',
                'page_id' => $page_id,
                'parent_content_id' => $tabId,
                'order' => 3,
                'params' => '{"title":"Trending Posts"}'
            ));
        } else {

            // container_id (will always be there)
            $select = new Zend_Db_Select($db);
            $select
                ->from('engine4_core_content')
                ->where('page_id = ?', $page_id)
                ->where('type = ?', 'container')
                ->where('name = ?', 'main')
                ->limit(1);
            
            $containerId = $select->query()->fetchObject()->content_id;

            // middle_id (will always be there)
            $select = new Zend_Db_Select($db);
            $select
                ->from('engine4_core_content')
                ->where('parent_content_id = ?', $containerId)
                ->where('type = ?', 'container')
                ->where('name = ?', 'middle')
                ->limit(1);
            $middleId = $select->query()->fetchObject()->content_id;


            // Check if it's already been placed
            $select = new Zend_Db_Select($db);
            $select
                ->from('engine4_core_content')
                ->where('page_id = ?', $page_id)
                ->where('parent_content_id = ?', $middleId)
                ->where('type = ?', 'widget')
                ->where('name = ?', 'core.show-search-hashtags');
            $info = $select->query()->fetch();
            if(empty($info)) {
                $db->insert('engine4_core_content', array(
                    'type' => 'widget',
                    'name' => 'core.show-search-hashtags',
                    'page_id' => $page_id,
                    'parent_content_id' => $middleId,
                    'order' => 1,
                ));
            }

            // tab_id (tab container) may not always be there
            $select
                ->reset('where')
                ->where('type = ?', 'widget')
                ->where('name = ?', 'core.container-tabs')
                ->where('page_id = ?', $page_id)
                ->limit(1);
            $tabId = $select->query()->fetchObject();
            if(empty($tabId)) {
                // middle column
                $db->insert('engine4_core_content', array(
                    'page_id' => $page_id,
                    'type' => 'widget',
                    'name' => 'core.container-tabs',
                    'parent_content_id' => $middleId,
                    'order' => 2,
                    'params' => '{"max":"5"}',
                ));
                $tabId = $db->lastInsertId('engine4_core_content');
            } else {
                if( $tabId && @$tabId->content_id ) {
                    $tabId = $tabId->content_id;
                } else {
                    $tabId = null;
                }
            }

            // Check if it's already been placed
            $select = new Zend_Db_Select($db);
            $select
                ->from('engine4_core_content')
                ->where('page_id = ?', $page_id)
                ->where('type = ?', 'widget')
                ->where('name = ?', 'activity.feed');
            $info = $select->query()->fetch();
            if(!empty($info)) {
                $db->query("DELETE FROM `engine4_core_content` WHERE `engine4_core_content`.`content_id` = '".$info['content_id']."';");
            }

            // Check if it's already been placed
            $select = new Zend_Db_Select($db);
            $select
                ->from('engine4_core_content')
                ->where('page_id = ?', $page_id)
                ->where('parent_content_id = ?', $tabId)
                ->where('type = ?', 'widget')
                ->where('name = ?', 'activity.feed');
            $info = $select->query()->fetch();
            if(empty($info)) {
                $db->insert('engine4_core_content', array(
                    'type' => 'widget',
                    'name' => 'activity.feed',
                    'page_id' => $page_id,
                    'parent_content_id' => $tabId,
                    'order' => 3,
                    'params' => '{"title":"Trending Posts"}'
                ));
            }
        }

        // Insert tag-cloud on member home page
        $pageId = $db->select()
            ->from('engine4_core_pages', 'page_id')
            ->where('name = ?', 'user_index_home')
            ->limit(1)
            ->query()
            ->fetchColumn();

        if ($pageId) {
            $hasWidget = $db->select()
                ->from('engine4_core_content', new Zend_Db_Expr('TRUE'))
                ->where('page_id = ?', $pageId)
                ->where('type = ?', 'widget')
                ->where('name = ?', 'core.hashtags-cloud')
                ->query()
                ->fetchColumn();

            if (!$hasWidget) {

                $containerId = $db->select()
                    ->from('engine4_core_content', 'content_id')
                    ->where('page_id = ?', $pageId)
                    ->where('type = ?', 'container')
                    ->limit(1)
                    ->query()
                    ->fetchColumn();

                $select = new Zend_Db_Select($db);
                $rightId = $select
                    ->from('engine4_core_content', 'content_id')
                    ->where('parent_content_id = ?', $containerId)
                    ->where('type = ?', 'container')
                    ->where('name = ?', 'right')
                    ->limit(1)
                    ->query()
                    ->fetchColumn();

                // insert
                if( $rightId ) {
                    $db->insert('engine4_core_content', array(
                        'page_id' => $pageId,
                        'type'    => 'widget',
                        'name'    => 'core.hashtags-cloud',
                        'parent_content_id' => $rightId,
                        'order'   => 3,
                        'params' => '{"title":"Trending Hashtags"}'
                    ));
                }
            }
        }
        return $this;
    }

    protected function _convertToIPv6($db, $table, $column, $isNull = false)
    {
        // Note: this group of functions will convert an IPv4 address to the new
        // IPv6-compatibly representation
        // ip = UNHEX(CONV(ip, 10, 16))

        // Detect if this is a 32bit system
        $is32bit = ( ip2long('200.200.200.200') < 0 );
        $offset = ( $is32bit ? '4294967296' : '0' );

        // Describe
        $cols = $db->describeTable($table);

        // Update
        if( isset($cols[$column]) && $cols[$column]['DATA_TYPE'] != 'varbinary(16)' ) {
            $temporaryColumn = $column . '_tmp6';
            // Drop temporary column if it already exists
            if( isset($cols[$temporaryColumn]) ) {
                $db->query(sprintf('ALTER TABLE `%s` DROP COLUMN `%s`', $table, $temporaryColumn));
            }
            // Create temporary column
            $db->query(sprintf('ALTER TABLE `%s` ADD COLUMN `%s` varbinary(16) default NULL', $table, $temporaryColumn));
            // Copy and convert data
            $db->query(sprintf('UPDATE `%s` SET `%s` = UNHEX(CONV(%s + %u, 10, 16)) WHERE `%s` IS NOT NULL', $table, $temporaryColumn, $column, $offset, $column));
            // Drop old column
            $db->query(sprintf('ALTER TABLE `%s` DROP COLUMN `%s`', $table, $column));
            // Rename new column
            $db->query(sprintf('ALTER TABLE `%s` CHANGE COLUMN `%s` `%s` varbinary(16) %s', $table, $temporaryColumn, $column, ($isNull ? 'default NULL' : 'NOT NULL')));
        }
    }

    protected function _onUpgrade490()
    {
        $db = $this->getDb();
        if( $this->_databaseOperationType != 'upgrade' || version_compare($this->_currentVersion, '4.9.0', '>=') ) {
            return;
        }
        // Header page
        $order = $db->select()
            ->from('engine4_core_content', 'order')
            ->where('name = ?', 'core.menu-mini')
            ->where('page_id = ?', 1)
            ->limit(1)
            ->query()
            ->fetchColumn();

        if( $order ) {
            $db->query('UPDATE `engine4_core_content` SET `order`=`order`+1 WHERE `order` >' . $order);
            // Insert content
            $db->insert('engine4_core_content', array(
                'type' => 'widget',
                'name' => 'core.search-mini',
                'page_id' => 1,
                'parent_content_id' => 100,
                'order' => $order + 1,
            ));
        }
    }
    public function deleteDir($path){
        $files = glob($path.DIRECTORY_SEPARATOR."*");  
        foreach($files as $file) { 
          if(is_file($file)){
            @unlink($file); 
          } elseif(is_dir($file)) {
            self::deleteDir($file);
          }  
        } 
        @rmdir($path);
    }
}
