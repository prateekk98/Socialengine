<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: ViewMore.php 10053 2013-06-12 02:12:53Z john $
 */

/**
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_View_Helper_ViewMore extends Zend_View_Helper_HtmlElement
{
  protected $_moreLength = 255; // Note: truncation at 255 + 4 = 259 (for " ...")
  protected $_lessLength = 511;
  protected $_maxLength = 1027;
  protected $_fudgesicles = 10;
  protected $_maxLineBreaks = 4; // Truncate early if more than this nl
  protected $_tag = 'span';

  public function viewMore($string, $moreLength = null, $maxLength = null, 
      $lessLength = null, $nl2br = true)
  {
    if( !is_numeric($moreLength) || $moreLength <= 0 ) {
      $moreLength = $this->_moreLength;
    }
    if( !is_numeric($maxLength) || $maxLength <= 0 ) {
      $maxLength = $this->_maxLength;
    }
    if( !is_numeric($lessLength) || $lessLength <= 0 ) {
      $lessLength = $this->_lessLength;
    }
    
    // If using line breaks, ensure that there are not too many line breaks
    if( $nl2br ) {
      $string = trim(preg_replace('/(\r\n|\n\r|\r|\n)/', "\n", $string));
      if( ($c = substr_count($string, "\n")) > $this->_maxLineBreaks) {
        $pos = 0;
        for( $i = 0; $i < $this->_maxLineBreaks; $i++ ) {
          $pos = strpos($string, "\n", $pos + 1);
        }
        if( $pos <= 0 || !is_int($pos) ) {
          $pos = null;
        }
        if( $pos && $pos < $moreLength ) {
          $moreLength = $pos;
        }
      }
    }
    
    // If length is less than max len, just return
    $strLen = Engine_String::strlen($string);
    if( $strLen <= $moreLength + $this->_fudgesicles ) {
      if( $nl2br ) {
        return nl2br($string);
      } else {
        return $string;
      }
    }
    
    // Otherwise truncate
    if( $strLen >= $maxLength ) {
      $strLen = $maxLength;
      $string = $this->truncateHtml($string, $strLen, $this->view->translate('... &nbsp;'));
    }

    $shortText = $this->truncateHtml($string, $moreLength, '');
    if ($string === $shortText) {
      return $string;
    }
    $fullText = $string;
    
    // Do nl2br
    if( $nl2br ) {
      $shortText = nl2br($shortText);
      $fullText = nl2br($fullText);
    }
    
    $onclick = <<<EOF
var me = $(this).getParent(), other = $(this).getParent().getNext(), fn = function() {
  me.style.display = 'none';
  other.style.display = '';
};
fn();
setTimeout(fn, 0);
EOF;
    $content = '<'
      . $this->_tag
      . ' class="view_more"'
      . '>'
      . $shortText
      . $this->view->translate('... &nbsp;')
      . '<a class="view_more_link" href="javascript:void(0);" onclick="' . htmlspecialchars($onclick) . '">'
      . $this->view->translate('more')
      . '</a>'
      . '</'
      . $this->_tag
      . '>'
      . '<'
      . $this->_tag
      . ' class="view_more"'
      . ' style="display:none;"'
      . '>'
      . $fullText
      . ' &nbsp;'
      ;

    if( $strLen >= $lessLength ) {
      $onclick = <<<EOF
var me = $(this).getParent(), other = $(this).getParent().getPrevious(), fn = function() {
  me.style.display = 'none';
  other.style.display = '';
};
fn();
setTimeout(fn, 0);
EOF;
      $content .= '<a class="view_less_link" href="javascript:void(0);" onclick="' . htmlspecialchars($onclick) . '">'
          . $this->view->translate('less')
          . '</a>';
    }

    $content .= '</'
      . $this->_tag
      . '>'
      ;

    return $content;
  }

  public function setMoreLength($length)
  {
    if( is_numeric($length) && $length > 0 )
    {
      $this->_moreLength = $length;
    }

    return $this;
  }

  public function setMaxLength($length)
  {
    if( is_numeric($length) && $length > 0 )
    {
      $this->_maxLength = $length;
    }

    return $this;
  }

  public function truncateHtml($text, $length = 100, $ending = '...')
  {
    $stringLength = strlen(preg_replace('/<.*?>/', '', $text));
    if ($stringLength <= $length) {
      return $text;
    }

    $openTags = array();
    $considerHtml = Engine_String::strlen($text) != $stringLength;
    if ($considerHtml) {
      // split tags
      preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);
      $totalLength = strlen($ending);
      $truncate = '';
      foreach ($lines as $line) {
        // if there is any html-tag in this line, handle it and add it (uncounted) to the output
        if (!empty($line[1])) {
          if (preg_match(
            '/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is',
            $line[1]
          )) {
            // do nothing for closing tag
          } else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line[1], $machedTags)) {
            $pos = array_search($machedTags[1], $openTags);
            // remove matched tag
            if ($pos !== false) {
              unset($openTags[$pos]);
            }
          } else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line[1], $machedTags)) {
            // add opening tag to $openTags
            array_unshift($openTags, strtolower($machedTags[1]));
          }
          $truncate .= $line[1];
        }
        // calculate the length of the plain text part of the line; handle entities as one character
        $contentLength = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', ' ', $line[2]));
        if ($totalLength + $contentLength > $length) {
          // the number of characters which are left
          $left = $length - $totalLength;
          $entitiesLength = 0;
          // search for html entities
          if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|[0-9a-f]{1,6};/i', $line[2], $entities, PREG_OFFSET_CAPTURE)) {
            // calculate the real length of all entities in the legal range
            foreach ($entities[0] as $entity) {
              if ($entity[1] + 1 - $entitiesLength <= $left) {
                $left--;
                $entitiesLength += strlen($entity[0]);
              } else {
                // no more characters left
                break;
              }
            }
          }
          $truncate .= substr($line[2], 0, $left + $entitiesLength);
          // maximum lenght is reached, so get off the loop
          break;
        } else {
          $truncate .= $line[2];
          $totalLength += $contentLength;
        }
        // if the maximum length is reached, get off the loop
        if ($totalLength >= $length) {
          break;
        }
      }
    } else {
      $truncate = substr($text, 0, $length - strlen($ending));
    }
    // if the words shouldn't be cut in the middle...
    // ...search the last occurance of a space...
    $spacepos = strrpos($truncate, ' ');
    if (isset($spacepos)) {
      // ...and cut the text in this position
      $truncate = substr($truncate, 0, $spacepos);
    }
    // add the defined ending to the text
    $truncate .= $ending;
    // close all unclosed html-tags
    foreach ($openTags as $tag) {
      $truncate .= '</' . $tag . '>';
    }
    return $truncate;
  }
}
