<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

/**
* Cronjob-administration
*
* @package Papaya
* @subpackage Core
*/
class base_cronjobs extends base_db {

  /**
  * Parameters
  * @var array $params
  */
  var $params = NULL;
  /**
  * Parameter name
  * @var string $paramName
  */
  var $paramName = 'cron';

  /**
  * Papaya database table cronjobs
  * @var string $tableCronjobs
  */
  var $tableCronjobs = PAPAYA_DB_TBL_CRONJOBS;
  /**
  * Papaya database table modules
  * @var string $tableModules
  */
  var $tableModules = PAPAYA_DB_TBL_MODULES;

  /**
  * Cronjobs
  * @var array $cronjobs
  */
  var $cronjobs = NULL;

  /**
   * @var \Papaya\Template
   */
  public $layout;

  /**
   * @var array
   */
  public $jobModules = array();

  /**
   * @var array
   */
  public $cronModules = array();

  /**
   * @var base_dialog
   */
  private $propertyDialog = NULL;

  /**
   * @var array
   */
  private $cronjob;

  /**
  * Initialisize function
  *
  * @param mixed $paramName optional, default value NULL
  * @access public
  */
  function initialize($paramName = NULL) {
    if (isset($paramName)) {
      $this->paramName = $paramName;
    }
    $this->sessionParamName = 'PAPAYA_SESS_'.$this->paramName;
    $this->initializeParams();
    $this->sessionParams = $this->getSessionValue($this->sessionParamName);
    $this->initializeSessionParam('id');
    $this->initializeSessionParam('mode');
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  /**
  * Load function
  *
  * @param integer $id
  * @access public
  * @return boolean
  */
  function load($id) {
    unset($this->cronjob);
    $sql = "SELECT cj.cronjob_id, cj.cronjob_active, cj.cronjob_title,
                   cj.cronjob_description, cj.cronjob_lastexec, cj.cronjob_nextexec,
                   cj.cronjob_start, cj.cronjob_end,
                   cj.cron_module_guid, cj.cron_data,
                   cj.job_module_guid, cj.job_data,
                   cm.module_class AS cron_module_class,
                   cm.module_title AS cron_module_title,
                   cm.module_path AS cron_module_path,
                   cm.module_file AS cron_module_file,
                   jm.module_class AS job_module_class,
                   jm.module_title AS job_module_title,
                   jm.module_path AS job_module_path,
                   jm.module_file AS job_module_file
              FROM %s cj
              LEFT OUTER JOIN %s cm
                ON (cj.cron_module_guid = cm.module_guid AND cm.module_active = 1)
              LEFT OUTER JOIN %s jm
                ON (cj.job_module_guid = jm.module_guid AND jm.module_active = 1)
             WHERE cronjob_id = '%d'";
    $params = array($this->tableCronjobs, $this->tableModules, $this->tableModules, $id);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $row['cron_module_file'] = $row['cron_module_path'].$row['cron_module_file'];
        $row['job_module_file'] = $row['job_module_path'].$row['job_module_file'];
        $row['cronjob_start_str'] = \Papaya\Utility\Date::timestampToString(
          $row['cronjob_start'], TRUE, FALSE
        );
        $row['cronjob_end_str'] = \Papaya\Utility\Date::timestampToString(
          $row['cronjob_end'], TRUE, FALSE
        );
        $this->cronjob = $row;
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
  * Get next cronjob
  *
  * @access public
  * @return mixed FALSE, integer number of affected rows or database result object
  */
  function getNext() {
    $sql = "SELECT cronjob_id, cronjob_nextexec
              FROM %s
             WHERE cronjob_active = 1 AND cronjob_nextexec <= '%d'
             ORDER BY cronjob_nextexec ASC";
    $params = array($this->tableCronjobs, time());
    if ($res = $this->databaseQueryFmt($sql, $params, 1)) {
      if ($row = $res->fetchRow()) {
        return $row[0];
      }
    }
    return FALSE;
  }

  /**
   * Check a given id is an active cronjob
   *
   * @param integer $cronjobId
   * @param bool $activeOnly
   * @access public
   * @return integer|FALSE
   */
  function checkJob($cronjobId, $activeOnly = TRUE) {
    $sql = "SELECT cronjob_id
              FROM %s
             WHERE cronjob_id = '%d'";
    if ($activeOnly) {
      $sql .= " AND cronjob_active = 1";
    }
    $params = array($this->tableCronjobs, (int)$cronjobId);
    if ($res = $this->databaseQueryFmt($sql, $params, 1)) {
      if ($row = $res->fetchRow()) {
        return (int)$row[0];
      }
    }
    return FALSE;
  }

  /**
  * Save changes of cronjob
  *
  * @access public
  * @return integer|array|FALSE integer number of affected rows or database result object
  */
  function save() {
    $values = array(
      'cronjob_title' => $this->params['cronjob_title'],
      'cronjob_description' => $this->params['cronjob_description'],
      'cronjob_start' => $this->params['cronjob_start'],
      'cronjob_end' => $this->params['cronjob_end'],
      'cron_module_guid' => $this->params['cron_module_guid'],
      'job_module_guid' => $this->params['job_module_guid'],
      'cronjob_active' => 0,
    );
    if ($this->cronjobs[$this->params['id']]['cronjob_active'] == 1) {
      $this->logMsg(
        MSG_INFO,
        PAPAYA_LOGTYPE_CRONJOBS,
        'Cronjob disabled',
        sprintf(
          'The cronjob "%s" has been disabled.',
          $this->cronjobs[$this->params['id']]['cronjob_title']
        )
      );
    }
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableCronjobs, $values, 'cronjob_id', (int)$this->params['id']
    );
  }

  /**
  * Save cronjob data
  *
  * @param array $data
  * @access public
  * @return mixed FALSE, integer number of affected rows or database result object
  */
  function saveCronData($data) {
    $values = array (
      'cron_data' => $data,
      'cronjob_active' => '0'
    );
    if ($this->cronjobs[$this->params['id']]['cronjob_active'] == 1) {
      $this->logMsg(
        MSG_INFO,
        PAPAYA_LOGTYPE_CRONJOBS,
        'Cronjob disabled',
        sprintf(
          'The cronjob "%s" has been disabled.',
          $this->cronjobs[$this->params['id']]['cronjob_title']
        )
      );
    }
    $updated = FALSE !== $this->databaseUpdateRecord(
      $this->tableCronjobs, $values, 'cronjob_id', (int)$this->params['id']
    );
    if ($updated) {
      $this->loadList();
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Save cronjob job data
  *
  * @param array $data
  * @access public
  * @return mixed FALSE, integer number of affected rows or database result object
  */
  function saveJobData($data) {
    $values = array (
      'job_data' => $data,
      'cronjob_active' => '0'
    );
    if ($this->cronjobs[$this->params['id']]['cronjob_active'] == 1) {
      $this->logMsg(
        MSG_INFO,
        PAPAYA_LOGTYPE_CRONJOBS,
        'Cronjob disabled',
        sprintf(
          'The cronjob "%s" has been disabled.',
          $this->cronjobs[$this->params['id']]['cronjob_title']
        )
      );
    }
    $updated = FALSE !== $this->databaseUpdateRecord(
      $this->tableCronjobs, $values, 'cronjob_id', (int)$this->params['id']
    );
    if ($updated) {
      $this->loadList();
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Activate cronjob
  *
  * @param integer $nextExecutionTime
  * @param integer $lastExecutionTime optional, default value 0
  * @param integer|NULL $id optional, default value NULL
  * @access public
  * @return integer|array|FALSE number of affected rows or database result object
  */
  function activate($nextExecutionTime, $lastExecutionTime = 0, $id = NULL) {
    $cronjobId = (isset($id)) ? (int)$id : (int)$this->params['id'];
    $values = array(
      'cronjob_nextexec' => (int)$nextExecutionTime,
      'cronjob_active' => '1'
    );
    if (isset($lastExecutionTime) && $lastExecutionTime > 0) {
      $values['cronjob_lastexec'] = $lastExecutionTime;
    }
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableCronjobs, $values, 'cronjob_id', $cronjobId
    );
  }

  /**
  * Deactivate Cronjob
  *
  * @param integer|NULL $id optional, default value NULL
  * @param integer $lastExecutionTime optional, default value 0
  * @access public
  * @return integer|array|FALSE number of affected rows or database result object
  */
  function deactivate($id = NULL, $lastExecutionTime = 0) {
    $cronjobId = (isset($id)) ? (int)$id : (int)$this->params['id'];
    $values['cronjob_active'] = '0';
    if (isset($lastExecutionTime) && $lastExecutionTime > 0) {
      $values['cronjob_lastexec'] = $lastExecutionTime;
    }
    return FALSE !== $this->databaseUpdateRecord(
      $this->tableCronjobs, $values, 'cronjob_id', $cronjobId
    );
  }

  /**
  * Create cronjob
  *
  * @access public
  * @return integer|array|FALSE number of affected rows or database result object
  */
  function create() {
    $start = time();
    $end = $start + 31536000;
    $values = array(
      'cronjob_title' => $this->_gt('Add cronjob'),
      'cronjob_start' => $start,
      'cronjob_end' => $end,
      'cronjob_active' => '0',
      'cronjob_description' => '',
      'cron_data' => '',
      'job_data' => ''
    );
    if ($this->databaseInsertRecord($this->tableCronjobs, 'cronjob_id', $values)) {
      $sql = "SELECT cronjob_id
                FROM %s
               ORDER BY cronjob_id DESC";
      $params = array($this->tableCronjobs);
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow()) {
          return $row[0];
        }
      }
    }
    return FALSE;
  }

  /**
  * Delete cronjob
  *
  * @access public
  * @return integer|array|FALSE
  */
  function delete() {
    return $this->databaseDeleteRecord(
      $this->tableCronjobs, 'cronjob_id', (int)$this->params['id']
    );
  }

  /**
  * Check cronjob data
  *
  * @access public
  * @return boolean
  */
  function check() {
    $result = TRUE;
    if (empty($this->params['cronjob_title']) ||
        !\Papaya\Filter\Factory::isNotXml($this->params['cronjob_title'], TRUE)) {
      $result = FALSE;
      $this->addMsg(
        MSG_ERROR,
        sprintf($this->_gt('The input in field "%s" is not correct.'), $this->_gt('Title'))
      );
    }
    if (empty($this->params['cronjob_description']) ||
        !\Papaya\Filter\Factory::isNotXml($this->params['cronjob_description'], TRUE)) {
      $result = FALSE;
      $this->addMsg(
        MSG_ERROR,
        sprintf($this->_gt('The input in field "%s" is not correct.'), $this->_gt('Description'))
      );
    }
    if (empty($this->params['cron_module_guid']) ||
        !\Papaya\Filter\Factory::isGuid($this->params['cron_module_guid'], TRUE)) {
      $result = FALSE;
      $this->addMsg(
        MSG_ERROR,
        sprintf($this->_gt('The input in field "%s" is not correct.'), $this->_gt('Time module'))
      );
    }
    if (empty($this->params['job_module_guid']) ||
        !\Papaya\Filter\Factory::isGuid($this->params['job_module_guid'], TRUE)) {
      $result = FALSE;
      $this->addMsg(
        MSG_ERROR,
        sprintf($this->_gt('The input in field "%s" is not correct.'), $this->_gt('Job module'))
      );
    }
    $this->params['cronjob_start'] =
      \Papaya\Utility\Date::stringToTimestamp($this->params['cronjob_start_str']);
    $this->params['cronjob_end'] =
      \Papaya\Utility\Date::stringToTimestamp($this->params['cronjob_end_str']);
    if ($this->params['cronjob_end'] <= $this->params['cronjob_start']) {
      $result = FALSE;
      $this->addMsg(MSG_ERROR, $this->_gt('Invalid time frame.'));
    } elseif ($this->params['cronjob_end'] <= time()) {
      $result = FALSE;
      $this->addMsg(MSG_ERROR, $this->_gt('Invalid time frame.'));
    }
    return $result;
  }

  /**
  * Load list
  *
  * @access public
  */
  function loadList() {
    unset($this->cronjobs);
    $sql = "SELECT cronjob_id,  cronjob_active, cronjob_title, cronjob_nextexec
              FROM %s
             ORDER BY cronjob_title, cronjob_id DESC";
    if ($res = $this->databaseQueryFmt($sql, array($this->tableCronjobs))) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        $this->cronjobs[$row['cronjob_id']] = $row;
      }
    }
  }

