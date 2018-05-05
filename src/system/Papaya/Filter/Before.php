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

/**
 * Apply first filter before using the second to validate
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class PapayaFilterBefore implements \PapayaFilter {

  /**
   * @var PapayaFilter
   */
  private $_before;

  /**
   * @var PapayaFilter
   */
  private $_after;

  /**
   * PapayaFilterLogicalBefore constructor.
   * @param \PapayaFilter $filterBefore
   * @param \PapayaFilter $validationAfter
   */
  public function __construct(\PapayaFilter $filterBefore, \PapayaFilter $validationAfter) {
    $this->_before = $filterBefore;
    $this->_after = $validationAfter;
  }

  /**
   * @param string $value
   * @return bool
   */
  public function validate($value) {
    return $this->_after->validate($this->_before->filter($value));
  }

  /**
   * @param string $value
   * @return mixed|null|string
   */
  public function filter($value) {
    return $this->_after->filter($this->_before->filter($value));
  }
}
