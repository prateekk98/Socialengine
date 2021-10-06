<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: TagMaps.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Model_DbTable_TagMaps extends Engine_Db_Table
{
  protected $_rowClass = 'Core_Model_TagMap';

  protected $_serializedColumns = array('extra');

  public function deleteTagMap($tagMap) {
    $select = $this->select()->from($this->info('name'), 'tag_id')
      ->where('tagmap_id = ?', $tagMap->tagmap_id);
    $tagsIds = $this->fetchAll($select);
    foreach($tagsIds as $tagId ) {
      if (!empty($tag = Engine_Api::_()->getItem('core_tag', $tagId->tag_id))) {
        if ($tag->tag_count <= 1) {
          Engine_Api::_()->getDbtable('Tags', 'core')->delete(array('tag_id = ?' => $tagId->tag_id));
          continue;
        }
        $tag->tag_count = $tag->tag_count - 1;
        $tag->modified_date = date('Y-m-d H:i:s');
        $tag->save();
      }
    }
    $this->delete(array('tagmap_id = ?' => $tagMap->tagmap_id));
  }
}
