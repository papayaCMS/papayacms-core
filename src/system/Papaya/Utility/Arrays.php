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
   * @param iterable|NULL $arrayOne
   * @param iterable|NULL $arrayTwo
   * @param int $recursion
   *
   * @return array
   */
  public static function merge(
    iterable $arrayOne = NULL, iterable $arrayTwo = NULL, int $recursion = 20
  ): array {
    $isTraversableOne = is_iterable($arrayOne);
    $isTraversableTwo = is_iterable($arrayTwo);
    if ($isTraversableOne) {
      $result = self::ensure($arrayOne);
      if ($isTraversableTwo) {
        foreach ($arrayTwo as $key => $value) {
          if (
            $recursion > 1 &&
            isset($result[$key]) &&
            (is_iterable($result[$key]))
          ) {
            $result[$key] = self::merge(
              self::ensure($result[$key]), $value, $recursion - 1
            );
          } else {
            $result[$key] = $value;
          }
        }
      }
    } elseif ($isTraversableTwo) {
      $result = self::ensure($arrayTwo);
    } else {
      return [];
    }
    return $result;
  }

  /**
   * Push elements into an existing. Unlike using array_push()
   * this will not throw an error if no element was provided.
   *
   * @param array $array
   * @param ...$values
   */
  public static function push(array &$array, ...$values): void {
    if (count($values) > 0) {
      array_push($array, ...$values);
    }
  }

  /**
   * Converts a Traversable into an array. For optimisation it checks for other possibilities to get
   * the array without traversing the object.
   *
   * A scalar value or an object that is not an traversable will be converted into an array
   * containing this value.
   *
   * @param mixed $input
   * @param bool $useKeys
   * @return array
   */
  public static function ensure($input, bool $useKeys = TRUE): array {
    if (is_array($input)) {
      return ($useKeys) ? $input : array_values($input);
    }
    if (is_iterable($input)) {
      return iterator_to_array($input, $useKeys);
    }
    return [$input];
  }

  /**
   * Normalize array values using a callback. If no callback is defined, the values will be casted
   * to string
   *
   * @param mixed $value
   * @param callable|NULL $callback
   */
  public static function normalize(&$value, callable $callback = NULL): void {
    if (is_array($value)) {
      foreach ($value as &$subValue) {
        self::normalize($subValue);
      }
    } elseif (is_callable($callback)) {
      $value = $callback($value);
    } elseif (is_object($value)) {
      if (method_exists($value, '__toString')) {
        $value = (string)$value;
      } else {
        $value = get_class($value);
      }
    } elseif (!is_string($value)) {
      $value = (string)$value;
    }
  }

  /**
   * Gets the element specified by the index from the array, or return the default value
   * if it doesn't not exists.
   *
   * @param array $array
   * @param mixed $index
   * @param mixed $default
   *
   * @return mixed
   */
  public static function get(array $array, $index, $default = NULL) {
    if (is_iterable($index)) {
      foreach ($index as $key) {
        if (array_key_exists($key, $array)) {
          return $array[$key];
        }
      }
      return $default;
    }
    if (is_scalar($index)) {
      return array_key_exists($index, $array) ? $array[$index] : $default;
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
    if (count($array) > 0) {
      $data = $array;
      foreach ($keys as $key) {
        if (is_array($data) && array_key_exists($key, $data)) {
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
   * Extract all positive integer numbers from a string into an array
   *
   * @param string $string
   * @return array
   */
  public static function decodeIdList(string $string): array {
    if (preg_match_all('([+-]?\d+)', $string, $matches)) {
      return is_array($matches[0]) ? $matches[0] : [];
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
  public static function encodeIdList(array $list, string $separator = ';'): string {
    return implode($separator, $list);
  }

  /**
   * Compile a list of integer number into a string and quote that string with the given char.
   *
   * @param array $list
   * @param string $quote
   * @param string $separator
   *
   * @return string
   */
  public static function encodeAndQuoteIdList(
    array $list, string $quote = ';', string $separator = ';'
  ): string {
    return $quote.self::encodeIdList($list, $separator).$quote;
  }

  /**
   * Return the first not null value in the array
   *
   * @param array $array
   * @return mixed
   */
  public static function firstNotNull(array $array) {
    foreach ($array as $value) {
      if (NULL !== $value) {
        return $value;
      }
    }
    return NULL;
  }
}