  /**
  * Load modules list
  *
  * @access public
  */
  function loadModulesList() {
    unset($this->cronModules);
    unset($this->jobModules);
    $sql = "SELECT module_guid, module_type, module_class, module_file,
                   module_path, module_title
              FROM %s
             WHERE ((module_type = 'cronjob') OR (module_type = 'time'))
               AND module_active = 1";
    $params = array($this->tableModules);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        switch ($row['module_type']) {
        case 'cronjob' :
          $this->jobModules[$row['module_guid']] = $row;
          break;
        case 'time' :
          $this->cronModules[$row['module_guid']] = $row;
          break;
        }
      }
    }
  }

  /**
  * Basic method for handling parameters
  *
  * @access public
  */
  function execute() {
    $this->loadList();
    if (!isset($this->params['cmd'])) {
      $this->params['cmd'] = '';
    }
    switch ($this->params['cmd']) {
    case 'save' :
      if (isset($this->params['id']) && ($this->params['id'] > 0)) {
        if ($this->check()) {
          if ($this->save()) {
            $this->addMsg(
              MSG_INFO,
              $this->_gt('Changes saved.').' - '.$this->_gt('Cronjob is deactivated.')
            );
          } else {
            $this->addMsg(
              MSG_ERROR,
              $this->_gt('Database Error').' - '.$this->_gt('Changes not saved.')
            );
          }
        }
      }
      break;
    case 'add' :
      if ($newId = $this->create()) {
        $this->addMsg(MSG_INFO, $this->_gt('Cronjob created.'));
        $this->params['id'] = $newId;
        $this->params['mode'] = 0;
        $this->initializeSessionParam('id');
        $this->initializeSessionParam('mode');
      } else {
        $this->addMsg(
          MSG_ERROR,
          $this->_gt('Database Error').' - '. $this->_gt('Could not create cronjob.')
        );
      }
      break;
    case 'delete' :
      if (isset($this->params['id']) && ($this->params['id'] > 0) &&
          isset($this->params['delconfirm'])) {
        if ($this->delete((int)$this->params['id'])) {
            $this->addMsg(MSG_INFO, $this->_gt('Cronjob deleted.'));
        } else {
          $this->addMsg(
            MSG_ERROR,
            $this->_gt('Database Error').' - '.$this->_gt('Could not delete cronjob.')
          );
        }
      }
      break;
    case 'activate' :
      if (isset($this->params['id'])) {
        $this->load((int)$this->params['id']);
        $dialog = $this->initializeActivateForm();
        if ($dialog->checkDialogInput()) {
          $nextExecutionTime = \Papaya\Utility\Date::stringToTimestamp($dialog->data['nextexec']);
          if ($this->activate($nextExecutionTime, 0, (int)$this->params['id'])) {
            $this->addMsg(MSG_INFO, $this->_gt('Cronjob activated'));
          } else {
            $this->addMsg(MSG_ERROR, $this->_gt('Could not activate cronjob.'));
          }
        }
      }
      break;
    case 'deactivate' :
      if (isset($this->params['id'])) {
        $this->load((int)$this->params['id']);
        if ($this->deactivate((int)$this->params['id'])) {
            $this->addMsg(MSG_INFO, $this->_gt('Cronjob deactivated'));
        } else {
          $this->addMsg(MSG_ERROR, $this->_gt('Could not deactivate cronjob.'));
        }
      }
      break;
    case 'delete_pid' :
      $this->deletePidFile();
      break;
    }
    $this->loadList();
    $this->loadModulesList();
    if (isset($this->params['id']) &&
        isset($this->cronjobs[(int)$this->params['id']])) {
      $this->load((int)$this->params['id']);
    }
    $this->setSessionValue($this->sessionParamName, $this->sessionParams);
  }

  /**
   * Execute job
   *
   * @param integer $id job id
   * @param bool $verbose
   * @access public
   */
  function executeJob($id, $verbose = TRUE) {
    echo "Loading: ID $id \n";
    if ($this->load($id)) {
      echo "Loaded: ".$this->cronjob['cronjob_title'].LF;
      $this->logMsg(
        MSG_INFO,
        PAPAYA_LOGTYPE_CRONJOBS,
        sprintf(
          'Cronjob "%s" (%s) loaded.',
          $this->cronjob['cronjob_title'],
          $this->cronjob['cronjob_id']
        )
      );
      $deactivate = FALSE;
      $reason = '';
      $time = time();
      if ($this->cronjob['cronjob_nextexec'] > 0) {
        $jobModule = $this->papaya()->plugins->get(
          $this->cronjob['job_module_guid'],
          $this,
          $this->cronjob['job_data']
        );
        if (isset($jobModule) && is_object($jobModule)) {
          echo "Module initialized: ".$this->cronjob['job_module_class'].LF;
          $jobModule->cron = $this;
          if (0 === ($msg = $jobModule->execute())) {
            echo "Job successfully executed.\n";
            $this->logMsg(
              MSG_INFO,
              PAPAYA_LOGTYPE_CRONJOBS,
              sprintf(
                'Cronjob "%s" (%s) executed.',
                $this->cronjob['cronjob_title'],
                $this->cronjob['cronjob_id']
              )
            );
          } else {
            $this->logMsg(
              MSG_ERROR,
              PAPAYA_LOGTYPE_CRONJOBS,
              sprintf(
                'Cronjob "%s" (%s) failed.',
                $this->cronjob['cronjob_title'],
                $this->cronjob['cronjob_id']
              ),
              sprintf(
                'Cronjob "%s" (%s) failed.'."\n\n%s",
                $this->cronjob['cronjob_title'],
                $this->cronjob['cronjob_id'],
                $msg
              )
            );
            echo "Job failed.\n";
          }
          if ($this->updateActive($id, $time)) {
            echo "Time updated.\n";
          } else {
            if ($this->deactivate($id, $time)) {
              echo "No Time/Data - Job deactivated.\n";
            } else {
              echo "No Time/Data - Failed to deactivate Job.\n";
            }
          }
        } else {
          $deactivate = TRUE;
          $reason = sprintf(
            $this->_gt('Could not initialize job module "%s" (%s).'),
            $this->cronjob['job_module_class'],
            $this->cronjob['job_module_file']
          );
        }
      } else {
        $deactivate = TRUE;
        $reason = $this->_gt('Invalid time.');
      }

      if ($deactivate) {
        if ($this->deactivate($id, $time)) {
          $this->logMsg(
            MSG_ERROR,
            PAPAYA_LOGTYPE_CRONJOBS,
            sprintf(
              'Cronjob "%s" (%s) failed.',
              $this->cronjob['cronjob_title'],
              $this->cronjob['cronjob_id']
            ),
            sprintf(
              'Cronjob "%s" (%s) failed. %s Cronjob deactivated.',
              $this->cronjob['cronjob_title'],
              $this->cronjob['cronjob_id'],
              $reason
            )
          );
        } else {
          $this->logMsg(
            MSG_ERROR,
            PAPAYA_LOGTYPE_CRONJOBS,
            sprintf(
              'Cronjob "%s" (%s) failed.',
              $this->cronjob['cronjob_title'],
              $this->cronjob['cronjob_id']
            ),
            sprintf(
              'Cronjob "%s" (%s) failed. %s Failed to deactivate cronjob.',
              $this->cronjob['cronjob_title'],
              $this->cronjob['cronjob_id'],
              $reason
            )
          );
        }
      }
    } else {
      $this->logMsg(
        MSG_ERROR,
        PAPAYA_LOGTYPE_CRONJOBS,
        sprintf(
          'Could not load cronjob "%s" (%s).',
          $this->cronjob['cronjob_title'],
          $this->cronjob['cronjob_id']
        )
      );
      $this->deactivate($id);
    }
  }

  /**
  * Set active
  *
  * @access public
  * @return boolean
  */
  function setActive() {
    if (isset($this->cronjob) && is_array($this->cronjob)) {
      if ($calculatedData = $this->checkCronJobData()) {
        if (($calculatedData['nextexec'] > time()) && ($calculatedData['jobdatamsg'])) {
          if ($this->activate($calculatedData['nextexec'])) {
            $this->logMsg(
              MSG_INFO,
              PAPAYA_LOGTYPE_CRONJOBS,
              'Cronjob activated',
              sprintf(
                'The cronjob "%s" has been activated.',
                $this->cronjob['cronjob_title']
              )
            );
            return TRUE;
          }
        }
      }
    }
    return FALSE;
  }

  /**
  * Update active
  *
  * @param integer|NULL $id optional, default value NULL
  * @param integer $lastExecutionTime optional, default value 0
  * @access public
  * @return boolean
  */
  function updateActive($id = NULL, $lastExecutionTime = 0) {
    if (isset($this->cronjob) && is_array($this->cronjob)) {
      if ($calculatedData = $this->checkCronJobData()) {
        if ($calculatedData['nextexec'] > time()) {
          if ($calculatedData['jobdatamsg']) {
            if ($this->activate($calculatedData['nextexec'], $lastExecutionTime, $id)) {
              return TRUE;
            } else {
              $this->logMsg(
                MSG_INFO,
                PAPAYA_LOGTYPE_CRONJOBS,
                sprintf('updating active cronjob %d failed, activation failed.', $id),
                sprintf(
                  'The activate method returned false. nextexec = %d, lastexec = %d',
                  $calculatedData['nextexec'],
                  $lastExecutionTime
                )
              );
            }
          } else {
            $this->logMsg(
              MSG_INFO,
              PAPAYA_LOGTYPE_CRONJOBS,
              sprintf(
                'updating active cronjob %d failed, result of checkCronJobData erroneous.',
                $id
              ),
              'jobdatamsg is 0.'
            );
          }
        } else {
          $this->logMsg(
            MSG_INFO,
            PAPAYA_LOGTYPE_CRONJOBS,
            sprintf(
              'updating active cronjob %d failed, result of checkCronJobData erroneous.',
              $id
            ),
            'The next execution is not in the future.'
          );
        }
      } else {
        $this->logMsg(
          MSG_INFO,
          PAPAYA_LOGTYPE_CRONJOBS,
          sprintf(
            'updating active cronjob %d failed, data check failed', $id
          ),
          'The checkCronJobData method returned false.'
        );
      }
    } else {
      $this->logMsg(
        MSG_INFO,
        PAPAYA_LOGTYPE_CRONJOBS,
        sprintf(
          'updating active cronjob %d failed, cronjob not found', $id
        ),
        sprintf(
          'The cronjob to be updated wasn\'t loaded "$this->cronjob"'.
          ' for last execution time = %d.',
          $lastExecutionTime
        )
      );
    }
    return FALSE;
  }

  /**
  * Get XML for output
  *
  * @access public
  */
  function getXML() {
    if (isset($this->layout) && is_object($this->layout)) {
      if (isset($this->params['id']) &&
          isset($this->cronjobs[(int)$this->params['id']])) {
        $this->getToolbarXML();
        if (isset($this->params['cmd']) && $this->params['cmd'] == 'execute') {
          $this->getJobExecuteFrame();
        } else {
          if (isset($this->params['cmd']) && $this->params['cmd'] == 'delete' &&
              (!isset($this->params['delconfirm']))) {
            $this->getDelConfirmXML();
          }
          switch($this->params['mode']) {
          case 1 :
            $this->getCronModuleEdit();
            break;
          case 2 :
            $this->getJobModuleEdit();
            break;
          case 3 :
            $this->getActivateFormXML();
            break;
          default :
            $this->getPropertyFormXML();
          }
        }
        $this->getPropertyListXML();
      }
      $this->getListXML();
      $this->getButtonsXML();
    }
  }

  /**
  * Get list XML
  *
  * @access public
  */
  function getListXML() {
    if (isset($this->cronjobs) && is_array($this->cronjobs)) {
      $result = sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Cronjobs'))
      );
      $result .= '<items>'.LF;
      foreach ($this->cronjobs as $cronjobId => $cronjob) {
        $idx = ($cronjob['cronjob_active']) ? 'items-cronjob' : 'status-cronjob-disabled';
        $href = $this->getLink(array('id' => (int)$cronjobId));
        $selected = ($cronjobId == $this->params['id']) ? ' selected="selected"' : '';
        $result .= sprintf(
          '<listitem title="%s" image="%s" href="%s" %s>'.LF,
          papaya_strings::escapeHTMLChars($cronjob['cronjob_title']),
          papaya_strings::escapeHTMLChars($this->papaya()->images[$idx]),
          papaya_strings::escapeHTMLChars($href),
          $selected
        );
        $result .= '</listitem>'.LF;
      }
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $this->layout->addLeft($result);
    }
  }

  /**
  * Initialize property dialog
  *
  * @access public
  */
  function initializePropertyDialog() {
    if (isset($this->cronjob) && is_array($this->cronjob)) {
      if (!(isset($this->propertyDialog) && is_array($this->propertyDialog))) {
        $hidden = array(
          'cmd' => 'save',
          'id' => (int)$this->cronjob['cronjob_id']
        );
        $cronModules = array();
        if (isset($this->cronModules) && is_array($this->cronModules)) {
          foreach ($this->cronModules as $moduleGuid => $module) {
            $cronModules[$moduleGuid] = $module['module_title'];
          }
        }
        $jobModules = array();
        if (isset($this->jobModules) && is_array($this->jobModules)) {
          foreach ($this->jobModules as $moduleGuid => $module) {
            $jobModules[$moduleGuid] = $module['module_title'];
          }
        }
        $fields = array(
          'cronjob_title' => array('Title', 'isSomeText', TRUE, 'input', 200),
          'cronjob_description' => array('Description', 'isSomeText', FALSE,
            'textarea', 5),
          'Time',
          'cronjob_start_str' => array('Start' , 'isISODateTime', TRUE, 'datetime', 50),
          'cronjob_end_str' => array('End' , 'isISODateTime', TRUE, 'datetime', 50),
          'Modules',
          'cron_module_guid' => array('Time' , 'isGUID', TRUE, 'combo', $cronModules),
          'job_module_guid' => array('Job' , 'isGUID', TRUE, 'combo', $jobModules),
        );
        $this->propertyDialog = new base_dialog(
          $this, $this->paramName, $fields, $this->cronjob, $hidden
        );
        $this->propertyDialog->loadParams();
        $this->propertyDialog->baseLink = $this->baseLink;
        $this->propertyDialog->dialogTitle = $this->_gt('Properties');
        $this->propertyDialog->buttonTitle = 'Save';
        $this->propertyDialog->dialogDoubleButtons = FALSE;
      }
    }
  }

  /**
  * Get property form XML
  *
  * @access public
  */
  function getPropertyFormXML() {
    if (isset($this->cronjob) && is_array($this->cronjob)) {
      $this->initializePropertyDialog();
      $this->layout->add($this->propertyDialog->getDialogXML());
    }
  }

  /**
  * Create an iframe executing the cronjob
  * @return void
  */
  function getJobExecuteFrame() {
    $result = sprintf(
      '<panel title="%s"><iframe src="%s" style="width: 100%%; height: 400px;"/></panel>',
      papaya_strings::escapeHTMLChars($this->_gt('Execute cronjob')),
      'cronexec.php?job='.(int)$this->cronjob['cronjob_id']
    );
    $this->layout->add($result);
  }

  /**
  * Get property list XML
  *
  * @access public
  */
  function getPropertyListXML() {
    if (isset($this->cronjob) && is_array($this->cronjob)) {
      $result = sprintf(
        '<listview title="%s">'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Information'))
      );
      $result .= '<items>';
      $result .= sprintf(
        '<listitem title="%s"><subitem>%s</subitem></listitem>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Title')),
        papaya_strings::escapeHTMLChars($this->cronjob['cronjob_title'])
      );
      // we need to take the active status from this->cronjobs,
      // since its up to date after recent changes
      $active = $this->cronjobs[$this->cronjob['cronjob_id']]['cronjob_active']
        ? 'Yes' : 'No';
      $result .= sprintf(
        '<listitem title="%s"><subitem>%s</subitem></listitem>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Active')),
        papaya_strings::escapeHTMLChars($this->_gt($active))
      );
      $result .= sprintf(
        '<listitem title="%s"><subitem>%s</subitem></listitem>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Last execution')),
        ($this->cronjob['cronjob_lastexec'] > 1)
          ? \Papaya\Utility\Date::timestampToString($this->cronjob['cronjob_lastexec'], TRUE, FALSE)
          : papaya_strings::escapeHTMLChars($this->_gt('never'))
      );
      $result .= sprintf(
        '<listitem title="%s"><subitem>%s</subitem></listitem>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Next execution')),
        ($this->cronjob['cronjob_nextexec'] > time())
          ? \Papaya\Utility\Date::timestampToString($this->cronjob['cronjob_nextexec'], TRUE, FALSE)
          : papaya_strings::escapeHTMLChars($this->_gt('instantaneous'))
      );
      $result .= sprintf(
        '<listitem title="%s"><subitem/></listitem>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Time calculation'))
      );
      $result .= sprintf(
        '<listitem title="%s" indent="1"><subitem>%s</subitem></listitem>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Module')),
        papaya_strings::escapeHTMLChars($this->cronjob['cron_module_title'])
      );
      $result .= sprintf(
        '<listitem title="%s" indent="1"><subitem>%s</subitem></listitem>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Class')),
        papaya_strings::escapeHTMLChars($this->cronjob['cron_module_class'])
      );
      $result .= sprintf(
        '<listitem title="%s"><subitem/></listitem>'.LF,
        $this->_gt('Job module')
      );
      $result .= sprintf(
        '<listitem title="%s" indent="1"><subitem>%s</subitem></listitem>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Module')),
        papaya_strings::escapeHTMLChars($this->cronjob['job_module_title'])
      );
      $result .= sprintf(
        '<listitem title="%s" indent="1"><subitem>%s</subitem></listitem>'.LF,
        papaya_strings::escapeHTMLChars($this->_gt('Class')),
        papaya_strings::escapeHTMLChars($this->cronjob['job_module_class'])
      );
      $result .= '</items>'.LF;
      $result .= '</listview>'.LF;
      $this->layout->addRight($result);
    }
  }

  /**
  * Get delete confirm XML
  *
  * @access public
  */
  function getDelConfirmXML() {
    if (isset($this->cronjob) && is_array($this->cronjob)) {
      $hidden = array(
        'cmd' => 'delete',
        'id' => $this->cronjob['cronjob_id'],
        'delconfirm' => 1,
      );
      $msg = sprintf(
        $this->_gt('Delete cronjob "%s" (%s)?'),
        $this->cronjob['cronjob_title'],
        $this->cronjob['cronjob_id']
      );
      $dialog = new base_msgdialog($this, $this->paramName, $hidden, $msg, 'question');
      $dialog->buttonTitle = 'Delete';
      $this->layout->add($dialog->getMsgDialog());
    }
  }

  /**
  * Get Buttons XML
  *
  * @access public
  */
  function getButtonsXML() {
    $menubar = new base_btnbuilder;
    $menubar->images = $this->papaya()->images;
    $menubar->addButton(
      'Add cronjob',
      $this->getLink(array('cmd' => 'add')),
      'actions-cronjob-add',
      'Add cronjob'
    );
    if (isset($this->params['id']) && isset($this->cronjobs[(int)$this->params['id']])) {
      $menubar->addButton(
        'Delete cronjob',
        $this->getLink(array('cmd' => 'delete', 'id' => (int)$this->params['id'])),
        'actions-cronjob-delete',
        'Delete cronjob'
      );
      if ($this->cronjobs[(int)$this->params['id']]['cronjob_active']) {
        $menubar->addSeperator();
        $menubar->addButton(
          'Deactivate cronjob',
          $this->getLink(array('cmd' => 'deactivate', 'id' => (int)$this->params['id'])),
          'status-cronjob-disabled',
          'Deactivate cronjob'
        );
      }
      if (defined('PAPAYA_BROWSER_CRONJOBS') && PAPAYA_BROWSER_CRONJOBS &&
          defined('PAPAYA_BROWSER_CRONJOBS_IP')) {
        $ipAddresses = preg_split('(\s*,\s*)', trim(PAPAYA_BROWSER_CRONJOBS_IP));
        if (isset($_SERVER['REMOTE_ADDR']) &&
            \Papaya\Filter\Factory::isIpAddress($_SERVER['REMOTE_ADDR'], TRUE)) {
          $remoteAddress = $_SERVER['REMOTE_ADDR'];
        }
        if (in_array('0.0.0.0', $ipAddresses) ||
            (isset($remoteAddress) && in_array($remoteAddress, $ipAddresses))) {
          $menubar->addSeperator();
          $menubar->addButton(
            'Execute cronjob',
            $this->getLink(array('cmd' => 'execute', 'id' => (int)$this->params['id'])),
            'actions-execute',
            'Execute cronjob now'
          );
        }
      }
      if (file_exists(PAPAYA_PATH_CACHE.'/papaya_cron.pid')) {
        $menubar->addSeperator();
        $menubar->addButton(
          'Delete pidfile',
          $this->getLink(array('cmd' => 'delete_pid')),
          'actions-generic-delete',
          'Delete process id file'
        );
      }
    }
    if ($result = $menubar->getXML()) {
      $this->layout->add('<menu>'.$result.'</menu>', 'menus');
    }
  }

  /**
  * Get toolbar XML, switches between the pages of the edit dialog
  *
  * @access public
  */
  function getToolbarXML() {
    $toolbar = new base_btnbuilder;
    $toolbar->images = $this->papaya()->images;
    $toolbar->addButton(
      'Properties',
      $this->getLink(array('mode' => 0)),
      'categories-properties',
      'General properties',
      ($this->params['mode'] == 0)
    );
    $toolbar->addButton(
      'Time',
      $this->getLink(array('mode' => 1)),
      'items-time',
      'Time configuration',
      ($this->params['mode'] == 1)
    );
    $toolbar->addButton(
      'Job',
      $this->getLink(array('mode' => 2)),
      'items-cronjob',
      'Job',
      ($this->params['mode'] == 2)
    );
    $toolbar->addSeperator();
    $toolbar->addButton(
      'Execution',
      $this->getLink(array('mode' => 3)),
      'actions-execute',
      'Execution',
      ($this->params['mode'] == 3)
    );
    if ($result = $toolbar->getXML()) {
      $this->layout->add('<toolbar>'.$result.'</toolbar>', 'toolbars');
    }
  }

  /**
  * edit field for time module
  *
  * @access public
  */
  function getCronModuleEdit() {
    if (isset($this->cronjob) && is_array($this->cronjob)) {
      $this->getModuleEdit(
        $this->cronjob['cron_module_file'],
        $this->cronjob['cron_module_class'],
        $this->cronjob['cron_module_guid'],
        $this->cronjob['cron_data'],
        'saveCronData'
      );
    }
  }

  /**
  * Get job module edit
  *
  * @access public
  */
  function getJobModuleEdit() {
    if (isset($this->cronjob) && is_array($this->cronjob)) {
      $this->getModuleEdit(
        $this->cronjob['job_module_file'],
        $this->cronjob['job_module_class'],
        $this->cronjob['job_module_guid'],
        $this->cronjob['job_data'],
        'saveJobData'
      );
    }
  }

  /**
   * Get module edit
   *
   * @param string $fileName
   * @param string $className
   * @param string $guid
   * @param array $data
   * @param string $saveFunc
   * @access public
   */
  function getModuleEdit($fileName, $className, $guid, $data, $saveFunc) {
    $moduleObj = $this->papaya()->plugins->get($guid, $this, $data);
    if (isset($moduleObj) && is_object($moduleObj)) {
      $moduleObj->initializeDialog();
      if ($moduleObj->modified()) {
        if ($moduleObj->checkData()) {
          if ($this->$saveFunc($moduleObj->getData())) {
            $this->addMsg(
              MSG_INFO,
              $this->_gt('Changes saved.').' - '.$this->_gt('Cronjob is deactivated.')
            );
          } else {
            $this->addMsg(
              MSG_ERROR,
              $this->_gt('Database Error').' - '.$this->_gt('Changes not saved.')
            );
          }
        }
      }
      $this->layout->add($moduleObj->getForm());
    } else {
      $this->addMsg(
        MSG_ERROR,
        sprintf(
          $this->_gt('Module "%s" in file "%s" could not be initialized.'),
          $className,
          $fileName
        )
      );
    }
  }

  /**
  * Check conjob data
  *
  * @param integer $nextExecutionTime to handle a previous parameter value
  * @return mixed FALSE or array
  */
  function checkCronJobData($nextExecutionTime = NULL) {
    $cronModule = $this->papaya()->plugins->get(
      $this->cronjob['cron_module_guid'],
      $this,
      $this->cronjob['cron_data']
    );
    $jobModule = $this->papaya()->plugins->get(
      $this->cronjob['job_module_guid'],
      $this,
      $this->cronjob['job_data']
    );
    if (isset($cronModule) && isset($jobModule) && is_object($cronModule) &&
        is_object($jobModule)) {
      $calculatedData['nextexec'] = $this->getNextExecute($cronModule, $nextExecutionTime);
      $calculatedData['jobdatamsg'] = $jobModule->checkExecParams();
      return $calculatedData;
    } else {
      $this->addMsg(MSG_ERROR, $this->_gt('Cannot activate module(s).'));
    }
    return FALSE;
  }

  /**
  * Get next execution
  *
  * @param object $cronModule
  * @param integer $nextExecutionTime to handle a previous parameter value
  * @return integer
  */
  function getNextExecute($cronModule, $nextExecutionTime = NULL) {
    if (!($nextExecutionTime > 0)) {
      $nextExecutionTime = time();
    }
    if (isset($cronModule) && is_object($cronModule)) {
      $nextExecutionTime = $cronModule->getNextDateTime($nextExecutionTime);
      if (-1 == $this->checkTimeFrame($nextExecutionTime) && 0 < $nextExecutionTime) {
        if ($nextExecutionTime ==
            ($i = $cronModule->getNextDateTime($this->cronjob['cronjob_start']))) {
          return 0;
        } else {
          $nextExecutionTime = $i;
        }
      }
      if ($nextExecutionTime > 0) {
        switch ($this->checkTimeFrame($nextExecutionTime)) {
        case -1:
          return -1;
        case  0:
          return $nextExecutionTime;
        }
      }
    }
    return 0;
  }

  /**
  * Check time frame
  *
  * @param string $dateTime
  * @access public
  * @return integer
  */
  function checkTimeFrame($dateTime) {
    if (isset($this->cronjob) && is_array($this->cronjob)) {
      if ($dateTime >= $this->cronjob['cronjob_start'] &&
          $dateTime <= $this->cronjob['cronjob_end']) {
        return 0;
      } elseif ($dateTime <= $this->cronjob['cronjob_start']) {
        return -1;
      }
    }
    return 1;
  }

  /**
  * initialize the cronjob activate dialog
  * @return base_dialog
  */
  function initializeActivateForm() {
    static $dialog;
    if (isset($this->cronjob) && is_array($this->cronjob) &&
        !(isset($dialog) && is_object($dialog))) {
      $data = $this->cronjob;
      $fields = array(
        'cronjob_title' => array(
          'Title', 'isSomeText', FALSE, 'info', '', '', '', 'left'
        ),
        'cronjob_description' => array(
          'Description', 'isSomeText', FALSE, 'info', '', '', '', 'left'
        ),
        'Time',
        'cronjob_start_str' => array(
          'Start' , 'isSomeText', FALSE, 'info', '', '', '', 'left'
        ),
        'cronjob_end_str' => array(
          'End' , 'isSomeText', FALSE, 'info', '', '', '', 'left'
        ),
        'Execution',
        'nextexec' => array(
          'Next execution' , 'isISODateTime', TRUE, 'input', 20, '', '', 'left'
        ),
        'jobdatamsg' => array(
          'Job' , 'isSomeText', FALSE, 'info', '', '', '', 'left'
        ),
      );

      if ($calculatedData = $this->checkCronJobData()) {

        $data['nextexec'] = \Papaya\Utility\Date::timestampToString(
          $calculatedData['nextexec'], TRUE, FALSE
        );

        if ($calculatedData['jobdatamsg']) {
          $data['jobdatamsg'] = $calculatedData['jobdatamsg'];
        } else {
          $data['jobdatamsg'] = $this->_gt('none');
        }
      }

      $hidden = array(
        'cmd' => 'activate',
        'id' => (int)$this->cronjob['cronjob_id']
      );
      $dialog = new base_dialog($this, $this->paramName, $fields, $data, $hidden);
      $dialog->baseLink = $this->baseLink;
      $dialog->dialogTitle = $this->_gt('Activate cronjob');
      $dialog->buttonTitle = 'Activate';
      $dialog->dialogDoubleButtons = FALSE;
      $dialog->initializeParams();

      $currentNextExec = \Papaya\Utility\Date::stringToTimestamp($dialog->data['nextexec']);
      if (empty($dialog->params['nextexec']) && $this->cronjob['cronjob_nextexec'] > time() &&
          $currentNextExec < $this->cronjob['cronjob_nextexec']) {
        $dialog->params['nextexec'] = \Papaya\Utility\Date::timestampToString(
          $this->cronjob['cronjob_nextexec'], TRUE, FALSE
        );
      }
    }
    return $dialog;
  }

  /**
  * Get activate form XML
  *
  * @access public
  */
  function getActivateFormXML() {
    $dialog = $this->initializeActivateForm();
    if (isset($dialog)) {
      $this->layout->add($dialog->getDialogXML());
    }
  }

  /**
  * Delete process id file
  * @return void
  */
  function deletePidFile() {
    if (file_exists(PAPAYA_PATH_CACHE.'/papaya_cron.pid')) {
      unlink(PAPAYA_PATH_CACHE.'/papaya_cron.pid');
    }
  }
}


