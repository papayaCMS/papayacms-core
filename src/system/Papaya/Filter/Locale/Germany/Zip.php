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
namespace Papaya\Filter\Locale\Germany;

use Papaya\Filter;

/**
 * Papaya filter class for German zip code
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class Zip implements Filter {
  /**
   * Check flag for country prefix
   *
   * @var bool|null
   */
  private $_allowCountryPrefix;

  /**
   * Constructor
   *
   * @param bool|null $allowCountryPrefix
   */
  public function __construct($allowCountryPrefix = NULL) {
    if (NULL !== $allowCountryPrefix && \is_bool($allowCountryPrefix)) {
      $this->_allowCountryPrefix = $allowCountryPrefix;
    }
  }

  /**
   * Validate the input value using the function and
   * throw an exception if the validation has failed.
   *
   * @throws Filter\Exception
   *
   * @param mixed $value
   *
   * @throws Filter\Exception\InvalidLength\ToShort
   * @throws Filter\Exception\InvalidLength\ToLong
   * @throws Filter\Exception\InvalidCharacter
   *
   * @return true
   */
  public function validate($value) {
    $matches = [];
    $regexp = '(^
        (?P<prefix>
          D[ -]?
        )?
        (?P<zipcode>
          .*
        )
      $)Dix';
    $found = \preg_match(
      $regexp,
      $value,
      $matches
    );
    if ($found && TRUE === $this->_allowCountryPrefix && empty($matches['prefix'])) {
      throw new Filter\Exception\InvalidCharacter($value, 0);
    }
    if (!$found || empty($matches['zipcode']) || \strlen($matches['zipcode']) < 5) {
      throw new Filter\Exception\InvalidLength\ToShort(5, \strlen($matches['zipcode']));
    }
    if (\strlen($matches['zipcode']) > 5) {
      throw new Filter\Exception\InvalidLength\ToLong(5, \strlen($matches['zipcode']));
    }
    $wrongMatches = [];
    $wrongFound = \preg_match('([^\\d])', $matches['zipcode'], $wrongMatches, PREG_OFFSET_CAPTURE);
    if ($wrongFound) {
      throw new Filter\Exception\InvalidCharacter($matches['zipcode'], $wrongMatches[0][1]);
    }
    return TRUE;
  }

  /**
   * The filter function is used to read a input value if it is valid.
   *
   * @param mixed $value
   *
   * @return string|null
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
