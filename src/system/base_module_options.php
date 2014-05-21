<?php
/**
* singleton class for modules to load and save options
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya
* @subpackage Core
* @version $Id: base_module_options.php 39760 2014-04-24 17:05:58Z weinert $
*/

/**
* singleton class for modules to load and save options
*
* @package Papaya
* @subpackage Core
*/
class base_module_options extends base_db {

  /**
  * module options database table
  * @var string
  */
  var $tableModuleOptions = PAPAYA_DB_TBL_MODULEOPTIONS;

  /**
  * internal options array
  * @var array
  */
  var $_options = array();

  /**
  * get a singleton instance
  *
  * @access public
  * @return base_module_options
  */
  function &getInstance() {
    static $instance;
    if (!isset($instance)) {
      $instance = new base_module_options();
    }
    return $instance;
  }

  /**
  * load an option (class function)
  * returns the default value if no option can be found
  * the first read call for a module loads all options into memory
  *
  * @param string $moduleGuid
  * @param string $optionName
  * @param string | array $defaultValue optional, default value NULL
  * @access public
  * @return string | array
  */
  public function readOption($moduleGuid, $optionName, $defaultValue = NULL) {
    $instance = base_module_options::getInstance();
    $optionName = strtoupper($optionName);
    if (!isset($instance->_options[$moduleGuid])) {
      $instance->loadOptions($moduleGuid);
    }
    if (isset($instance->_options[$moduleGuid])) {
      if (isset($instance->_options[$moduleGuid][$optionName])) {
        return $instance->_options[$moduleGuid][$optionName];
      }
    }
    return $defaultValue;
  }

  /**
  * write an option (class function)
  *
  * @param string $moduleGuid
  * @param string $optionName
  * @param string | array $value
  * @access public
  * @return boolean
  */
  public function writeOption($moduleGuid, $optionName, $value) {
    $instance = base_module_options::getInstance();
    if ($instance->saveOption($moduleGuid, $optionName, $value)) {
      //if the option is changed in the database - and already loaded
      if (isset($instance->_options[$moduleGuid])) {
        //change the loaded value
        $instance->_options[$moduleGuid][$optionName] = $value;
      }
      return FALSE;
    }
    return TRUE;
  }

  /**
  * load all options for a module
  *
  * @param string $moduleGuid
  * @access public
  * @return boolean
  */
  function loadOptions($moduleGuid) {
    $this->_options[$moduleGuid] = array();
    $sql = "SELECT moduleoption_name, moduleoption_value, moduleoption_type
              FROM %s
             WHERE module_guid = '%s'
             ORDER BY moduleoption_name";
    $params = array($this->tableModuleOptions, $moduleGuid);
    if ($res = $this->databaseQueryFmt($sql, $params)) {
      while ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
        if ($row['moduleoption_type'] == 'array') {
          if (empty($row['moduleoption_value'])) {
            $this->_options[$moduleGuid][$row['moduleoption_name']] = array();
          } elseif (substr($row['moduleoption_value'], 0, 1) == '<') {
            $this->_options[$moduleGuid][$row['moduleoption_name']] =
              PapayaUtilStringXml::unserializeArray($row['moduleoption_value']);
          } else {
            $this->_options[$moduleGuid][$row['moduleoption_name']] =
              @unserialize($row['moduleoption_value']);
          }
        } else {
          $this->_options[$moduleGuid][$row['moduleoption_name']] =
            $row['moduleoption_value'];
        }
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
  * save an option
  *
  * this saves an option to the database table
  *
  * @param string $moduleGuid
  * @param string $optionName
  * @param string $value
  * @access public
  * @return boolean
  */
  function saveOption($moduleGuid, $optionName, $value) {
    $optionName = PapayaUtilStringIdentifier::toUnderscoreUpper($optionName);
    if (preg_match('~^[a-fA-F\d]{32}$~', $moduleGuid) &&
        preg_match('~^[A-Z_-\d]{3,50}$~', $optionName)) {
      $sql = "SELECT moduleoption_value
                FROM %s
               WHERE module_guid = '%s'
                 AND moduleoption_name = '%s'";
      $params = array($this->tableModuleOptions, $moduleGuid, $optionName);
      if (isset($value) && is_array($value)) {
        $value = PapayaUtilStringXml::serializeArray($value);
        $type = 'array';
      } else {
        $value = (string)$value;
        $type = '';
      }
      if ($res = $this->databaseQueryFmt($sql, $params)) {
        if ($row = $res->fetchRow(DB_FETCHMODE_ASSOC)) {
          if ($row['moduleoption_value'] != $value) {
            $data = array(
              'moduleoption_value' => $value,
              'moduleoption_type' => $type
            );
            $filter = array(
              'module_guid' => $moduleGuid,
              'moduleoption_name' => $optionName
            );
            return FALSE !== $this->databaseUpdateRecord(
              $this->tableModuleOptions, $data, $filter
            );
          } else {
            //nothing to save - option value already in database
            return TRUE;
          }
        } else {
          //insert the option
          $data = array(
            'module_guid' => $moduleGuid,
            'moduleoption_name' => $optionName,
            'moduleoption_type' => $type,
            'moduleoption_value' => $value
          );
          return (FALSE !== $this->databaseInsertRecord($this->tableModuleOptions, NULL, $data));
        }
      }
    }
    return FALSE;
  }
}
