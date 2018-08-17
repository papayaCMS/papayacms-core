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

namespace Papaya\Email;
/**
 * A list of email recipients. If you add a address using a string it will
 * be converted into a {@see \Papaya\Email\Address) object.
 *
 * @package Papaya-Library
 * @subpackage Email
 */
class Recipients extends \Papaya\BaseObject\Collection {

  /**
   * Initialize object and set class restriction
   */
  public function __construct() {
    parent::__construct(Address::class);
  }

  /**
   * Overload prepareItem method to convert a string into an object if needed.
   *
   * @param string|Address $value
   * @return Address
   */
  protected function prepareItem($value) {
    if (is_string($value)) {
      $item = new Address();
      $item->address = $value;
    } else {
      $item = $value;
    }
    return $item;
  }
}
