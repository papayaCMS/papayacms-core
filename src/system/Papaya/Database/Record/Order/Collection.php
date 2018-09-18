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

namespace Papaya\Database\Record\Order;

/**
 * An list storing several elements representing fields for an sql order by
 *
 * @package Papaya-Library
 * @subpackage Database
 */
class Collection
  extends \Papaya\BaseObject\Collection
  implements \Papaya\Database\Interfaces\Order {
  /**
   * Setup item class limit and add all function arguments as items
   */
  public function __construct() {
    parent::__construct(\Papaya\Database\Interfaces\Order::class);
    if (\func_num_args() > 0) {
      foreach (\func_get_args() as $item) {
        $this->add($item);
      }
    }
  }

  /**
   * Casting the list to string generates the needed sql
   *
   * @see \Papaya\Database\Interfaces\Order::__toString()
   * @return string
   */
  public function __toString() {
    if ($this->count() > 0) {
      $result = '';
      foreach ($this as $item) {
        $result .= ', '.$item;
      }
      return (string)\substr($result, 2);
    }
    return '';
  }
}
