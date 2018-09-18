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

namespace Papaya\UI\Control\Command\Condition;

/**
 * A command condition testing a request parameter.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Parameter extends \Papaya\UI\Control\Command\Condition {
  /**
   * The parameter name
   *
   * @var string|array|\Papaya\Request\Parameters\Name
   */
  private $_parameterName;

  /**
   * the filter object
   *
   * @var \Papaya\Filter
   */
  private $_filter;

  /**
   * Create object, store parameter and filter.
   *
   * @param string|array|\Papaya\Request\Parameters\Name $parameterName
   * @param \Papaya\Filter $filter
   */
  public function __construct($parameterName, \Papaya\Filter $filter) {
    $this->_parameterName = $parameterName;
    $this->_filter = $filter;
  }

  /**
   * Validate the condition by fetch the parameter value and filtering it. If it is not NULL
   * it is a usable value.
   *
   * @return bool
   */
  public function validate() {
    return NULL !== $this->_filter->filter(
        $this->command()->owner()->parameters()->get($this->_parameterName)
      );
  }
}
