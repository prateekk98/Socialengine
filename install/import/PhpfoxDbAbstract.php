<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    PhpfoxDbAbstract.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
abstract class Install_Import_PhpfoxDbAbstract extends Install_Import_PhpfoxAbstract
{

  protected $_fromDb;
  protected $_fromTable;
  protected $_fromJoins;
  protected $_fromJoinTable;
  protected $_fromJoinCondition;
  protected $_fromWhere;
  protected $_fromTableExists;
  protected $_selectCount = 100;
  protected $_runCount = 0;
  protected $_runComplete = false;
  protected $_successCount = 0;
  protected $_failureCount = 0;
  protected $_totalFromRecords;
  protected $_fromOrderBy = null;
  protected $_fromGroupBy = null;
  protected $_fileSize = null;
  protected $_messageCount = 0;

  public function __sleep()
  {
    return array_merge(parent::__sleep(), array(
      '_fromTable', '_fromJoins',
      '_fromJoinTable', '_fromJoinCondition', '_fromWhere', '_fromTableExists',
      '_selectCount', '_runCount', '_runComplete', '_successCount',
      '_failureCount', '_totalFromRecords', '_fromOrderBy', '_fromGroupBy', '_fileSize', '_messageCount'
    ));
  }

  public function setFromDb(Zend_Db_Adapter_Abstract $fromDb)
  {
    $this->_fromDb = $fromDb;
    return $this;
  }

  /**
   * @return Zend_Db_Adapter_Abstract
   */
  public function getFromDb()
  {
    if( null === $this->_fromDb ) {
      throw new Engine_Exception('No from database');
    }
    return $this->_fromDb;
  }

  public function setFromTable($fromTable)
  {
    $this->_fromTable = $fromTable;
    return $this;
  }

  public function getFromTable()
  {
    if( null === $this->_fromTable ) {
      throw new Engine_Exception('No from table');
    }
    return $this->_fromTable;
  }

  public function getFromTableExists()
  {
    if( null === $this->_fromTable ) {
      return false;
    }
    if( null === $this->_fromTableExists ) {
      $this->_fromTableExists = $this->_tableExists($this->getFromDb(), $this->getFromTable());
    }
    return $this->_fromTableExists;
  }

  public function setSelectCount($selectCount)
  {
    $this->_selectCount = (int) $selectCount;
    return $this;
  }

  public function getSelectCount()
  {
    if( !is_int($this->_selectCount) || $this->_selectCount <= 0 ) {
      $this->_selectCount = 100;
    }
    return $this->_selectCount;
  }

  public function getRunCount()
  {
    return $this->_runCount;
  }

  public function getTotalFromRecords()
  {
    return $this->_totalFromRecords;
  }

  // Custom

  public function init()
  {
    $this->_initPre();

    if( $this->_toTableTruncate ) {
      $this->_truncateToTable();
    }

    $this->_initPost();

    // Try to get total rows now
    try {
      if( null === $this->_totalFromRecords ) {
        if( null !== $this->_fromTable ) {
          $select = $this->getFromDb()->select()
            ->from($this->getFromTable(), new Zend_Db_Expr('COUNT(*)'));

          if( $this->_fromWhere && is_array($this->_fromWhere) ) {
            foreach( $this->_fromWhere as $cond => $value ) {

              if( !is_null($value) )
                $select->where($cond, $value);
              else
                $select->where($cond);
            }
          }

          $this->_totalFromRecords = (int) $select
              ->query()
              ->fetchColumn(0);
        }
      }
    } catch( Exception $e ) {
      // Silence
    }
  }

