<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Content.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Model_DbTable_Content extends Engine_Db_Table
{
    protected $_serializedColumns = array('params');

    public function widgetId($widgetName, $pageName) {

        $contentTableName = $this->info('name');

        $pagesTableName = Engine_Api::_()->getDbTable('pages', 'core')->info('name');

        return $this->select()
            ->setIntegrityCheck(false)
            ->from($contentTableName, 'content_id')
            ->joinLeft($pagesTableName, $pagesTableName . '.page_id = ' . $contentTableName . '.page_id')
            ->where($pagesTableName . '.name = ?', $pageName)
            ->where($contentTableName . '.name = ?', $widgetName)
            ->query()
            ->fetchColumn();
    }
}
