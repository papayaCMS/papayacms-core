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
* Papaya filter class for German zip code
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterLocaleGermanyZip implements \Papaya\Filter {

  /**
   * Check flag for country prefix
   * @var boolean|NULL
   */
  private $_allowCountryPrefix;

  /**
   * Constructor
   *
   * @param bool|NULL $allowCountryPrefix
   */
  public function __construct($allowCountryPrefix = NULL) {
    if (NULL !== $allowCountryPrefix && is_bool($allowCountryPrefix)) {
      $this->_allowCountryPrefix = $allowCountryPrefix;
    }
  }

  /**
   * Validate the input value using the function and
   * throw an exception if the validation has failed.
   *
   * @throws \Papaya\Filter\Exception
   * @param string $value
   * @throws \Papaya\Filter\Exception\InvalidLength\ToShort
   * @throws \Papaya\Filter\Exception\InvalidLength\ToLong
   * @throws \Papaya\Filter\Exception\InvalidCharacter
   * @return TRUE
   */
  public function validate($value) {
    $matches = array();
    $regexp = '(^
        (?P<prefix>
          D[ -]?
        )?
        (?P<zipcode>
          .*
        )
      $)Dix';
    $found = preg_match(
      $regexp,
      $value,
      $matches
    );
    if ($found && TRUE === $this->_allowCountryPrefix && empty($matches['prefix'])) {
      throw new \Papaya\Filter\Exception\InvalidCharacter($value, 0);
    }
    if (!$found || empty($matches['zipcode']) || strlen($matches['zipcode']) < 5) {
      throw new \Papaya\Filter\Exception\InvalidLength\ToShort(5, strlen($matches['zipcode']));
    }
    if (strlen($matches['zipcode']) > 5) {
      throw new \Papaya\Filter\Exception\InvalidLength\ToLong(5, strlen($matches['zipcode']));
    }
    $wrongMatches = array();
    $wrongFound = preg_match('([^\\d])', $matches['zipcode'], $wrongMatches, PREG_OFFSET_CAPTURE);
    if ($wrongFound) {
      throw new \Papaya\Filter\Exception\InvalidCharacter($matches['zipcode'], $wrongMatches[0][1]);
    }
    return TRUE;
  }

  /**
  * The filter function is used to read a input value if it is valid.
  *
  * @param string $value
  * @return string|NULL
  */
  public function filter($value) {
    try {
      $this->validate($value);
      return $value;
    } catch (\PapayaFilterException $e) {
      return NULL;
    }
  }

}
