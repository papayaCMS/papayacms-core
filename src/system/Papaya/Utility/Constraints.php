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
 * Papaya Utilities implementing contraints
 *
 * The functions of this class check for a simple type. If not given they throw an exception.
 *
 * @package Papaya-Library
 * @subpackage Util
 */
class Constraints {
  /**
   * Handle an assertion failure (throw the exception)
   *
   * @param string $expected expected types string
   * @param mixed $value actual value
   * @param string $message Individual error message (can be empty)
   *
   * @return \UnexpectedValueException
   */
  protected static function createException($expected, $value, $message) {
    if (empty($message)) {
      return new \UnexpectedValueException(
        \sprintf(
          'Unexpected value type: Expected "%s" but "%s" given.',
          $expected,
          \is_object($value) ? \get_class($value) : \gettype($value)
        )
      );
    }
    return new \UnexpectedValueException($message);
  }

  /**
   * Assert value is an array
   *
   * @throws \UnexpectedValueException
   *
   * @param mixed $value
   * @param string $message Individual error message (can be empty)
   *
   * @return true
   */
  public static function assertArray($value, $message = '') {
    if (\is_array($value)) {
      return TRUE;
    }
    throw self::createException('array', $value, $message);
  }

  /**
   * Assert value is an array or an Traversable instance. If either one is true, foreach can be
   * used on the variable.
   *
   * @throws \UnexpectedValueException
   *
   * @param mixed $value
   * @param string $message Individual error message (can be empty)
   *
   * @return true
   */
  public static function assertArrayOrTraversable($value, $message = '') {
    if (\is_array($value)) {
      return TRUE;
    }
    if ($value instanceof \Traversable) {
      return TRUE;
    }
    throw self::createException('array, Traversable', $value, $message);
  }

  /**
   * Assert value is a boolean
   *
   * @throws \UnexpectedValueException
   *
   * @param mixed $value
   * @param string $message Individual error message (can be empty)
   *
   * @return true
   */
  public static function assertBoolean($value, $message = '') {
    if (\is_bool($value)) {
      return TRUE;
    }
    throw self::createException('boolean', $value, $message);
  }

  /**
   * Assert value is a boolean
   *
   * @throws \UnexpectedValueException
   *
   * @param mixed $value
   * @param string $message Individual error message (can be empty)
   *
   * @return true
   */
  public static function assertCallable($value, $message = '') {
    if (\is_callable($value)) {
      return TRUE;
    }
    throw self::createException('callable', $value, $message);
  }

  /**
   * Assert value is contained in the given list
   *
   * @throws \UnexpectedValueException
   *
   * @param array $array
   * @param mixed $value
   * @param string $message Individual error message (can be empty)
   *
   * @return true
   */
  public static function assertContains(array $array, $value, $message = '') {
    if (\in_array($value, $array, FALSE)) {
      return TRUE;
    }
    if (empty($message)) {
      throw new \UnexpectedValueException('Array does not contains the given value.');
    }
    throw new \UnexpectedValueException($message);
  }

  /**
   * Assert value is a float
   *
   * @throws \UnexpectedValueException
   *
   * @param mixed $value
   * @param string $message Individual error message (can be empty)
   *
   * @return true
   */
  public static function assertFloat($value, $message = '') {
    if (\is_float($value)) {
      return TRUE;
    }
    throw self::createException('float', $value, $message);
  }

  /**
   * Assert value is an integer
   *
   * @throws \UnexpectedValueException
   *
   * @param mixed $value
   * @param string $message Individual error message (can be empty)
   *
   * @return true
   */
  public static function assertInteger($value, $message = '') {
    if (\is_int($value)) {
      return TRUE;
    }
    throw self::createException('integer', $value, $message);
  }

