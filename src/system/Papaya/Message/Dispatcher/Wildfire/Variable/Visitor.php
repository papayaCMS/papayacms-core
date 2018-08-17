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

namespace Papaya\Message\Dispatcher\Wildfire\Variable;
/**
 * Visitor to convert a variable into a plain text string dump
 *
 * @package Papaya-Library
 * @subpackage Messages
 */
class Visitor
  extends \Papaya\Message\Context\Variable\Visitor {

  /**
   * Suffix for truncated string values
   *
   * @var string
   */
  protected $_truncateSuffix = '...';

  /**
   * Dump result buffer variable
   *
   * @var mixed
   */
  protected $_dump;

  /**
   * Current element reference (to $_dump) to add nested elements.
   *
   * @var mixed
   */
  protected $_parent;

  /**
   * Key for next nested element (buffer variable)
   *
   * @var mixed
   */
  protected $_currentKey;

  /**
   * Dump stack, parent path references
   *
   * @var mixed
   */
  protected $_stack = array();

  /**
   * return compiled string result
   *
   * @return string
   */
  public function get() {
    return print_r($this->_dump, TRUE);
  }

  /**
   * Return created dump
   *
   * @return mixed
   */
  public function getDump() {
    return $this->_dump;
  }

  /**
   * Visit an array, and all its elements
   *
   * array(n) {
   *   [key] =>
   *   value
   * }
   *
   * @param array $array
   */
  public function visitArray(array $array) {
    if ($this->_checkIndentLimit()) {
      $arrayDump = &$this->_addElement(array());
      $this->_increaseIndent($arrayDump);
      foreach ($array as $key => $value) {
        $this->_currentKey = $key;
        $this->visitVariable($value);
      }
      $this->_decreaseIndent();
    } else {
      $this->_addElement(
        sprintf(
          '** Max Recursion Depth (%d) **', $this->_depth
        )
      );
    }
  }

  /**
   * Visit an boolean
   *
   * bool(true) or bool(false)
   *
   * @param bool $boolean
   */
  public function visitBoolean($boolean) {
    $this->_addElement($boolean);
  }

  /**
   * Visit an integer variable
   *
   * int(n)
   *
   * @param integer $integer
   */
  public function visitInteger($integer) {
    $this->_addElement($integer);
  }

  /**
   * Visit a float variable
   *
   * float(n.m)
   *
   * @param float $float
   */
  public function visitFloat($float) {
    $this->_addElement($float);
  }

  /**
   * Visit a NULL variable
   *
   * NULL
   *
   * @param NULL $null
   */
  public function visitNull($null) {
    $this->_addElement($null);
  }

  /**
   * Visit an object variable, handle recursions and duplicates
   *
   * @param object $object
   * @throws \LogicException
   */
  public function visitObject($object) {
    $reflection = new \ReflectionObject($object);
    $hash = spl_object_hash($object);
    $isRecursion = $this->_isObjectRecursion($hash);
    $isDuplicate = $this->_isObjectDuplicate($hash);
    if ($isRecursion) {
      $this->_addElement(
        sprintf(
          '** Object Recursion (%s #%d) **', $reflection->getName(), $this->_getObjectIndex($hash)
        )
      );
    } elseif ($isDuplicate) {
      $this->_addElement(
        sprintf(
          '** Object Duplication (%s #%d) **', $reflection->getName(), $this->_getObjectIndex($hash)
        )
      );
    } elseif ($this->_checkIndentLimit()) {
      $this->_pushObjectStack($hash);
      $objectDump = &$this->_addElement(
        array(
          '__className' => $reflection->getName().' #'.$this->_getObjectIndex($hash)
        )
      );
      $this->_increaseIndent($objectDump);
      $values = array_merge((array)$reflection->getStaticProperties(), (array)$object);
      foreach ($reflection->getProperties() as $property) {
        $propertyName = $property->getName();
        $visibility = '';
        if ($property->isStatic()) {
          $visibility .= 'static:';
        }
        if ($property->isPrivate()) {
          $visibility .= 'private:';
        } elseif ($property->isProtected()) {
          $visibility .= 'protected:';
        } else {
          $visibility .= 'public:';
        }
        $this->_currentKey = $visibility.$propertyName;
        if (array_key_exists($propertyName, $values)) {
          $this->visitVariable($values[$propertyName]);
        } elseif ($property->isProtected()) {
          $protectedName = "\0*\0".$propertyName;
          $this->visitVariable($values[$protectedName]);
        } elseif ($property->isPrivate()) {
          $privateName = "\0".$reflection->getName()."\0".$propertyName;
          if (array_key_exists($privateName, $values)) {
            $this->visitVariable($values[$privateName]);
          }
        }
      }
      $this->_popObjectStack($hash);
      $this->_decreaseIndent();
    } else {
      $this->_addElement(
        sprintf(
          '** Max Recursion Depth (%d) **', $this->_depth
        )
      );
    }
  }

  /**
   * Visit a resource
   *
   * resource(#n)
   *
   * @param resource $resource
   */
  public function visitResource($resource) {
    $this->_addElement('** '.(string)$resource.' **');
  }

  /**
   * Visit a string variable
   *
   * string(n) "sample"
   * string(n) "sample..."
   *
   * @param string $string
   */
  public function visitString($string) {
    $length = strlen($string);
    if (strlen($string) > $this->_stringLength) {
      $value = substr($string, 0, $this->_stringLength).$this->_truncateSuffix.'('.$length.')';
    } else {
      $value = $string;
    }
    $this->_addElement($value);
  }

  /**
   * Add element to dump, and return a reference to it.
   *
   * @param mixed $element
   * @return mixed
   */
  private function &_addElement($element) {
    if (NULL === $this->_dump) {
      $this->_dump = &$element;
    } else {
      $this->_parent[$this->_currentKey] = &$element;
    }
    return $element;
  }

  /**
   * Check if indent limit is reached
   *
   * @return boolean
   */
  protected function _checkIndentLimit() {
    return count($this->_stack) < $this->_depth;
  }

  /**
   * Increase indent
   *
   * @param mixed $reference
   */
  protected function _increaseIndent(&$reference) {
    $this->_parent = &$reference;
    $this->_stack[] = &$reference;
  }

  /**
   * Decrease indent, throw an exception if indent whould be negative
   *
   * @return void
   */
  protected function _decreaseIndent() {
    array_pop($this->_stack);
    $this->_parent = &$this->_stack[count($this->_stack) - 1];
  }
}
