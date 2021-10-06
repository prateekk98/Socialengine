<?php

require_once(SCAFFOLD_SYSPATH . 'modules/NestedSelectors/NestedSelectors.php');

class EngineNestedSelectors extends NestedSelectors
{
  public static function initialize()
  {
    unset(Scaffold::$modules['NestedSelectors']);
    self::$skip = array_merge(self::$skip, array(
      '@-ms-keyframes',
      '@-moz-keyframes',
      '@-webkit-keyframes',
      '@keyframes'
    ));
  }
}
