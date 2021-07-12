<?php
/**
* Administration interface class for module options
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
* @subpackage Administration
* @version $Id: papaya_module_options.php 39260 2014-02-18 17:13:06Z weinert $
*/

/**
* Administration interface class for module options
*
* @package Papaya
* @subpackage Administration
*/
class papaya_module_options extends base_module_options {

  /**
  * get all options of an module
  *
  * @param string $moduleGuid
  * @access public
  * @return array
  */
  function getOptions($moduleGuid) {
    if ($this->loadOptions($moduleGuid)) {
      if (isset($this->_options[$moduleGuid]) &&
          is_array($this->_options[$moduleGuid])) {
        return $this->_options[$moduleGuid];
      }
    }
    return array();
  }

  /**
  * save a list of option values
  *
  * @param string $moduleGuid
  * @param array $values
  * @access public
  * @return boolean
  */
  function saveOptions($moduleGuid, $values) {
    if (is_array($values)) {
      //just call the option save in a loop
      foreach ($values as $optionName => $optionValue) {
        if (!$this->saveOption($moduleGuid, $optionName, $optionValue)) {
          return FALSE;
        }
      }
    }
    return TRUE;
  }
}


