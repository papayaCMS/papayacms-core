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

/** @noinspection PhpComposerExtensionStubsInspection */
namespace Papaya\Text;

use Papaya\BaseObject\Interfaces\StringCastable;
use Papaya\Utility;

class UTF8String implements \Iterator, \ArrayAccess, StringCastable {
  const MODE_INTL = 1;

  const MODE_ICONV = 2;

  const MODE_MBSTRING = 3;

  /**
   * @var array list of possible modes and the needed php extension
   */
  private $_extensions = [
    self::MODE_INTL => 'intl',
    self::MODE_MBSTRING => 'mbstring',
    self::MODE_ICONV => 'iconv'
  ];

  /**
   * @var string Internal string buffer
   */
  private $_string;

  /**
   * @var int string character length buffer (cached)
   */
  private $_length;

  /**
   * @var int used unicode mode
   */
  private $_mode;

  /**
   * @var int iterator position
   */
  private $_position = -1;

  /**
   * @var null current iterator character
   */
  private $_current;

  /**
   * @var array allowed modes
   */
  private $_allowModes = [
    self::MODE_INTL,
    self::MODE_MBSTRING,
    self::MODE_ICONV
  ];

  /**
   * Encapsulate string into unicode object
   *
   * @param string $string
   * @param bool $convertUnknown convert unknown bytes as LATIN1 to UTF-8
   */
  public function __construct($string, $convertUnknown = FALSE) {
    $this->_string = $convertUnknown ? Utility\Text\UTF8::ensure($string) : (string)$string;
  }

  /**
   * Cast object back to string
   *
   * @return string
   */
  public function __toString() {
    return $this->_string;
  }

  /**
   * @return int return the character length
   */
  public function length() {
    if (NULL === $this->_length) {
      $this->_length = $this->_getLength($this->_string);
    }
    return $this->_length;
  }

  /**
   * Return the first occurence of the character, if offset is provided,
   * start looking at that position
   *
   * @param string $needle
   * @param int $offset
   *
   * @return int
   */
  public function indexOf($needle, $offset = 0) {
    switch ($this->getMode()) {
      case self::MODE_ICONV :
        return \iconv_strpos($this->_string, (string)$needle, $offset, 'utf-8');
      case self::MODE_MBSTRING :
        return \mb_strpos($this->_string, (string)$needle, $offset, 'utf-8');
      case self::MODE_INTL :
      default :
        return \grapheme_strpos($this->_string, (string)$needle, $offset);
      // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd
  }

  /**
   * Return the last occurence of the character, if offsetif is provided,
   * stop looking at that position
   *
   * @param string $needle
   * @param int $offset
   *
   * @return int
   */
  public function lastIndexOf($needle, $offset = NULL) {
    $string = NULL !== $offset ? $this->_getSubStr(0, $offset) : $this->_string;
    switch ($this->getMode()) {
      case self::MODE_ICONV :
        $string = NULL !== $offset ? $this->_getSubStr(0, $offset) : $this->_string;
        return \iconv_strrpos($string, (string)$needle, 'utf-8');
      case self::MODE_MBSTRING :
        return \mb_strrpos($string, (string)$needle, 0, 'utf-8');
      case self::MODE_INTL :
      default :
        return \grapheme_strrpos($string, (string)$needle);
    }
  }

  /**
   * Return the character at the specified position
   *
   * @param $index
   *
   * @return null|string
   */
  public function charAt($index) {
    $char = $this->_getSubStr($index, 1);
    return (FALSE !== $char) ? $char : NULL;
  }

  /**
   * Return a substring ad an string object
   *
   * @param int $start
   * @param null|int $length
   *
   * @return self
   */
  public function substr($start, $length = NULL) {
    return new self($this->_getSubStr($start, $length));
  }

  /**
   * Set the allowed mode (the used library). You can provide a list of allowed modes.
   *
   * @param int|array $mode
   *
   * @return int
   */
  public function setMode($mode) {
    $this->_allowModes = Utility\Arrays::ensure($mode);
    $this->_mode = NULL;
    $this->_length = NULL;
    return $this->getMode();
  }

  /**
   * Check available extensions and allowed modes, return used
   * mode.
   *
   * @return int
   *
   * @throws \LogicException
   */
  public function getMode() {
    if (NULL === $this->_mode) {
      foreach ($this->_allowModes as $mode) {
        if (isset($this->_extensions[$mode]) &&
          \extension_loaded($this->_extensions[$mode])) {
          return $this->_mode = $mode;
        }
      }
      throw new \LogicException('Can not find unicode string support.');
    }
    return $this->_mode;
  }

  /**
   * @param $string
   * @return int
   */
  private function _getLength($string) {
    switch ($this->getMode()) {
      case self::MODE_ICONV :
        return \iconv_strlen($string, 'utf-8');
      break;
      case self::MODE_MBSTRING :
        return \mb_strlen($string, 'utf-8');
      break;
      case self::MODE_INTL :
      default :
        return \grapheme_strlen($string);
      break;
      // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd
  }

  private function _getSubStr($start, $length = NULL) {
    static $lengthBug = NULL;
    switch ($this->getMode()) {
      case self::MODE_ICONV :
        return \iconv_substr(
          $this->_string, $start, (NULL === $length) ? $this->length() : $length, 'utf-8'
        );
      case self::MODE_MBSTRING :
        return \mb_substr(
          $this->_string, $start, (NULL === $length) ? $this->length() : $length, 'utf-8'
        );
      case self::MODE_INTL :
      default :
        if (NULL === $lengthBug) {
          $lengthBug = PHP_VERSION_ID >= 50400;
        }
        // @codeCoverageIgnoreStart
        if (NULL === $length) {
          return \grapheme_substr($this->_string, $start);
        }
        if ($lengthBug && $length > 0) {
          if ($start >= 0) {
            $possibleLength = $this->length() - $start;
          } else {
            $possibleLength = \abs($start);
          }
          if ($possibleLength < $length) {
            $length = $possibleLength;
          }
        }
        return \grapheme_substr($this->_string, $start, $length);
    }
    // @codeCoverageIgnoreEnd
  }

  public function rewind() {
    $this->_position = -1;
    $this->next();
  }

  public function next() {
    $this->_current = $this->charAt(++$this->_position);
  }

  /**
   * @return bool
   */
  public function valid() {
    return $this->offsetExists($this->_position);
  }

  /**
   * @return int|mixed
   */
  public function key() {
    return $this->_position;
  }

  /**
   * @return mixed|null
   */
  public function current() {
    return $this->_current;
  }

  /**
   * @param int $offset
   * @return bool
   */
  public function offsetExists($offset) {
    return $offset >= 0 && $offset < $this->length();
  }

  /**
   * @param int $offset
   * @return null|string
   */
  public function offsetGet($offset): mixed {
    return $this->charAt($offset);
  }

  /**
   * @param int $offset
   * @param string $char
   */
  public function offsetSet($offset, $char): void {
    if (1 !== $this->_getLength($char)) {
      throw new \LogicException('Invalid character: '.$char);
    }
    $this->_string = $this->_getSubStr(0, $offset).$char.$this->_getSubStr($offset + 1);
    $this->_length = NULL;
  }

  /**
   * @param int $offset
   */
  public function offsetUnset($offset): void {
    throw new \LogicException('You can not remove character from the string.');
  }
}
