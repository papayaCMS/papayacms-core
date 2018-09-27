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
namespace Papaya\Message\Context\Variable\Visitor;

use Papaya\Message;

/**
 * Visitor to convert a variable into a plain text string dump
 *
 * @package Papaya-Library
 * @subpackage Messages
 */
class Text
  extends Message\Context\Variable\Visitor {
  /**
   * internal indent counter
   *
   * @var int
   */
  protected $_indent = 0;

  /**
   * Indentation string
   *
   * @var string
   */
  protected $_indentString = '  ';

  /**
   * Suffix for truncated string values
   *
   * @var string
   */
  protected $_truncateSuffix = '...';

  /**
   * Compiled string result
   *
   * @var string
   */
  protected $_variableString = '';

  /**
   * return compiled string result
   *
   * @return string
   */
  public function get() {
    return $this->_variableString;
  }

  /**
   * Add a line to the result
   *
   * @param string $line
   */
  private function _addLine($line) {
    if ('' !== (string)$this->_variableString) {
      $this->_variableString .= "\n";
    }
    $this->_variableString .= \str_repeat($this->_indentString, $this->_indent).$line;
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
    $count = \count($array);
    $this->_addLine(\sprintf('array(%d) {', $count));
    if ($count > 0) {
      if ($this->_increaseIndent()) {
        foreach ($array as $index => $element) {
          $this->_addLine(\sprintf('[%s]=>', $index));
          $this->visitVariable($element);
        }
        $this->_decreaseIndent();
      } else {
        $this->_addLine($this->_indentString.'...recursion limit...');
      }
    }
    $this->_addLine('}');
  }

  /**
   * Visit an boolean
   *
   * bool(true) or bool(false)
   *
   * @param bool $boolean
   */
  public function visitBoolean($boolean) {
    $this->_addLine(
      \sprintf('bool(%s)', $boolean ? 'true' : 'false')
    );
  }

  /**
   * Visit an integer variable
   *
   * int(n)
   *
   * @param int $integer
   */
  public function visitInteger($integer) {
    $this->_addLine(
      \sprintf('int(%d)', $integer)
    );
  }

  /**
   * Visit a float variable
   *
   * float(n.m)
   *
   * @param float $float
   */
  public function visitFloat($float) {
    $this->_addLine(
      \sprintf('float(%s)', (string)$float)
    );
  }

  /**
   * Visit a NULL variable
   *
   * NULL
   *
   * @param null $null
   */
  public function visitNull($null) {
    $this->_addLine('NULL');
  }

  /**
   * Visit an object variable, handle recursions and duplicates
   *
   * @param object $object
   */
  public function visitObject($object) {
    $reflection = new \ReflectionObject($object);
    $hash = \spl_object_hash($object);
    $isRecursion = $this->_isObjectRecursion($hash);
    $isDuplicate = $this->_isObjectDuplicate($hash);
    $this->_pushObjectStack($hash);
    $this->_addLine(
      \sprintf('object(%s) #%s {', $reflection->getName(), $this->_getObjectIndex($hash))
    );
    if ($isRecursion) {
      $this->_addLine($this->_indentString.'...object recursion...');
    } elseif ($isDuplicate) {
      $this->_addLine($this->_indentString.'...object duplication...');
    } elseif ($this->_increaseIndent()) {
      $values = \array_merge((array)$reflection->getStaticProperties(), (array)$object);
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
        $this->_addLine(\sprintf('[%s%s]=>', $visibility, $propertyName));
        if (\array_key_exists($propertyName, $values)) {
          $this->visitVariable($values[$propertyName]);
        } elseif ($property->isProtected()) {
          $protectedName = "\0*\0".$propertyName;
          $this->visitVariable($values[$protectedName]);
        } elseif ($property->isPrivate()) {
          $privateName = "\0".$reflection->getName()."\0".$propertyName;
          $this->visitVariable($values[$privateName]);
        }
      }
      $this->_decreaseIndent();
    } else {
      $this->_addLine($this->_indentString.'...recursion limit...');
    }
    $this->_popObjectStack($hash);
    $this->_addLine('}');
  }

  /**
   * Visit a resource
   *
   * resource(#n)
   *
   * @param resource $resource
   */
  public function visitResource($resource) {
    $this->_addLine(
      \sprintf('resource(#%d)', (int)$resource)
    );
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
    $length = \strlen($string);
    if (\strlen($string) > $this->_stringLength) {
      $value = \substr($string, 0, $this->_stringLength).$this->_truncateSuffix;
    } else {
      $value = $string;
    }
    $this->_addLine('string('.$length.') "'.$value.'"');
  }

  /**
   * Increase indent, return FALSE if recusion limit is reached
   *
   * @return bool
   */
  protected function _increaseIndent() {
    if ($this->_indent < ($this->_depth - 1)) {
      $this->_indent++;
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Decrease indent, throw an exception if indent whould be negative
   */
  protected function _decreaseIndent() {
    $this->_indent--;
  }
}
