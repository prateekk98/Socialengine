<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Item.php 9747 2016-12-06 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Activity_View_Helper_GetActionContent extends Zend_View_Helper_Abstract
{

  public function getActionContent(Activity_Model_Action $action, $similarActivities = array() )
  {
    $similarFeedType = $action->type . '_' . $action->getObject()->getGuid();
    $action->body = $this->updateActionContent($action, $action->body);
    if( empty($similarActivities) || !isset($similarActivities[$similarFeedType]) ) {
      return $action->getContent();
    }

    $actionSubject = $action->getSubject();
    $otherItems = array();
    foreach( $similarActivities[$similarFeedType] as $activity ) {
      $activitySubject = $activity->getSubject();
      if( $activity->getSubject() === $actionSubject ) {
        continue;
      }
      $otherItems[$activitySubject->getGuid()] = $activitySubject;
    }

    return $action->getContent($otherItems);
  }

  public function updateActionContent($action, $content)
  {
    $composerOptions = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.composer.options');
    if (empty($composerOptions)) {
      return $content;
    }

    $content = $this->smileyToEmoticons($content);

    if (in_array('userTags', $composerOptions)) {
      $content = $this->replaceTags($action, $content);
    }

    if (in_array('hashtags', $composerOptions) &&
      ($action instanceof Activity_Model_Action || $action instanceof Activity_Model_Comment)
    ) {
      $content = $this->replaceHashTags($content);
    }

    return $content;
  }

  public function smileyToEmoticons($string = null)
  {
    if (!in_array(
      'emoticons',
      Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.composer.options')
    )) {
      return $string;
    }

    $emoticonsTag = Engine_Api::_()->activity()->getEmoticons(true);

    if (empty($emoticonsTag)) {
      return $string;
    }

    $string = str_replace("&lt;:o)", "<:o)", $string);
    $string = str_replace("(&amp;)", "(&)", $string);

    return strtr($string, $emoticonsTag);
  }

  private function replaceTags($action, $content)
  {
    $actionParams = is_string($action->params) ? Zend_Json::decode($action->params) : (array) $action->params;
    if (isset($actionParams['tags'])) {
      foreach ((array) $actionParams['tags'] as $key => $tagStrValue) {
        $tag = Engine_Api::_()->getItemByGuid($key);
        if (!$tag) {
          continue;
        }
        $replaceStr = '<a class="feed_item_username" '
          . 'href="' . $tag->getHref() . '" '
          . 'rel="' . $tag->getType() . ' ' . $tag->getIdentity() . '" >'
          . $tag->getTitle()
          . '</a>';
        $content = preg_replace("/" .addcslashes(preg_quote($tagStrValue),"/"). "/", $replaceStr, $content);
      }
    }
    return $content;
  }

  private function replaceHashTags($content)
  {
    $string = $content;
    $hashtags = Engine_Api::_()->activity()->getHashTags($string);
    $hashtags = $hashtags[0];
    if( empty($hashtags) ) {
      return $string;
    }
    $newString = '';
    foreach( $hashtags as $hashtag ) {
      $hasHastag = strpos($string, '#' . $hashtag);
      $substr = $hasHastag ? substr($string, 0, $hasHastag) : '';
      $newString .= $substr . $this->getHashtagLink($hashtag);
      $string = substr($string, $hasHastag + strlen($hashtag) + 1);
    }
    $newString .= $string;
    return $newString;
  }

  private function getHashtagLink($hashtag)
  {
    $view = Zend_Registry::get('Zend_View');
    $url = $this->view->url(array('controller' => 'hashtag', 'action' => 'index'), "core_hashtags") . "?search=" . urlencode('#' . $hashtag);
    return "<a href='$url'>" . '#' . $hashtag . "</a>";
  }
}
