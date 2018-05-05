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
* An list storing several elements representing fields for an sql order by
*
* @package Papaya-Library
* @subpackage Database
* @version $Id: List.php 38282 2013-03-19 12:23:19Z weinert $
*/
class PapayaDatabaseRecordOrderList
  extends \PapayaObjectList
  implements \PapayaDatabaseInterfaceOrder {

  /**
   * Setup item class limit and add all function arguments as items
   */
  public function __construct() {
    parent::__construct(\PapayaDatabaseInterfaceOrder::class);
    if (func_num_args() > 0) {
      foreach (func_get_args() as $item) {
        $this->add($item);
      }
    }
  }

  /**
   * Casting the list to string generates the needed sql
   *
   * @see \PapayaDatabaseInterfaceOrder::__toString()
   * @return string
   */
  public function __toString() {
    if ($this->count() > 0) {
      $result = '';
      foreach ($this as $item) {
        $result .= ', '.(string)$item;
      }
      return substr($result, 2);
    } else {
      return '';
    }
  }
}