  public function run()
  {
    $this->_message('(START)', 2);

    // This is to trick things that overrode _run to be complete
    $this->_isComplete = true;

    try {

      // Only run this when starting
      if( null === $this->getBatchCount() || $this->_runCount == 0 ) {
        $toDb = $this->getToDb();
        if( !isset($this->_isTableExist) || is_null($this->_isTableExist) )
          $this->_isTableExist = array();

        foreach( $this->_isTableExist as $table ) {
          if( !$this->_tableExists($toDb, $table) ) {
            $msg = '@@@@@One of the source tables does not exist (%s). This usually means you did not have the plugin installed.$$$key$$$' . $table;
            if( count($this->_warningMessage) > 0 )
              $msg = "@@@@@" . $this->_warningMessage['value'] . '$$$key$$$' . $this->_warningMessage['key'];
            $this->_warning(sprintf($msg, $table), 0);
            return false;
          }
        }
        $this->_runPre();
      }

      // Main
      $this->_run();

      // Only run this when ending
      if( null === $this->getBatchCount() || $this->_isComplete ) {
        $this->_runPost();
      }
    } catch( Exception $e ) {
      $this->_error($e->getMessage(), 0);
    }

    $this->_message('(END)', 2);
  }

  protected function _run()
  {
    // We're done
    if( $this->_runComplete ) {
      return;
    }

    // Get from db/table, check if it exists
    try {
      $fromDb = $this->getFromDb();
      $fromTable = $this->getFromTable();
    } catch( Exception $e ) {
      return $this->_error($e);
    }

    $fromTables = array();
    $fromTables[] = $this->_fromJoinTable;
    $fromTables = array_merge($fromTables, array_keys((array) $this->_fromJoins));
    $fromTables[] = $fromTable;
    $fromTables = array_filter($fromTables);

    if( !$fromTables || empty($fromTables) || !is_array($fromTables) || count($fromTables) < 1 ) {
      $this->_error('No from table specified.', 0);
      return;
    }

    if( !$this->_tableExists($fromDb, $fromTables) ) {

      $msg = '@@@@@One of the source tables does not exist (%s). This usually means you did not have the plugin installed.$$$key$$$' . join('_', $fromTables);
      if( count($this->_warningMessage) > 0 )
        $msg = "@@@@@" . $this->_warningMessage['value'] . '$$$key$$$' . $this->_warningMessage['key'];
      $this->_warning(sprintf($msg, join(', ', $fromTables)), 0);
      return;
    }

    // Get to db/table, check if it exists
    try {
      $toDb = $this->getToDb();
      $toTable = $this->getToTable();
    } catch( Exception $e ) {
      return $this->_error($e);
    }

    if( !$this->_tableExists($toDb, $toTable) ) {

      $msg = '@@@@@One of the source tables does not exist (%s). This usually means you did not have the plugin installed.$$$key$$$' . $toTable;
      if( count($this->_warningMessage) > 0 )
        $msg = "@@@@@" . $this->_warningMessage['value'] . '$$$key$$$' . $this->_warningMessage['key'];
      $this->_warning(sprintf($msg, $toTable), 0);
      return;
    }

    $this->_runComplete = $this->_isComplete = false;

    // Get a count of rows
    if( null === $this->_totalFromRecords ) {
      $select = $this->getFromDb()->select()
        ->from($this->getFromTable(), new Zend_Db_Expr('COUNT(*)'));

      if( $this->_fromWhere && is_array($this->_fromWhere) ) {
        foreach( $this->_fromWhere as $cond => $value ) {
          $select->where($cond, $value);
        }
      }

      $this->_totalFromRecords = (int) $select
          ->query()
          ->fetchColumn(0);
    }

    // stats
    $cTotal = 0;
    $cSuccess = 0;
    $cFail = 0;

    // Import
    $select = $fromDb->select();
    $select->from($fromTable);
    if( $this->_fromJoins && is_array($this->_fromJoins) ) {
      foreach( $this->_fromJoins as $table => $condition ) {
        $select->joinLeft($table, $condition);
      }
    } elseif( $this->_fromJoinTable && $this->_fromJoinCondition ) {
      $select->joinLeft($this->_fromJoinTable, $this->_fromJoinCondition);
    }

    if( $this->_fromWhere && is_array($this->_fromWhere) ) {
      foreach( $this->_fromWhere as $cond => $value ) {
        $select->where($cond, $value);
      }
    }
    if( !empty($this->_fromOrderBy) && is_array($this->_fromOrderBy) && isset($this->_fromOrderBy[0]) && isset($this->_fromOrderBy[1]) && is_array($this->_fromOrderBy[0]) ) {
      if( !in_array(strtolower($this->_fromOrderBy[1]), array('asc', 'desc')) )
        $this->_fromOrderBy[1] = 'ASC';
      $select->order($this->_fromOrderBy[0], $this->_fromOrderBy[1]);
    }
    if( !empty($this->_fromGroupBy) && is_array($this->_fromGroupBy) && count($this->_fromGroupBy) > 0 ) {
      $select->group($this->_fromGroupBy);
    }
    // Get ready to select
    $break = false;
    $currentRunCount = 0;
    $batchCount = 0;
    $runStartTime = time();
    $maxAllowedTime = $this->getParam('maxAllowedTime');

    $this->_message($select->__toString(), 2);

    // Select in batches of $this->_selectCount
    do {
      $batchStartTime = time();

      // Select
      $tmpSelect = clone $select;
      $dataSet = $tmpSelect->limit($this->getSelectCount(), $this->_runCount)
        ->query(Zend_Db::FETCH_ASSOC)
        ->fetchAll(Zend_Db::FETCH_ASSOC);

      // Nothing left
      if( !is_array($dataSet) || empty($dataSet) ) {
        $this->_runComplete = $this->_isComplete = true;
        $break = true;
        continue;
      }

      // Iterate over each item in batch
      foreach( $dataSet as $data ) {
        set_time_limit(0);
        ini_set('max_execution_time', 600);
        $cTotal++;

        try {

          $newData = $this->_translateRow($data);

          if( !empty($newData) && is_array($newData) ) {
            $toDb->insert($toTable, $newData);
          }

          $cSuccess++;
        } catch( Exception $e ) {

          $this->_error($e);
          $cFail++;
        }
      }

      // Prepare next round
      $num = count($dataSet);
      $this->_runCount += $num;
      $currentRunCount += $num;
      $batchCount++;

      // Ran out of stuff
      if( $num < $this->getSelectCount() ) {
        $this->_runComplete = $this->_isComplete = true;
        $break = true;
      }
      // Finished the batch
      elseif( null !== $this->getBatchCount() && $currentRunCount >= $this->getBatchCount() ) {
        $break = true;
      }

      // Check maxAllowedTime
      if( $maxAllowedTime > 0 ) {
        $timeSinceStart = time() - $runStartTime;
        $avgTimeForBatch = $timeSinceStart / $batchCount;
        $approxNextBatchEnd = $timeSinceStart + $avgTimeForBatch;
        // Break if we might go over
        if( $approxNextBatchEnd > $maxAllowedTime ) {
          $break = true;
        }
      }
    } while( !$break );

    $this->_successCount += $cSuccess;
    $this->_failureCount += $cFail;

    // If we're in batch mode, strip success and failure messages and re-add
    if( null !== $this->getBatchCount() ) {
      foreach( $this->_messages as $index => $message ) {
        if( strpos($message, 'Success - ') !== false || strpos($message, 'Failure - ') !== false ) {
          unset($this->_messages[$index]);
        }
      }
      $this->_messages = array_values($this->_messages);
    }

    $this->_message(sprintf('Average batch time: %d, Total time on batches: %d, Max allowed time: %d', @$avgTimeForBatch, (time() - $runStartTime), $maxAllowedTime), 2);

    if( $this->_failureCount <= 0 ) {
      if( null !== $this->_totalFromRecords ) {
        $this->_message(sprintf('Success - %1$d records imported of %2$d total records.', $this->_successCount, $this->_totalFromRecords), 1);
      } else {
        $this->_message(sprintf('Success - %d records imported.', $this->_successCount), 1);
      }
    } else {
      if( null !== $this->_totalFromRecords ) {
        $this->_warning(sprintf('Failure - %1$d records imported, %2$d records failed of %3$d total records.', $this->_successCount, $this->_failureCount, $this->_totalFromRecords), 0);
      } else {
        $this->_warning(sprintf('Failure - %1$d records imported, %2$d records failed.', $this->_successCount, $this->_failureCount), 0);
      }
    }
  }
}
