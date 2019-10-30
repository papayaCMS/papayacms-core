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
namespace Papaya\Template\Simple\Scanner;

use Papaya\BaseObject\Interfaces\Properties;

/**
 * Scanner token of papaya simple template sytem.
 *
 * @package Papaya-Library
 * @subpackage Template
 *
 * @property-read int $type
 * @property-read int $offset
 * @property-read int $length
 * @property-read string $content
 */
class Token implements Properties {
  const ANY = -1;

  const TEXT = 1;

  const WHITESPACE = 2;

  const COMMENT_START = 10;

  const COMMENT_END = 11;

  const VALUE_NAME = 20;

  const VALUE_DEFAULT = 21;

  /**
   * @var array
   */
  private static $_tokenNames;

  /**
   * @var int
   */
  private $_offset;

  /**
   * @var int
   */
  private $_type;

  /**
   * @var string
   */
  private $_content;

  /**
   * Validate constructor arguments and store them
   *
   * @param int $type
   * @param int $offset
   * @param string $content
   *
   * @throws \InvalidArgumentException
   */
  public function __construct($type, $offset, $content) {
    \Papaya\Utility\Constraints::assertInteger($type);
    \Papaya\Utility\Constraints::assertInteger($offset);
    \Papaya\Utility\Constraints::assertString($content);
    $tokenTypes = self::getTokenTypes();
    if (!isset($tokenTypes[$type])) {
      throw new \InvalidArgumentException(
        \sprintf(
          'Unknown token type "%d"', $type
        )
      );
    }
    $this->_type = $type;
    $this->_offset = $offset;
    $this->_content = $content;
  }

  /**
   * Return the token types as type => name array.
   *
   * @return array(integer => string)
   */
  private static function getTokenTypes() {
    // @codeCoverageIgnoreStart
    if (NULL === self::$_tokenNames) {
      try {
        $reflection = new \ReflectionClass(__CLASS__);
        self::$_tokenNames = \array_flip($reflection->getConstants());
      } catch (\ReflectionException $e) {
      }
    }
    // @codeCoverageIgnoreEnd
    return self::$_tokenNames;
  }

  /**
   * Return a description of the token if it is casted to string;
   */
  public function __toString() {
    $tokenTypes = self::getTokenTypes();
    return $tokenTypes[$this->_type].'@'.$this->_offset.': "'.$this->_content.'"';
  }

  /**
   * Return the type as a string, return NULL if it is an invalid type.
   *
   * @param int $type
   *
   * @return string|null
   */
  public static function getTypeString($type) {
    $tokenTypes = self::getTokenTypes();
    if (!isset($tokenTypes[$type])) {
      return NULL;
    }
    return $tokenTypes[$type];
  }

  /**
   * Read private properties stored in constructor
   *
   * @param string $name
   *
   * @throws \LogicException
   *
   * @return int|string
   */
  public function __isset($name) {
    switch ($name) {
      case 'offset' :
      case 'type' :
      case 'content' :
      case 'length' :
        return TRUE;
    }
    return FALSE;
  }

  /**
   * Read private properties stored in constructor
   *
   * @param string $name
   *
   * @throws \LogicException
   *
   * @return int|string
   */
  public function __get($name) {
    switch ($name) {
      case 'offset' :
        return $this->_offset;
      case 'type' :
        return $this->_type;
      case 'content' :
        return $this->_content;
      case 'length' :
        return \strlen($this->_content);
    }
    throw new \LogicException(
      \sprintf('Unknown property: %s::$%s', __CLASS__, $name)
    );
  }

  /**
   * Block all undefined properties
   *
   * @param string $name
   * @param mixed $value
   *
   * @throws \LogicException
   */
  public function __set($name, $value) {
    throw new \LogicException('All properties are defined in the constructor, they are read only.');
  }

  /**
   * Block all undefined properties
   *
   * @param string $name
   *
   * @throws \LogicException
   */
  public function __unset($name) {
    throw new \LogicException('All properties are defined in the constructor, they are read only.');
  }
}
