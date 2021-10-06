<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Comment.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 * @todo       documentation
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Activity_Model_Comment extends Core_Model_Comment
{
  protected $resource_type = 'activity_action';

  public function tags()
  {
    return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('tags', 'core'));
  }

  protected function _delete()
  {
    $hashtags = Engine_Api::_()->activity()->getHashTags($this->body);
    $this->tags()->removeTagMaps($hashtags[0]);
    parent::_delete();
  }
}
