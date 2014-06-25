<?php
/**
* Basic class for page modules
*
* All page modules must inherit from this object
* @copyright 2001-2014 by dimensional GmbH - All rights reserved.
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
* @subpackage Modules
* @version $Id: base_content.php 39818 2014-05-13 13:15:13Z weinert $
*/

/**
* Basic class for page modules
*
* All page modules must inherit from this object
*
* @package Papaya
* @subpackage Modules
*/
class base_content extends base_plugin {

  /**
  * Input field size
  * @var string $inputFieldSize
  */
  var $inputFieldSize = 'x-large';

  /**
   * @var PapayaTemplate
   */
  public $layout;

  /**
   * Get parsed data
   *
   * @access public
   * @param null|array $parseParams
   * @return string|NULL
   */
  function getParsedData($parseParams = NULL) {
    return '';
  }


  /**
   * get parsed teaser
   *
   * @access public
   * @param NULL|array $parseParams
   * @return string|NULL ''
   */
  function getParsedTeaser($parseParams = NULL) {
    return '';
  }

  /**
  * Execution of commands. When you implement your own class, you should override
  * this function if you need to execute commands this class receives via POST and
  * GET in the administration interface. The commands need to be tested for plausibility.
  *
  * @access public
  * @return boolean FALSE
  */
  function execute() {
    return FALSE;
  }

  /**
  * Delete cache
  *
  * @access public
  */
  function deleteCache() {
    if (isset($this->parentObj) && is_object($this->parentObj)) {
      $this->parentObj->deleteCache();
    }
  }

  /**
  * return an subpage identifier (alphanum string) for this page
  *
  * Examples: REGISTRATION_START, REGISTRATION_FINISHED
  *
  * Important: If a page is cached,
  *   $this->data whould be empty and getParsedData not was executed
  *
  * @access public
  * @return string
  */
  function getSubPageIdentifier() {
    return '';
  }


  /**
  * Initialize data filter objects
  *
  * @access public
  * @return object $dataFilterObj base_datafilter_list
  */
  function &initDataFilterObject() {
    if (defined('PAPAYA_DATAFILTER_USE') && PAPAYA_DATAFILTER_USE) {
      static $dataFilterObj;
      if (!(isset($dataFilterObj) && is_object($dataFilterObj))) {
        $dataFilterObj = new base_datafilter_list();
        $dataFilterObj->initialize($this);
      }
      return $dataFilterObj;
    } else {
      $result = FALSE;
      return $result;
    }
  }

  /**
  * Prepares content data in getData()
  *
  * @param array $keys keys of content data array to prepare
  * @access public
  */
  function prepareFilterData($keys) {
    if ($obj = $this->initDataFilterObject()) {
      $obj->prepareFilterData($keys);
    }
  }

  /**
   * Load filtered data in parseData
   *
   * @access public
   */
  function loadFilterData() {
    if ($obj = $this->initDataFilterObject()) {
      $obj->loadFilterData();
    }
  }

  /**
  * Applies filter to given string and return result
  *
  * @param string $string input string to filter
  * @access public
  * @return string $string filtered string
  */
  function applyFilterData($string) {
    if ($obj = $this->initDataFilterObject()) {
      return $obj->applyFilterData($string);
    }
    return $string;
  }

  /**
  * Get xml data from filter(s)
  *
  * @access public
  * @param array $parseParams parsing params
  * @return string as xml
  */
  function getFilterData($parseParams = NULL) {
    if ($obj = $this->initDataFilterObject()) {
      return $obj->getFilterData($parseParams);
    }
    return '';
  }
}

