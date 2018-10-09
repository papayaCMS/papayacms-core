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
namespace Papaya\Filter;

use Papaya\Filter;

/**
 * Apply first filter before using the second to validate
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class Before implements Filter {
  /**
   * @var Filter
   */
  private $_before;

  /**
   * @var Filter
   */
  private $_after;

  /**
   * PapayaFilterLogicalBefore constructor.
   *
   * @param Filter $filterBefore
   * @param Filter $validationAfter
   */
  public function __construct(Filter $filterBefore, Filter $validationAfter) {
    $this->_before = $filterBefore;
    $this->_after = $validationAfter;
  }

  /**
   * @param mixed $value
   *
   * @return true
   *
   * @throws \Papaya\Filter\Exception
   */
  public function validate($value) {
    return $this->_after->validate($this->_before->filter($value));
  }

  /**
   * @param mixed $value
   *
   * @return mixed|null
   */
  public function filter($value) {
    return $this->_after->filter($this->_before->filter($value));
  }
}
