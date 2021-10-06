<?php

class Engine_Text_BBCode
{
  public static function prepare($text)
  {
    $allowHtml = (bool) Engine_Api::_()->getApi('settings', 'core')->getSetting('forum_html', 0);
    if( $allowHtml ) {
      return $text;
    }
    $allowBbCode = (bool) Engine_Api::_()->getApi('settings', 'core')->getSetting('forum_bbcode', 0);
    if( $allowBbCode ) {
      $text = preg_replace("/&lt;img src=&quot;(.*?)&quot;(.*?)&gt;/i", '[img]\\1[/img]', $text);
      $text = preg_replace("/&lt;img class=&quot;emoticon_img&quot; src=&quot;(.*?)&quot;&gt;/i", '[img alt="emoticon_img"]\\1[/img]', $text);
      return $text;
    }

    return $text;
  }
}
