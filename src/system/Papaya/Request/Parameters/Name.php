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
namespace Papaya\Request\Parameters;

/**
 * Papaya Request Parameter Name Handling, coverts a parameter name between array and string
 * representation.
 *
 * @package Papaya-Library
 * @subpackage Request
 */
class Name implements \ArrayAccess, \Countable, \IteratorAggregate {
  /**
   * Name parts list
   *
   * @var array
   */
  protected $_parts = [];

  /**
   * Default separator, used for typecasting to string
   *
   * @var string
   */
  protected $_separator = '';

  /**
   * Initialize object with data if provided.
   *
   * @param string|array $name
   * @param string $groupSeparator
   */
  public function __construct($name = NULL, $groupSeparator = NULL) {
    if (NULL !== $name) {
      $this->set($name, $groupSeparator);
    }
  }

  /**
   * Getter/Setter for parameter group separator
   *
   * @param string $groupSeparator
   *
   * @throws \InvalidArgumentException
   *
   * @internal param array|string $name
   *
   * @return string
   */
  public function separator($groupSeparator = NULL) {
    if (NULL !== $groupSeparator) {
      if (\in_array($groupSeparator, ['', '[]', ',', ':', '/', '*', '!'], TRUE)) {
        $this->_separator = (string)$groupSeparator;
      } else {
        throw new \InvalidArgumentException(
          \sprintf(
            'Invalid parameter group separator: "%s".', $groupSeparator
          )
        );
      }
    }
    return $this->_separator;
  }

  /**
   * Set name parts.
   *
   * @see \Papaya\Request\Parameters\Name::parse
   *
   * @throws \InvalidArgumentException
   *
   * @param string|int|array|self $name
   * @param string $groupSeparator
   */
  public function set($name, $groupSeparator = NULL) {
    if (
      NULL === $groupSeparator &&
      $name instanceof self
    ) {
      $groupSeparator = $name->separator();
    }
    $this->separator($groupSeparator);
    $this->_parts = $this->parse($name, $groupSeparator);
  }

  /**
   * Append name parts.
   *
   * @see \Papaya\Request\Parameters\Name::parse
   *
   * @throws \InvalidArgumentException
   *
   * @param string|int|array|\Papaya\Request\Parameters\Name $name
   * @param string $groupSeparator
   */
  public function append($name, $groupSeparator = NULL) {
    $parsed = $this->parse($name, $groupSeparator);
    $this->_parts = \array_merge($this->_parts, $parsed);
  }

  /**
   * Prepend name parts.
   *
   * @see \Papaya\Request\Parameters\Name::parse
   *
   * @throws \InvalidArgumentException
   *
   * @param string|int|array|\Papaya\Request\Parameters\Name $name
   * @param string $groupSeparator
   */
  public function prepend($name, $groupSeparator = NULL) {
    $parsed = $this->parse($name, $groupSeparator);
    $this->_parts = \array_merge($parsed, $this->_parts);
  }

  /**
   * Insert name parts before the specified index, append if the index does not exists.
   *
   * @see \Papaya\Request\Parameters\Name::parse
   *
   * @throws \InvalidArgumentException
   *
   * @param int $index
   * @param string|int|array|\Papaya\Request\Parameters\Name $name
   * @param string $groupSeparator
   */
  public function insertBefore($index, $name, $groupSeparator = NULL) {
    $parsed = $this->parse($name, $groupSeparator);
    \array_splice($this->_parts, $index, 0, $parsed);
  }

  /**
   * Parse name parts from a string or array or a \Papaya\Request\Parameters\Papaya\Request\Parameters\Name.
   * An integer is used like a string.
   *
   * @see \Papaya\Request\Parameters\Name::parseString()
   * @see \Papaya\Request\Parameters\Name::parseArray()
   *
   * @throws \InvalidArgumentException
   *
   * @param string|int|array|\Papaya\Request\Parameters\Name $name
   * @param string $groupSeparator
   *
   * @return array|string
   */
  public function parse($name, $groupSeparator = NULL) {
    if (\is_array($name)) {
      $parsed = $this->parseArray($name);
    } elseif (\is_string($name) || \is_int($name)) {
      $parsed = $this->parseString($name, $groupSeparator);
    } elseif ($name instanceof self) {
      $parsed = $name->getArray();
    } else {
      throw new \InvalidArgumentException(
        'InvalidAgmumentException: $name must be an array or string'.
        ' or a \Papaya\Request\Parameters\Name.'
      );
    }
    return $parsed;
  }

  /**
   * Set name parts using an array.
   *
   * @see \Papaya\Request\Parameters\Name::parseArray()
   *
   * @param array $name
   */
  public function setArray(array $name) {
    $this->_parts = $this->parseArray($name);
  }

