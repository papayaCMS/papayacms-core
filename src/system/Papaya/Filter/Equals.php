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
 * Papaya filter class that chcks if the value is an empty one
 *
 * The private typeMapping property is used to specifiy possible casts.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class Equals implements Filter {
  /**
   * The comparision
   *
   * @var mixed
   */
  private $_value;

  /**
   * Construct object, check and store options
   *
   * @param mixed $value
   */
  public function __construct($value) {
    $this->_value = $value;
  }

  /**
   * Check the value throw exception if value is not empty
   *
   * @param string $value
   *
   * @throws Exception
   *
   * @return true
   */
  public function validate($value) {
    /** @noinspection TypeUnsafeComparisonInspection */
    if ($this->_value != $value) {
      throw new Exception\NotEqual($this->_value);
    }
    return TRUE;
  }

  /**
   * @param mixed $value
   *
   * @return mixed|null
   */
  public function filter($value) {
    /** @noinspection TypeUnsafeComparisonInspection */
    if ($this->_value == $value) {
      return $this->_value;
    }
    return NULL;
  }
}
