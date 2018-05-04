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
* A list of email recipients. If you add a address using a string it will
* be converted into a {@see PapayaEmailAddress) object.
*
* @package Papaya-Library
* @subpackage Email
*/
class PapayaEmailRecipients extends PapayaObjectList {

  /**
  * Initialize object and set class restriction
  */
  public function __construct() {
    parent::__construct(\PapayaEmailAddress::class);
  }

  /**
  * Overload prepareITem method to convert a string into an object if needed.
  *
  * @param string|\PapayaEmailAddress $value
  * @return \PapayaEmailAddress
  */
  protected function prepareItem($value) {
    if (is_string($value)) {
      $item = new \PapayaEmailAddress();
      $item->address = $value;
    } else {
      $item = $value;
    }
    return $item;
  }
}
