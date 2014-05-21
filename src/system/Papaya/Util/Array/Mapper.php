<?php
/**
* Map values of an array into another array.
*
* @copyright 2009 by papaya Software GmbH - All rights reserved.
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
* @subpackage Util
* @version $Id: Mapper.php 39521 2014-03-05 17:08:00Z weinert $
*/

/**
* Map values of an array into another array.
*
* @package Papaya-Library
* @subpackage Util
*/
class PapayaUtilArrayMapper {

  /**
   * Target array uses teh same keys, the values are array elements, the subelement specified
   * by $indexName is used in the result.
   *
   * If you provide an array to one of the index arguments, it is treated as a list of identifers
   * the first found element is used.
   *
   * If the $elementIndex is NULL the full array element is added to the result.
   *
   * If the $keyIndex is NULL, the key from the original array is used
   *
   * @param array|Traversable $array
   * @param string|int|array $elementIndex
   * @param string|int|array $keyIndex
   * @return array
   */
  public static function byIndex($array, $elementIndex = NULL, $keyIndex = NULL) {
    PapayaUtilConstraints::assertArrayOrTraversable($array);
    $result = array();
    foreach ($array as $key => $value) {
      if (isset($keyIndex)) {
        $key = PapayaUtilArray::get($value, $keyIndex, NULL);
      }
      if (isset($elementIndex)) {
        $value = PapayaUtilArray::get($value, $elementIndex, NULL);
      }
      if (isset($value)) {
        if (isset($key)) {
          $result[$key] = $value;
        } else {
          $result[] = $value;
        }
      }
    }
    return $result;
  }
}