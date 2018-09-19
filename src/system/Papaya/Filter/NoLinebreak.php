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
 * This filter class checks for linebreaks and filters them out.
 *
 * The linebreak and whitespace character preceding and following the linebreak are replaced with
 * a single space.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class NoLinebreak implements \Papaya\Filter {
  /**
   * Pattern to check for a linebreak
   *
   * @var string
   */
  private $_patternCheck = '([\r\n])u';

  /**
   * Pattern to replace linebreaks and surrounding whitespace
   *
   * @var string
   */
  private $_patternFilter = '(\s*[\r\n]+\s*)u';

  /**
   * Check the value for linebreaks, if the value contains linebreaks throw an exception
   *
   * @throws \Papaya\Filter\Exception\InvalidCharacter
   *
   * @param string $value
   *
   * @return true
   */
  public function validate($value) {
    if (\preg_match($this->_patternCheck, $value, $match, PREG_OFFSET_CAPTURE)) {
      throw new \Papaya\Filter\Exception\InvalidCharacter($value, $match[0][1]);
    }
    return TRUE;
  }

  /**
   * Replace linebreaks and surrounding whitespace characters with a single space
   *
   * @param string $value
   *
   * @return string
   */
  public function filter($value) {
    return \preg_replace($this->_patternFilter, ' ', $value);
  }
}
