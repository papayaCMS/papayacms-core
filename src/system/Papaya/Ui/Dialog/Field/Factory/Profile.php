<?php
/**
* Abstract superclass for field factory profiles.
*
* @copyright 2012 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Ui
* @version $Id: Profile.php 37351 2012-08-03 12:55:10Z weinert $
*/

/**
* Abstract superclass for field factory profiles.
*
* Each profile defines how a field {@see PapayaUiDialogField} is created for a specified
* type. Here is an options subobject to provide data for the field configuration.
*
* @package Papaya-Library
* @subpackage Ui
*/
abstract class PapayaUiDialogFieldFactoryProfile {

  /**
   * @var PapayaUiDialogFieldFactoryOptions
   */
  private $_options = NULL;

  /**
   * Create the field and return it. Throw an exception if somthing goes wrong
   *
   * @return PapayaUiDialogField
   * @throw PapayaUiDialogFieldFactoryException
   */
  abstract public function getField();

  /**
   * Getter/Setter for the options subobject
   *
   * @param PapayaUiDialogFieldFactoryOptions $options
   * @return PapayaUiDialogFieldFactoryOptions
   */
  public function options(PapayaUiDialogFieldFactoryOptions $options = NULL) {
    if (isset($options)) {
      $this->_options = $options;
    } elseif (NULL == $this->_options) {
      $this->_options = new PapayaUiDialogFieldFactoryOptions();
    }
    return $this->_options;
  }
}