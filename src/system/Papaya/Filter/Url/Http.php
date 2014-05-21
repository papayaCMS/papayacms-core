<?php
/**
* Papaya filter class validating a url host name
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
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
* @version $Id: Http.php 37436 2012-08-16 21:02:01Z weinert $
*/

/**
* Papaya filter class validating a url host name
*
* @package Papaya-Library
* @subpackage Filter
*/
class PapayaFilterUrlHttp extends PapayaFilterUrl {

  /**
   * @see PapayaFilter::validate()
   */
  public function validate($value) {
    return parent::validate($this->prepare($value));
  }

  /**
   * @see PapayaFilter::filter()
   */
  public function filter($value) {
    return parent::filter($this->prepare($value));
  }

  /**
   * prefix the value if needed with http://
   *
   * @param string $value
   * @return string
   */
  private function prepare($value) {
    if (!empty($value) && !preg_match('(^https?://)', $value)) {
      return 'http://'.$value;
    }
    return $value;
  }
}
