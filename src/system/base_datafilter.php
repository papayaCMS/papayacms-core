<?php
/**
* data filter base class
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
* @subpackage Modules
* @version $Id: base_datafilter.php 39643 2014-03-20 10:23:13Z weinert $
*/

/**
* data filter base class
*
* @package Papaya
* @subpackage Modules
*/
abstract class base_datafilter extends base_plugin {

  /**
  * Edit fields
  * @var array $editFields
  */
  var $editFields = array();

  /**
  * Get dialog
  *
  * @see base_dialog:getDialogXML
  * @access public
  * @return string
  */
  function getDialog() {
    $this->initializeDialog();
    $this->dialog->dialogTitle = $this->_gt('Edit filter properties');
    $this->dialog->dialogDoubleButtons = FALSE;
    return $this->dialog->getDialogXML();
  }

  /**
   * Load filter data
   *
   * @param array $data
   * @param array $keys
   * @access public
   * @return boolean loaded
   */
  function prepareFilterData($data, $keys) {
    return FALSE;
  }

  /**
   * Load filter data
   *
   * @param array $data
   * @access public
   * @return boolean loaded
   */
  function loadFilterData($data) {
    return FALSE;
  }

  /**
  * apply filter to string
  *
  * @param string $str text input
  * @access public
  * @return string $str text result / output
  */
  function applyFilterData($str) {
    return $str;
  }

  /**
  * Get xml output of additional filter data
  *
  * @access public
  * @param array $parseParams parsing params
  * @return string $result xml
  */
  function getFilterData($parseParams = NULL) {
    return '';
  }
}
