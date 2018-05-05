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

class PapayaStringUtf8 implements \Iterator, \ArrayAccess {

  const MODE_INTL = 1;
  const MODE_ICONV = 2;
  const MODE_MBSTRING = 3;

  /**
   * @var array list of possible modes and the needed php extension
   */
  private $_extensions = array(
    self::MODE_INTL => 'intl',
    self::MODE_MBSTRING => 'mbstring',
    self::MODE_ICONV => 'iconv'
  );

  /**
   * @var string Internal string buffer
   */
  private $_string = '';
  /**
   * @var int string character length buffer (cached)
   */
  private $_length = 0;
  /**
   * @var integer used unicode mode
   */
  private $_mode = NULL;

  /**
   * @var int iterator position
   */
  private $_position = -1;
  /**
   * @var null current iterator character
   */
  private $_current = NULL;

  /**
   * @var array allowed modes
   */
  private $_allowModes = array(
    self::MODE_INTL,
    self::MODE_MBSTRING,
    self::MODE_ICONV
  );

  /**
   * Encapsulate string into unicode object
   *
   * @param $string
   */
  public function __construct($string) {
    $this->_string = $string;
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
    if (NULL == $this->_length) {
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
   * @return int
   */
  public function indexOf($needle, $offset = 0) {
    switch ($this->getMode()) {
    case self::MODE_ICONV :
      return iconv_strpos($this->_string, (string)$needle, $offset, 'utf-8');
    case self::MODE_MBSTRING :
      return mb_strpos($this->_string, (string)$needle, $offset, 'utf-8');
    case self::MODE_INTL :
    default :
      return grapheme_strpos($this->_string, (string)$needle, $offset);
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
   * @return int
   */
  public function lastIndexOf($needle, $offset = NULL) {
    $string = isset($offset) ? $this->_getSubStr(0, $offset) : $this->_string;
    switch ($this->getMode()) {
    case self::MODE_ICONV :
      $string = isset($offset) ? $this->_getSubStr(0, $offset) : $this->_string;
      return iconv_strrpos($string, (string)$needle, 'utf-8');
    case self::MODE_MBSTRING :
      return mb_strrpos($string, (string)$needle, 0, 'utf-8');
    case self::MODE_INTL :
    default :
      return grapheme_strrpos($string, (string)$needle);
      // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd
  }

  /**
   * Return the chracter at the specified position
   *
   * @param $index
   * @return int|null|string
   */
  public function charAt($index) {
    $char = $this->_getSubStr($index, 1);
    return ($char !== FALSE) ? $char : NULL;
  }

  /**
   * Return a substring ad an string object
   *
   * @param $start
   * @param null $length
   * @return \PapayaStringUtf8
   */
  public function substr($start, $length = NULL) {
    return new self($this->_getSubStr($start, $length));
  }

  /**
   * Set the allowed mode (the used library). You can provide a list of allowed modes.
   *
   * @param integer|array $mode
   * @return int
   */
  public function setMode($mode) {
    $this->_allowModes = \PapayaUtilArray::ensure($mode);
    $this->_mode = NULL;
    $this->_length = NULL;
    return $this->getMode();
  }

  /**
   * Check available extensions and allowed modes, return used
   * mode.
   *
   * @return int
   * @throws \LogicException
   */
  public function getMode() {
    if (NULL === $this->_mode) {
      foreach ($this->_allowModes as $mode) {
        if (isset($this->_extensions[$mode]) &&
            extension_loaded($this->_extensions[$mode])) {
          return $this->_mode = $mode;
        }
      }
      throw new \LogicException('Can not find unicode string support.');
    }
    return $this->_mode;
  }

  private function _getLength($string) {
    switch ($this->getMode()) {
    case self::MODE_ICONV :
      return iconv_strlen($string, 'utf-8');
      break;
    case self::MODE_MBSTRING :
      return mb_strlen($string, 'utf-8');
      break;
    case self::MODE_INTL :
    default :
      return grapheme_strlen($string);
      break;
    // @codeCoverageIgnoreStart
    }
    // @codeCoverageIgnoreEnd
  }

  private function _getSubStr($start, $length = NULL) {
    static $lengthBug = NULL;
    switch ($this->getMode()) {
    case self::MODE_ICONV :
      return iconv_substr(
        $this->_string, $start, (NULL === $length) ? $this->length() : $length, 'utf-8'
      );
    case self::MODE_MBSTRING :
      return mb_substr(
        $this->_string, $start, (NULL === $length) ? $this->length() : $length, 'utf-8'
      );
    case self::MODE_INTL :
    default :
      if (NULL === $lengthBug) {
        $lengthBug = version_compare(PHP_VERSION, '5.4', '>=');
      }
      // @codeCoverageIgnoreStart
      if (NULL === $length) {
        return grapheme_substr($this->_string, $start);
      } elseif ($lengthBug && $length > 0) {
        if ($start >= 0) {
          $possibleLength = $this->length() - $start;
        } else {
          $possibleLength = abs($start);
        }
        if ($possibleLength < $length) {
          $length = $possibleLength;
        }
      }
      return grapheme_substr($this->_string, $start, $length);
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

  public function valid() {
    return $this->offsetExists($this->_position);
  }

  public function key() {
    return $this->_position;
  }

  public function current() {
    return $this->_current;
  }

  public function offsetExists($offset) {
    return $offset >= 0 && $offset < $this->length();
  }

  public function offsetGet($offset) {
    return $this->charAt($offset);
  }

  public function offsetSet($offset, $char) {
    if ($this->_getLength($char) != 1) {
      throw new \LogicException('Invalid character: '.$char);
    }
    $this->_string = $this->_getSubStr(0, $offset).$char.$this->_getSubStr($offset + 1);
    $this->_length = NULL;
  }

  public function offsetUnset($offset) {
    throw new \LogicException('You can not remove character from the string.');
  }
}
