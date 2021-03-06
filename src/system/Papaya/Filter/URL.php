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
 * This filter class checks an url.
 *
 * @package Papaya-Library
 * @subpackage Filter
 */
class URL implements Filter {
  /**
   * Pattern to check for a linebreak
   *
   * @var string
   */
  private $_patternCheck =
    '(^
       (https?://) # protocol
       ([a-z_]+(:[^@]+)?@)? # username:password
       ([a-z\d_-]+\.)* # subdomains
       ([a-z\d_-]+) # host
       (\.[a-z]{2,6})? # tld
       (:[\d\W\S]{1,5})? # port
       (/[+:%@\.a-z\d_~=()!,;-]*)* # path
       /? # optional ending slash
       (\? # query string
        (
         ([:a-z\d\.\[\]()/%@!_,;-]+)| # string value
         (&?[+:a-z\d\.\[\]()/%@_,;-]+=[+:a-z\d_\.\[\]()/%@!,;-]*)| # name value pair
         & # ampersand
        )*
       )?
       (\#[&?=+:a-z\d\.\[\]()%@!_,;/\\-]*)? # fragment
     $)Diux';

  /**
   * Check the value if it's a valid url, if not throw an exception.
   *
   * @throws Exception
   *
   * @param mixed $value
   *
   * @return true
   */
  public function validate($value) {
    if (!\preg_match($this->_patternCheck, $value)) {
      throw new Exception\UnexpectedType('url');
    }
    return TRUE;
  }

  /**
   * The filter function is used to read a input value if it is valid.
   *
   * @param mixed $value
   *
   * @return string
   */
  public function filter($value) {
    try {
      $this->validate($value);
      return (string)$value;
    } catch (Exception $e) {
      return NULL;
    }
  }
}
