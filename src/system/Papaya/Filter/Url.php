<?php
/**
* Papaya filter class for urls.
*
* @copyright 2011 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Filter
* @version $Id: Url.php 38143 2013-02-19 14:58:24Z weinert $
*/

/**
* This filter class checks an url.
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterUrl implements PapayaFilter {

  /**
  * Pattern to check for a linebreak
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
  * @throws PapayaFilterExceptionType
  * @param string $value
  * @return TRUE
  */
  public function validate($value) {
    if (!preg_match($this->_patternCheck, $value)) {
      throw new PapayaFilterExceptionType('url');
    }
    return TRUE;
  }

  /**
  * The filter function is used to read a input value if it is valid.
  *
  * @param string $value
  * @return string
  */
  public function filter($value) {
    try {
      $this->validate($value);
      return $value;
    } catch (PapayaFilterException $e) {
      return NULL;
    }
  }
}