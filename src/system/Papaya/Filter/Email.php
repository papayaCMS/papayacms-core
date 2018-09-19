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
 * This filter class checks an email address.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class Email implements \Papaya\Filter {
  /**
   * Pattern to check for a linebreak
   *
   * @var string
   */
  private $_patternCheck = '(^[-!\#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+
    @[-!\#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+
    \.[-!\#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$)umx';

  /**
   * Check the value if it's a valid email address, if not throw an exception.
   *
   * @param string $value
   *
   * @throws \Papaya\Filter\Exception\UnexpectedType
   *
   * @return true
   */
  public function validate($value) {
    if (!\preg_match($this->_patternCheck, $value)) {
      throw new \Papaya\Filter\Exception\UnexpectedType('email address');
    }
    return TRUE;
  }

  /**
   * The filter function is used to read a input value if it is valid.
   *
   * @param string $value
   *
   * @return string
   */
  public function filter($value) {
    try {
      $this->validate($value);
      return $value;
    } catch (\Papaya\Filter\Exception $e) {
      return;
    }
  }
}
