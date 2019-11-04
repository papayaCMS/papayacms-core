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
namespace Papaya\CSV;

use Papaya\BaseObject;
use Papaya\Utility;

/**
 * CSV writer allows you write data as csv into a stream or output.
 *
 * @package Papaya-Library
 * @subpackage CSV
 *
 * @property resource|null $stream
 * @property string $linebreak
 * @property string $encodedLinebreak
 * @property string $separator
 * @property-read int $separatorLength
 * @property string $quote
 */
class Writer implements BaseObject\Interfaces\Properties {
  private $_callbacks;

  private $_stream;

  private $_linebreak = "\n";

  private $_separator = ',';

  private $_separatorLength = 1;

  private $_quote = '"';

  private $_encodedLinebreak = '\n';

  /**
   * Create object and store output stream, if no stream if provided, the standard output will be
   * used.
   *
   * @param resource|null $stream
   * @param bool $addByteOrderMark optional, default FALSE
   */
  public function __construct($stream = NULL, $addByteOrderMark = FALSE) {
    if (NULL !== $stream) {
      $this->_stream = $stream;
    }
    if ($addByteOrderMark) {
      $bom = "\xEF\xBB\xBF";
      if (NULL !== $this->_stream) {
        \fwrite($this->_stream, $bom);
      } else {
        echo $bom;
      }
    }
  }

  /**
   * @param string $name
   * @return bool
   */
  public function __isset($name) {
    switch ($name) {
      case 'stream' :
      case 'linebreak' :
      case 'encodedLinebreak' :
      case 'separator' :
      case 'separatorLength' :
      case 'quote' :
        return TRUE;
      default :
        return FALSE;
    }
  }

  /**
   * Read object properties
   *
   * @param string $name
   *
   * @throws \UnexpectedValueException
   *
   * @return int|null|resource|string
   */
  public function __get($name) {
    switch ($name) {
      case 'stream' :
        return $this->_stream;
      case 'linebreak' :
        return $this->_linebreak;
      case 'encodedLinebreak' :
        return $this->_encodedLinebreak;
      case 'separator' :
        return $this->_separator;
      case 'separatorLength' :
        return $this->_separatorLength;
      case 'quote' :
        return $this->_quote;
      default :
        throw new \UnexpectedValueException(
          \sprintf('Can not read undefined property "%s".', $name)
        );
    }
  }

  /**
   * Write object properties
   *
   * @param string $name
   * @param $value
   *
   * @throws \UnexpectedValueException
   */
  public function __set($name, $value) {
    switch ($name) {
      case 'stream' :
        $this->_stream = $value;
      break;
      case 'linebreak' :
        Utility\Constraints::assertString($value);
        $this->_linebreak = $value;
      break;
      case 'encodedLinebreak' :
        Utility\Constraints::assertString($value);
        $this->_encodedLinebreak = $value;
      break;
      case 'separator' :
        Utility\Constraints::assertString($value);
        $this->_separator = $value;
        $this->_separatorLength = \strlen($this->_separator);
      break;
      case 'separatorLength' :
        throw new \UnexpectedValueException(
          \sprintf('Can not write read only property "%s".', $name)
        );
      case 'quote' :
        Utility\Constraints::assertString($value);
        $this->_quote = $value;
      break;
      default :
        throw new \UnexpectedValueException(
          \sprintf('Can not write undefined property "%s".', $name)
        );
    }
  }

  /**
   * @param string $name
   */
  public function __unset($name) {
    throw new \UnexpectedValueException(
      \sprintf('Can not unset property "%s".', $name)
    );
  }

  /**
   * Write the csv header (the column names) this is basically the same as as writeRow but
   * calls a different callback to map the given column names.
   *
   * @param array|\Traversable $row
   */
  public function writeHeader($row) {
    if (isset($this->callbacks()->onMapHeader)) {
      $row = $this->callbacks()->onMapHeader($row);
    }
    $this->write($row);
  }

  /**
   * Write a csv data row. A callback is executed to map the values if needed.
   *
   * @param array|\Traversable $row
   */
  public function writeRow($row) {
    if (isset($this->callbacks()->onMapRow)) {
      $row = $this->callbacks()->onMapRow($row);
    }
    $this->write($row);
  }

  /**
   * Write multiple csv data rows, for each element of the given parameter writeRow() is called.
   *
   * @param array|\Traversable $list
   */
  public function writeList($list) {
    Utility\Constraints::assertArrayOrTraversable($list);
    foreach ($list as $row) {
      $this->writeRow($row);
    }
  }

  /**
   * Serialize the parameter into an string and write it to the csv output target using
   * writeString().
   *
   * If the output is written to the standard output (and not to a stream) flush() is
   * called.
   *
   * @param $row
   */
  private function write($row) {
    if (\is_array($row) || $row instanceof \Traversable) {
      $result = '';
      foreach ($row as $value) {
        $result .= $this->_separator.$this->quoteValue($value);
      }
      $this->writeString(\substr($result, $this->_separatorLength).$this->_linebreak);
      if (NULL === $this->_stream) {
        \flush();
      }
    }
  }

  /**
   * Prepare a header or data value for csv. The value is escaped and quotes if needed.
   *
   * @param string $value
   *
   * @return string
   */
  private function quoteValue($value) {
    $quotesNeeded =
      '('.\preg_quote($this->_quote, '(').'|'.\preg_quote($this->_separator, '(').'|[\r\n])';
    if (\preg_match($quotesNeeded, $value)) {
      $encoded = \preg_replace(
        [
          '('.\preg_quote($this->_quote, '(').')',
          "(\r\n|\n\r|[\r\n])"
        ],
        [
          $this->_quote.'$0',
          $this->_encodedLinebreak
        ],
        $value
      );
      return $this->_quote.$encoded.$this->_quote;
    }
    return $value;
  }

  /**
   * Write a string to the attached stream or output it if no stream is attached.
   *
   * @param string $string
   */
  private function writeString($string) {
    if (NULL !== $this->_stream) {
      \fwrite($this->_stream, $string);
    } else {
      echo $string;
    }
  }

  /**
   * Getter/Setter for the callbacks subobject handlign the mapping callbacks
   *
   * @param Writer\Callbacks $callbacks
   *
   * @return Writer\Callbacks
   */
  public function callbacks(Writer\Callbacks $callbacks = NULL) {
    if (NULL !== $callbacks) {
      $this->_callbacks = $callbacks;
    } elseif (NULL === $this->_callbacks) {
      $this->_callbacks = new Writer\Callbacks();
    }
    return $this->_callbacks;
  }
}
