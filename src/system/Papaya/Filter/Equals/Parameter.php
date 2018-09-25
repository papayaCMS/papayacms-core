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
namespace Papaya\Filter\Equals;

use Papaya\Filter;
use Papaya\Request;

/**
 * Papaya filter class that checks if the value is equal to a given parameter value.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class Parameter implements Filter {
  /**
   * Given parameters object
   *
   * @var Request\Parameters
   */
  private $_parameters;

  /**
   * Given parameters name object
   *
   * @var Request\Parameters\Name
   */
  private $_parameterName;

  /**
   * Construct object, check and store options
   *
   * @param Request\Parameters $parameters
   * @param string $parameterName
   */
  public function __construct(Request\Parameters $parameters, $parameterName) {
    $this->_parameters = $parameters;
    $this->_parameterName = new Request\Parameters\Name($parameterName);
  }

  /**
   * Check the value throw exception if value is not equal to given parameter value
   *
   * @throws Filter\Exception\InvalidValue
   *
   * @param mixed $value
   *
   * @return true
   */
  public function validate($value) {
    if ($this->_parameters->get((string)$this->_parameterName, '') !== (string)$value) {
      throw new Filter\Exception\InvalidValue($value);
    }
    return TRUE;
  }

  /**
   * Checks the value and returns the value if validate succeeded, otherwise NULL
   *
   * @param mixed $value
   *
   * @return mixed|null
   */
  public function filter($value) {
    try {
      $this->validate($value);
      return $value;
    } catch (Filter\Exception $e) {
      return NULL;
    }
  }
}
