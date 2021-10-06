<?php

class Install_Import_Ning_Activities extends Install_Import_Ning_Abstract
{
  protected $_priority = 90;

  protected $_toTable = 'engine4_activity_actions';

  protected $_streamTable = 'engine4_activity_stream';

  public function run()
  {
    $this->_message('(START)', 2);
    $memory = memory_get_usage(true);
    // stats
    $cTotal = 0;
    $cSuccess = 0;
    $cFail = 0;
    $path = $this->getActivitiesDirectoryPath();
    foreach ($this->getActivityFiles($path) as $file) {
      foreach ($this->getActivitiesDataFromFile($file) as $fromKey => $fromDatum) {
        $cTotal++;
        try {
          $this->_translateRow($fromDatum, $fromKey);
          $cSuccess++;
        } catch (Exception $e) {
          $message = $e->getMessage();
          $this->_message($message, 0);
          $cFail++;
        }
      }
    }
    $this->_message(sprintf('Total: %d', $cTotal), 1);
    $this->_message(sprintf('Success: %d', $cSuccess), 1);
    $this->_message(sprintf('Failure: %d', $cFail), 1);
    $this->_message('(END)', 2);
    $memoryUsed = memory_get_usage(true) - $memory;
    $this->_message(sprintf('Additional memory usage: %d bytes', $memoryUsed), 1);
  }

  protected function _translateRow(array $data, $key = null)
  {
    $targetTypes = $data['targetTypes'];
    unset($data['targetTypes']);

    // Insert into feed
    $this->getToDb()->insert($this->getToTable(), $data);
    $action_id = $this->getToDb()->lastInsertId();

    // Insert into stream table
    foreach ($targetTypes as $targetType => $targetIdentity) {
      $this->getToDb()->insert($this->_streamTable, array(
        'target_type' => $targetType,
        'target_id' => $targetIdentity,
        'subject_type' => $data['subject_type'],
        'subject_id' => $data['subject_id'],
        'object_type' => $data['object_type'],
        'object_id' => $data['object_id'],
        'type' => $data['type'],
        'action_id' => $action_id,
      ));
    }
  }

  public function beforeStart()
  {
    $this->removeDirectoryAndFile($this->getActivitiesDirectoryPath());
  }

  public function reset()
  {
    parent::reset();
    $this->_truncateTable($this->getToDb(), $this->_streamTable);
  }

  protected function getActivityFiles($path)
  {
    $files = array();
    $scanDir = array_diff(scandir($path), array('..', '.'));
    foreach ($scanDir as $dir) {
      $absPath = $path . $dir;
      if (is_dir($absPath)) {
        $files = array_merge($files, $this->getActivityFiles($absPath . DIRECTORY_SEPARATOR));
        continue;
      }
      $files[] = $absPath;
    }
    return $files;
  }

  protected function getActivitiesDataFromFile($fromFileAbs)
  {
    // Import
    $fromData = file_get_contents($fromFileAbs);
    // Decode
    $fromData = Zend_Json::decode($fromData);
    if (!is_array($fromData)) {
      throw new Engine_Exception('Data could not be decoded');
    }
    ksort($fromData);
    return $fromData;
  }

}
