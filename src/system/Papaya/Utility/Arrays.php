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

namespace Papaya\Utility;

/**
 * Papaya Utilities for Arrays
 *
 * @package Papaya-Library
 * @subpackage Util
 */
class Arrays {
  /**
   * recursive merge for two arrays with out changing the keys
   *
   * @param array|\Traversable $arrayOne
   * @param array|\Traversable $arrayTwo
   * @param int $recursion
   * @return array
   */
  public static function merge($arrayOne, $arrayTwo, $recursion = 20) {
    if (\is_array($arrayOne) || $arrayOne instanceof \Traversable) {
      $result = self::ensure($arrayOne);
      if (\is_array($arrayTwo) || $arrayTwo instanceof \Traversable) {
        foreach ($arrayTwo as $key => $value) {
          if (isset($result[$key]) &&
            (\is_array($result[$key]) || $result[$key] instanceof \Traversable) &&
            $recursion > 1) {
            $result[$key] = \Papaya\Utility\Arrays::merge(
              self::ensure($result[$key]), $value, $recursion - 1
            );
          } else {
            $result[$key] = $value;
          }
        }
      }
    } elseif (\is_array($arrayTwo) || $arrayTwo instanceof \Traversable) {
      $result = self::ensure($arrayTwo);
    } else {
      return;
    }
    return $result;
  }

  /**
   * Converts a Traversable into an array. For optimisation it checks for other possiblities to get
   * the array without traversing the object.
   *
   * A skalar value or an object that is not an traversable will be converted into an array
   * containing this value.
   *
   * @param mixed $input
   * @param bool $useKeys
   * @return array
   */
  public static function ensure($input, $useKeys = TRUE) {
    if (\is_array($input)) {
      return ($useKeys) ? $input : \array_values($input);
    } elseif ($input instanceof \Traversable) {
      return \iterator_to_array($input, $useKeys);
    } else {
      return [$input];
    }
  }

  /**
   * Normalize array values using a callback. If no callback is defined, the values will be casted
   * to string
   *
   * @param mixed $value
   * @param \Callable $callback
   */
  public static function normalize(&$value, $callback = NULL) {
    if (\is_array($value)) {
      foreach ($value as &$subValue) {
        self::normalize($subValue);
      }
    } elseif (\is_callable($callback)) {
      $value = \call_user_func($callback, $value);
    } elseif (\is_object($value)) {
      if (\method_exists($value, '__toString')) {
        $value = (string)$value;
      } else {
        $value = \get_class($value);
      }
    } elseif (!\is_string($value)) {
      $value = (string)$value;
    }
  }

  /**
   * Gets the element specified by the index from the array, or return the default value
   * if it doesn not exists.
   *
   * @param array $array
   * @param mixed $index
   * @param mixed $default
   * @return mixed
   */
  public static function get(array $array, $index, $default = NULL) {
    if (\is_array($index) || $index instanceof \Traversable) {
      foreach ($index as $key) {
        if (\array_key_exists($key, $array)) {
          return $array[$key];
        }
      }
      return $default;
    } elseif (\is_scalar($index)) {
      return \array_key_exists($index, $array) ? $array[$index] : $default;
    }
    return $default;
  }

  /**
   * Gets the element specified by the keys from the nested arrays, or return the default value
   * if it does not not exists.
   *
   * @param array $array
   * @param array $keys
   * @param mixed $default
   * @return mixed
   */
  public static function getRecursive(array $array, array $keys, $default = NULL) {
    if (\count($array) > 0) {
      $data = $array;
      foreach ($keys as $key) {
        if (\is_array($data) && \array_key_exists($key, $data)) {
          $data = &$data[$key];
        } else {
          $data = $default;
          break;
        }
      }
      return $data;
    }
    return $default;
  }

  /**
   * Extract all positive integer numbers from a stirng into an array
   *
   * @param string $string
   * @return array
   */
  public static function decodeIdList($string) {
    if (\preg_match_all('([+-]?\d+)', $string, $matches)) {
      return \is_array($matches[0]) ? $matches[0] : [];
    }
    return [];
  }

  /**
   * Compile a list of integer number into a string.
   *
   * @param array $list
   * @param string $separator
   * @return string
   */
  public static function encodeIdList(array $list, $separator = ';') {
    return \implode($separator, $list);
  }

  /**
   * Compile a list of integer number into a string and quote that string with the given char.
   *
   * @param array $list
   * @param string $quote
   * @param string $separator
   * @return string
   */
  public static function encodeAndQuoteIdList(array $list, $quote = ';', $separator = ';') {
    return $quote.self::encodeIdList($list, $separator).$quote;
  }
}