  /**
   * All array elements are converted to strings.
   *
   * @param array $name
   *
   * @return array
   */
  public function parseArray(array $name) {
    $parts = [];
    foreach ($name as $part) {
      $parts[] = (string)$part;
    }
    return $parts;
  }

  /**
   * Set the name parts using a string.
   *
   * @see \Papaya\Request\Parameters\Name::parseString()
   *
   * @param string $name
   * @param string $groupSeparator
   */
  public function setString($name, $groupSeparator = '') {
    $this->separator($groupSeparator);
    $this->_parts = $this->parseString($name);
  }

  /**
   * Create an array of name parts from a string. Only the first found delimiter
   * is used. Mixing delimiters in a name string is not possible.
   * When an integer ist passed as $name it is used like a string.
   *
   * @param string $name
   * @param string $groupSeparator delimiter
   *
   * @return array
   */
  public function parseString($name, $groupSeparator = '') {
    $maximumLevels = 42;
    $name = \str_replace('.', '_', $name);
    $separators = [
      ['[', ']'], ',', ':', '/', '*', '!'
    ];
    if (empty($groupSeparator)) {
      $groupSeparator = $this->separator();
    }
    if (!empty($groupSeparator) &&
      \is_string($groupSeparator) &&
      '[]' !== $groupSeparator) {
      \array_unshift($separators, $groupSeparator);
    }
    if ($isList = ('[]' === \substr($name, -2))) {
      $name = \substr($name, 0, -2);
      $maximumLevels--;
    }
    $result = [$name];
    foreach ($separators as $separator) {
      if (\is_array($separator)) {
        /* @noinspection MultiAssignmentUsageInspection */
        list($delimiter, $suffix) = $separator;
        $suffixOffset = \strlen($suffix) * -1;
      } else {
        $delimiter = $separator;
        $suffix = '';
        $suffixOffset = 0;
      }
      if (FALSE !== \strpos($name, $delimiter)) {
        $parts = \explode($delimiter, $name, $maximumLevels);
        if ($suffixOffset < 0) {
          $result = [
            \array_shift($parts)
          ];
          foreach ($parts as $part) {
            if (\substr($part, $suffixOffset) === $suffix) {
              $result[] = \substr($part, 0, $suffixOffset);
            }
          }
          break;
        }
        $result = $parts;
        break;
      }
    }
    if ($isList) {
      $result[] = '';
    }
    return $result;
  }

  /**
   * Get the name as a string
   *
   * @param string $groupSeparator
   *
   * @return string
   */
  public function getString($groupSeparator = '') {
    if (\count($this->_parts) > 0) {
      if (empty($groupSeparator)) {
        $groupSeparator = $this->separator();
      }
      if (\count($this->_parts) > 1) {
        if ('[]' === $groupSeparator || '' === $groupSeparator) {
          $subParts = $this->_parts;
          $firstPart = \array_shift($subParts);
          return $firstPart.'['.\implode('][', $subParts).']';
        }
        return \implode($groupSeparator, $this->_parts);
      }
      return \reset($this->_parts);
    }
    return '';
  }

  /**
   * Get the name as a string, using []-syntax for url levels. This is a magic method and called
   * if the object is converted into a string.
   *
   * @return string
   */
  public function __toString() {
    return $this->getString();
  }

  /**
   * Get the name as an array. This will always return an array. The array can be empty.
   *
   * @return array
   */
  public function getArray() {
    return $this->_parts;
  }

  /**
   * ArrayAccess: check if an name parts exists
   *
   * @param int $offset
   *
   * @return bool
   */
  public function offsetExists($offset) {
    return isset($this->_parts[$offset]);
  }

  /**
   * ArrayAccess: get the specified name part
   *
   * @param int $offset
   *
   * @return string
   */
  public function offsetGet($offset) {
    return $this->_parts[$offset];
  }

  /**
   * ArrayAccess: change the specified name part. This will reset the offset.
   *
   * @param int $offset
   * @param string $value
   */
  public function offsetSet($offset, $value) {
    $this->_parts[$offset] = (string)$value;
    $this->_parts = \array_values($this->_parts);
  }

  /**
   * ArrayAccess: remove the specified name part. This will reset the offset.
   *
   * @param int $offset
   */
  public function offsetUnset($offset) {
    unset($this->_parts[$offset]);
    $this->_parts = \array_values($this->_parts);
  }

  /**
   * Countable: return the number of name parts stored in the internal array
   *
   * @return int
   */
  public function count() {
    return \count($this->_parts);
  }

  /**
   * IteratorAggregate: return an iterator for the name parts
   *
   * @return \ArrayIterator
   */
  public function getIterator() {
    return new \ArrayIterator($this->getArray());
  }
}
