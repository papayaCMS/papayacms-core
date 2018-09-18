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

/**
 * Papaya filter class that casts the value into the specified type.
 *
 * The private typeMapping property is used to specifiy possible casts.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class Cast implements \Papaya\Filter {
  /**
   * target type the value should be cast to.
   *
   * @var int
   */
  private $_type;

  /**
   * Type mapping
   *
   * @var int
   */
  private $_typeMapping = [
    'bool' => 'boolean',
    'boolean' => 'boolean',
    'double' => 'float',
    'float' => 'float',
    'int' => 'integer',
    'integer' => 'integer',
    'number' => 'float',
    'string' => 'string'
  ];

  /**
   * Construct object, check an store target type
   *
   * @param string $type
   * @throws \InvalidArgumentException
   */
  public function __construct($type) {
    if (isset($this->_typeMapping[$type])) {
      $this->_type = $this->_typeMapping[$type];
    } else {
      throw new \InvalidArgumentException(\sprintf('"%s" is not a valid type.', $type));
    }
  }

  /**
   * This filter does not validate values, it just filters (casts) them.
   *
   * @param string $value
   * @return true
   */
  public function validate($value) {
    return TRUE;
  }

  /**
   * The filter function casts the value into the target type.
   *
   * @param string $value
   * @return int|null
   */
  public function filter($value) {
    \settype($value, $this->_type);
    return $value;
  }
}
