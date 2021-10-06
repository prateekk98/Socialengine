<?php

class Paginator
{

  protected static $_nestedBlockPositions = array();
  protected static $_ignoreSelectorPerPage = false;

  static public function flag()
  {
    Scaffold::flag_set('paginator');
    Scaffold::flag_set(sprintf('pagestart=%d', @$_GET['pageStart']));
    Scaffold::flag_set(sprintf('pageend=%d', @$_GET['pageEnd']));
  }

  static public function output()
  {
    $currentStart = isset($_GET['pageStart']) ? (int) $_GET['pageStart'] : null;
    $currentEnd = isset($_GET['pageEnd']) ? (int) $_GET['pageEnd'] : null;

    // Just output substr
    if( $currentStart != null || $currentEnd != null ) {
      Scaffold::$output = substr(Scaffold::$output, $currentStart, $currentEnd - $currentStart);
      return;
    }

    $selectorsPerPage = 4000;
    $cssLength = strlen(Scaffold::$output);
    $cssSelectors = self::_getSelectorCount();
    $avgLengthPerSelector = (int) round($cssLength / $cssSelectors);
    self::$_nestedBlockPositions = self::_parseNestedBlocks();

    // Segment the file
    $segments = array();
    $currentStart = 0;
    $currentPos = 0;
    $currentCount = 0;
    $i = 0;

    do {
      // Get next pos
      $currentPos += round($selectorsPerPage * $avgLengthPerSelector);
      if( $currentPos > $cssLength ) {
        $currentPos = $cssLength;
      } else {
        // Rewind until it's less than selector count
        $ignoreSelectorCounter = 0;
        do {
          $currentPos = round($currentPos - (5 * $avgLengthPerSelector)) - $ignoreSelectorCounter * 500 * $avgLengthPerSelector; // Fudgesicles
          $currentPos = self::_getPreviousSelectorEnd($currentPos - $cssLength, $currentStart);
          $currentCount = self::_getSelectorCount($currentStart, $currentPos - $currentStart);
          if (self::$_ignoreSelectorPerPage ) {
            $ignoreSelectorCounter++;
          }
        } while( $currentCount > $selectorsPerPage && ($ignoreSelectorCounter < 2) );
      }

      $segments[] = $currentPos;
      $currentStart = $currentPos;
      $i++;
    } while( $currentCount > 0 && $currentPos < $cssLength && $i < 100 );

    // Only do stuff if there is more than one segment
    if( count($segments) > 1 ) {
      array_pop($segments);
      // Generate imports
      $urlInfo = parse_url($_SERVER['REQUEST_URI']);
      $urlQueryArr = array();
      parse_str($urlInfo['query'], $urlQueryArr);

      $importStr = '';
      $lastEnd = -1;
      foreach( $segments as $segmentEnd ) {
        $urlQueryArr['pageStart'] = $lastEnd + 1;
        $urlQueryArr['pageEnd'] = $segmentEnd + 1;
        $url = $urlInfo['path'] . '?' . http_build_query($urlQueryArr);
        $importStr .= '@import "' . $url . '";' . "\r\n";
        $lastEnd = $segmentEnd;
      }
      // Truncate CSS, only get last segment
      Scaffold::$output = substr(Scaffold::$output, $lastEnd + 1);
      Scaffold::$output = $importStr . "\r\n" . "\r\n" . Scaffold::$output;
    }
  }

  static protected function _getSelectorCount($start = 0, $length = null)
  {
    if( null === $length ) {
      $length = strlen(Scaffold::$output) - $start;
    }
    return substr_count(Scaffold::$output, '{', $start, $length) +
      substr_count(Scaffold::$output, ',', $start, $length);
  }

  static protected function _getPreviousSelectorEnd($offset = null, $start = null)
  {
    self::$_ignoreSelectorPerPage = false;
    $pos = strrpos(Scaffold::$output, '}', $offset);
    if( false === $pos || $pos < $start ) {
      return false;
    }

    foreach( self::$_nestedBlockPositions as $nestedBlock ) {
      $startMedia = $nestedBlock['start'];
      $endMedia = $nestedBlock['end'];
      if( !($startMedia >= $start && $endMedia > $pos) ) {
        continue;
      }

      $pos = $endMedia;
      // We need to ignore the selector per page because we want to added complete media
      self::$_ignoreSelectorPerPage = true;
      break;
    }

    return $pos;
  }

  static protected function _parseNestedBlocks()
  {
    $nestedBlocks = array();
    $css = Scaffold::$output;
    $nestedSelectors = array('@media', '@-ms-keyframes', '@-moz-keyframes', '@-webkit-keyframes', '@keyframes');
    foreach($nestedSelectors as $nestedSelector) {
      $start = 0;
      while( ($start = strpos($css, $nestedSelector, $start)) !== false ) {
        $pos = strpos($css, '{', $start);
        if( $pos === false ) {
          continue;
        }
        $stack = array();
        array_push($stack, $css[$pos]);
        $pos++;

        while( !empty($stack) ) {
          if( $css[$pos] == '{' ) {
            array_push($stack, '{');
          } elseif( $css[$pos] == '}' ) {
            array_pop($stack);
          }
          $pos++;
        }
        $nestedBlocks[] = array('start' => $start, 'end' => $pos - 1);
        $start = $pos;
      }
    }
    return $nestedBlocks;
  }
}
