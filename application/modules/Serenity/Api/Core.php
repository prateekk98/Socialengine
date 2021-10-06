<?php

class Serenity_Api_Core extends Core_Api_Abstract {

  public function getContantValueXML($key) {
    $filePath = APPLICATION_PATH . "/application/settings/constants.xml";
    $results = simplexml_load_file($filePath);
    $xmlNodes = $results->xpath('/root/constant[name="' . $key . '"]');
    $nodeName = @$xmlNodes[0];
    $value = @$nodeName->value;
    return $value;
  }

  public function readWriteXML($keys, $value, $default_constants = null) {

    $filePath = APPLICATION_PATH . "/application/settings/constants.xml";
    $results = simplexml_load_file($filePath);

    if (!empty($keys) && !empty($value)) {
        $contactsThemeArray = array($keys => $value);
    } elseif (!empty($keys)) {
        $contactsThemeArray = array($keys => '');
    } elseif ($default_constants) {
        $contactsThemeArray = $default_constants;
    }

    foreach ($contactsThemeArray as $key => $value) {
      $xmlNodes = $results->xpath('/root/constant[name="' . $key . '"]');
      $nodeName = @$xmlNodes[0];
      $params = json_decode(json_encode($nodeName));
      $paramsVal = @$params->value;
      if ($paramsVal && $paramsVal != '' && $paramsVal != null) {
          $nodeName->value = $value;
      } else {
          $entry = $results->addChild('constant');
          $entry->addChild('name', $key);
          $entry->addChild('value', $value);
      }
    }
    return $results->asXML($filePath);
  }
}
