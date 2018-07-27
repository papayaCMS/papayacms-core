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

namespace Papaya\Filter\Text;
/**
 * Papaya filter class normalizing a string
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class Normalize implements \Papaya\Filter {

  const OPTION_LOWERCASE = 1;
  const OPTION_ALLOW_ASTERISK = 2;

  /**
   * @var int
   */
  private $_options;

  public function __construct($options = 0) {
    $this->_options = $options;
  }

  /**
   * @param mixed $value
   * @return bool
   * @throws \Papaya\Filter\Exception\IsEmpty
   * @throws \Papaya\Filter\Exception\UnexpectedType
   */
  public function validate($value) {
    if (empty($value)) {
      throw new \Papaya\Filter\Exception\IsEmpty();
    } elseif (!is_scalar($value)) {
      throw new \Papaya\Filter\Exception\UnexpectedType('string');
    }
    return TRUE;
  }

  /**
   * @param mixed|NULL $value
   * @return string|null
   */
  public function filter($value) {
    if (!(isset($value) && is_scalar($value))) {
      return NULL;
    }
    $asterisk = \PapayaUtilBitwise::inBitmask(self::OPTION_ALLOW_ASTERISK, $this->_options) ? '*' : '';
    $pattern = '([^\pL\pN'.($asterisk).']+)u';
    $value = trim(preg_replace($pattern, ' ', $value));
    if (\PapayaUtilBitwise::inBitmask(self::OPTION_LOWERCASE, $this->_options)) {
      $value = \PapayaUtilStringUtf8::toLowerCase($value);
    }
    return $value !== '' ? $value : NULL;
  }

}
