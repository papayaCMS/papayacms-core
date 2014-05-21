<?php
/**
* A command condition testing a request parameter.
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
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
* @version $Id: Parameter.php 36273 2011-10-04 13:51:35Z weinert $
*/

/**
* A command condition testing a request parameter.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiControlCommandConditionParameter extends PapayaUiControlCommandCondition {

  /**
  * The parameter name
  *
  * @var string|array|PapayaRequestParametersName
  */
  private $_parameterName = NULL;

  /**
  * the filter object
  *
  * @var PapayaFilter
  */
  private $_filter = NULL;

  /**
  * Create object, store parameter and filter.
  *
  * @param string|array|PapayaRequestParametersName $parameterName
  * @param PapayaFilter $filter
  */
  public function __construct($parameterName, PapayaFilter $filter) {
    $this->_parameterName = $parameterName;
    $this->_filter = $filter;
  }

  /**
  * Validate the condition by fetch the parameter value and filtering it. If it is not NULL
  * it is a usable value.
  *
  * @return boolean
  */
  public function validate() {
    return NULL !== $this->_filter->filter(
      $this->command()->owner()->parameters()->get($this->_parameterName)
    );
  }
}