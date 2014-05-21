<?php 
/**
* Papaya filter class that checks if the value is equal to a given parameter value.
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
* @subpackage Filter
* @version $Id: Parameter.php 38143 2013-02-19 14:58:24Z weinert $
*/

/**
* Papaya filter class that checks if the value is equal to a given parameter value.
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterEqualsParameter implements PapayaFilter {
  
  /**
  * Given parameters object
  * 
  * @var PapayaRequestParameters
  */
  private $_parameters = TRUE;
  
  /**
  * Given parameters name object
  * 
  * @var PapayaRequestParametersName
  */
  private $_parameterName = TRUE;
  
  /**
  * Construct object, check and store options
  *
  * @param PapayaRequestParameters $parameters
  * @param string $parameterName
  */
  public function __construct(PapayaRequestParameters $parameters, $parameterName) {
    $this->_parameters = $parameters;
    $this->_parameterName = new PapayaRequestParametersName($parameterName);
  }
  
  /**
  * Check the value throw exception if value is not equal to given parameter value
  * 
  * @see PapayaFilter::validate()
  * 
  * @throws PapayaFilterExceptionInvalid
  * @param string $value
  * @return TRUE
  */
  public function validate($value) {
    if ($this->_parameters->get((string)$this->_parameterName) != (string)$value) {
      throw new PapayaFilterExceptionInvalid($value);
    }
    return TRUE;
  }
  
  /**
  * Checks the value and returns the value if validate succeeded, otherwise NULL
  *
  * @param string $value
  * @return mixed|NULL
  */
  public function filter($value) {
    try {
      $this->validate($value);
      return $value;
    } catch (PapayaFilterException $e) {
      return NULL;
    }
  }
}