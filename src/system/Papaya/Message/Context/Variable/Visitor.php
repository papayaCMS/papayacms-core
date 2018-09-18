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

namespace Papaya\Message\Context\Variable;

/**
 * Abstract superclass for variable dumps
 *
 * This class ist an abstract superclass for visitor classes used to convert a variable into an
 * formatted output (dump)
 *
 * @package Papaya-Library
 * @subpackage Messages
 */
abstract class Visitor {
  /**
   * maximum recursion depth
   *
   * @var int
   */
  protected $_depth = 5;

  /**
   * maximum string output length
   *
   * @var int
   */
  protected $_stringLength = 30;

  /**
   * @var array internal object stack for recursions
   */
  protected $_objectStack = [];

  /**
   * @var array internal object list for duplicates
   */
  protected $_objectList = [];

  /**
   * compile result to string and return it
   *
   * @return string
   */
  abstract public function get();

  /**
   * Visit an array variable
   *
   * @param array $array
   */
  abstract public function visitArray(array $array);

  /**
   * Visit a boolean variable
   *
   * @param bool $boolean
   */
  abstract public function visitBoolean($boolean);

  /**
   * Visit an integer variable
   *
   * @param int $integer
   */
  abstract public function visitInteger($integer);

  /**
   * Visit a float variable
   *
   * @param float $float
   */
  abstract public function visitFloat($float);

  /**
   * Visit a NULL variable
   *
   * @param null $null
   */
  abstract public function visitNull($null);

  /**
   * Visit an object variable
   *
   * @param object $object
   */
  abstract public function visitObject($object);

  /**
   * Visit a resource
   *
   * @param resource $resource
   */
  abstract public function visitResource($resource);

  /**
   * Visit a string variable
   *
   * @param string $string
   */
  abstract public function visitString($string);

  /**
   * Construct visitor object and set recursion depth and string length
   *
   * @param int $depth
   * @param int $stringLength
   */
  public function __construct($depth, $stringLength) {
    $this->_depth = $depth;
    $this->_stringLength = $stringLength;
  }

  /**
   * Magic method, allow to convert the visitor into a string
   */
  public function __toString() {
    return $this->get();
  }

  /**
   * Visit a variable (calls the other visit* methods)
   *
   * @param mixed $variable
   */
  public function visitVariable($variable) {
    if (\is_null($variable)) {
      $this->visitNull($variable);
    } elseif (\is_object($variable)) {
      $this->visitObject($variable);
    } elseif (\is_array($variable)) {
      $this->visitArray($variable);
    } elseif (\is_string($variable)) {
      $this->visitString($variable);
    } elseif (\is_bool($variable)) {
      $this->visitBoolean($variable);
    } elseif (\is_resource($variable)) {
      $this->visitResource($variable);
    } elseif (\is_int($variable)) {
      $this->visitInteger($variable);
    } elseif (\is_float($variable)) {
      $this->visitFloat($variable);
    }
  }

  /**
   * pushes an object hash to the recursion stack and adds it to the object list
   *
   * @param string $hash
   */
  protected function _pushObjectStack($hash) {
    $this->_objectStack[] = $hash;
    if (!isset($this->_objectList[$hash])) {
      $this->_objectList[$hash] = \count($this->_objectList) + 1;
    }
  }

  /**
   * pushes an object hash to the recursion stack and adds it to the object list
   *
   * @param string $hash
   * @throws \LogicException
   */
  protected function _popObjectStack($hash) {
    $last = \end($this->_objectStack);
    if ($last != $hash) {
      throw new \LogicException(
        \sprintf(
          'Trying to remove %s from object stack, but %s found.',
          $hash,
          $last
        )
      );
    }
    \array_splice($this->_objectStack, -1, 1);
  }

  /**
   * Check if object hash is in current recursion stack
   *
   * @param string $hash
   * @return bool
   */
  protected function _isObjectRecursion($hash) {
    return \in_array($hash, $this->_objectStack);
  }

  /**
   * Check if object hash is in object list (already visited)
   *
   * @param string $hash
   * @return bool
   */
  protected function _isObjectDuplicate($hash) {
    return isset($this->_objectList[$hash]);
  }

  /**
   * Return index of object in this context
   *
   * @param string $hash
   * @return int
   */
  protected function _getObjectIndex($hash) {
    return $this->_objectList[$hash];
  }
}