  /**
   * Assert value is not empty
   *
   * @throws \UnexpectedValueException
   *
   * @param mixed $value
   * @param string $message Individual error message (can be empty)
   *
   * @return true
   */
  public static function assertNotEmpty($value, $message = '') {
    if (empty($value)) {
      throw self::createException(
        '',
        NULL,
        empty($message) ? 'Empty value given but not allowed.' : $message
      );
    }
    return TRUE;
  }

  /**
   * Assert value is a number (integer or float)
   *
   * @throws \UnexpectedValueException
   *
   * @param mixed $value
   * @param string $message Individual error message (can be empty)
   *
   * @return true
   */
  public static function assertNumber($value, $message = '') {
    if (\is_int($value) || \is_float($value)) {
      return TRUE;
    }
    throw self::createException('integer, float', $value, $message);
  }

  /**
   * Assert value is an object
   *
   * @throws \UnexpectedValueException
   *
   * @param mixed $value
   * @param string $message Individual error message (can be empty)
   *
   * @return true
   */
  public static function assertObject($value, $message = '') {
    if (\is_object($value)) {
      return TRUE;
    }
    throw self::createException('object', $value, $message);
  }

  /**
   * Assert value is an object or NULL
   *
   * This is not a class check! Use type hints and the instanceof operator.
   *
   * @throws \UnexpectedValueException
   *
   * @param mixed $value
   * @param string $message Individual error message (can be empty)
   *
   * @return true
   */
  public static function assertObjectOrNull($value, $message = '') {
    if (\is_object($value) || NULL === $value) {
      return TRUE;
    }
    throw self::createException('object, NULL', $value, $message);
  }

  /**
   * Assert value is an instance of $className
   *
   * @throws \UnexpectedValueException
   *
   * @param array|string $expectedClass
   * @param mixed $value
   * @param string $message Individual error message (can be empty)
   *
   * @return true
   */
  public static function assertInstanceOf($expectedClass, $value, $message = '') {
    if (\is_array($expectedClass) || $expectedClass instanceof \Traversable) {
      $validated = [];
      foreach ($expectedClass as $class) {
        if ($value instanceof $class) {
          return TRUE;
        }
        $validated[] = $class;
      }
      throw self::createException(\implode(', ', $validated), $value, $message);
    }
    if ($value instanceof $expectedClass) {
      return TRUE;
    }
    throw self::createException($expectedClass, $value, $message);
  }

  /**
   * Assert value is an instance of $className if it is not NULL
   *
   * @throws \UnexpectedValueException
   *
   * @param array|string $expectedClass
   * @param mixed $value
   * @param string $message Individual error message (can be empty)
   *
   * @return true
   */
  public static function assertInstanceOfOrNull($expectedClass, $value, $message = '') {
    if (NULL === $value) {
      return TRUE;
    }
    return self::assertInstanceOf($expectedClass, $value, $message);
  }

  /**
   * Assert value is a resource
   *
   * @throws \UnexpectedValueException
   *
   * @param mixed $value
   * @param string $message Individual error message (can be empty)
   *
   * @return true
   */
  public static function assertResource($value, $message = '') {
    if (\is_resource($value)) {
      return TRUE;
    }
    throw self::createException('resource', $value, $message);
  }

  /**
   * Assert value is a string
   *
   * @throws \UnexpectedValueException
   *
   * @param mixed $value
   * @param string $message Individual error message (can be empty)
   *
   * @return true
   */
  public static function assertString($value, $message = '') {
    if (\is_string($value)) {
      return TRUE;
    }
    throw self::createException('string', $value, $message);
  }

  /**
   * Assert value is a string
   *
   * @throws \UnexpectedValueException
   *
   * @param mixed $value
   * @param string $message Individual error message (can be empty)
   *
   * @return true
   */
  public static function assertStringCastable($value, $message = '') {
    if (
      \is_string($value) ||
      $value instanceof \Papaya\BaseObject\Interfaces\StringCastable ||
      (\is_object($value) && \method_exists($value, '__toString'))
    ) {
      return TRUE;
    }
    throw self::createException('string, string castable', $value, $message);
  }
}
