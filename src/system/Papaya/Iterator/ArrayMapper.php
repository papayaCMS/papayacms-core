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
namespace Papaya\Iterator;

use Papaya\Utility;

/**
 * This iterator allows convert array elements into scalars by fetching one specified element
 * from the array.
 *
 * @package Papaya-Library
 * @subpackage Iterator
 */
class ArrayMapper extends Callback {
  /**
   * Create object, store iterator data and element name.
   *
   * The element name can be an array. In this case the first found element is used.
   *
   * @param array|\Traversable $iterator
   * @param mixed $elementName
   */
  public function __construct($iterator, $elementName) {
    parent::__construct(
      $iterator,
      function($element) use ($elementName) {
        return Utility\Arrays::get($element, $elementName, NULL);
      },
      self::MODIFY_VALUES
    );
  }
}
