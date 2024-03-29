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

use Papaya\Utility;

/**
 * CSV reader class
 *
 * @package Papaya-Library
 * @subpackage CSV
 */
class Reader implements \IteratorAggregate {
  /**
   * Maximum file size
   *
   * @var int
   */
  private $_maxFileSize = 0;

  /**
   * Maximum line size
   *
   * @var int
   */
  private $_maxLineSize = 32000;

  /**
   * CSV file name
   *
   * @var string
   */
  private $_fileName;

  /**
   * @var array
   */
  private $_values;

  /**
   * Initialize reader object and set file name.
   *
   * @param string $fileName
   */
  public function __construct($fileName) {
    Utility\Constraints::assertString($fileName);
    Utility\Constraints::assertNotEmpty($fileName);
    $this->_fileName = $fileName;
  }

  public function getFileName(): string {
    return $this->_fileName;
  }

  /**
   * Set a byte maximum for the allowed file size.
   *
   * @param int $size
   */
  public function setMaximumFileSize($size) {
    Utility\Constraints::assertInteger($size);
    $this->_maxFileSize = $size;
  }

  public function getMaximumFileSize(): int {
    return $this->_maxFileSize;
  }

  /**
   * Set the maximum count of bytes readed for a line.
   *
   * @param int $size
   */
  public function setMaximumLineSize($size) {
    Utility\Constraints::assertInteger($size);
    $this->_maxLineSize = $size;
  }

  public function getMaximumLineSize(): int {
    return $this->_maxLineSize;
  }

  /**
   * Check if the given file can be loaded.
   *
   * Throws differenc exceptions depending on the error.
   *
   * @param bool $allowLocal
   *
   * @return true
   *
   * @throws \UnexpectedValueException
   * @throws \LogicException
   * @throws \LengthException
   */
  public function isValid($allowLocal = FALSE) {
    if (
      \file_exists($this->_fileName) &&
      \is_file($this->_fileName) &&
      \is_readable($this->_fileName)
    ) {
      if ($allowLocal || \is_uploaded_file($this->_fileName)) {
        $fileSize = \filesize($this->_fileName);
        if ($fileSize <= 0) {
          throw new \LengthException('File is empty.');
        }
        if ($this->_maxFileSize > 0 && $fileSize > $this->_maxFileSize) {
          throw new \LengthException('File is to large.');
        }
        return TRUE;
      }
      throw new \LogicException('Local files are not allowed.');
    }
    throw new \UnexpectedValueException('Can not read file.');
  }

  /**
   * Fetch data from csv using the first line for column names.
   *
   * If $limit i greater 0, limit the result to this count of records.
   *
   * The $offset parameter is a byte offset. It will be set to the new offset after the execution.
   *
   * @param int $offset byte offset to start reading, new offset after reading
   * @param int $limit maximum lines to read
   *
   * @return array|null
   */
  public function fetchAssoc(&$offset, $limit = 0) {
    if ($fh = $this->_getFileResource()) {
      $style = $this->_getStyle($fh);
      list($titles) = $this->_readLine($fh, $style['separator'], $style['enclosure']);
      if ($offset > 0) {
        \fseek($fh, $offset, SEEK_SET);
      }
      $result = [];
      while (TRUE) {
        if ($data = $this->_readLine($fh, $style['separator'], $style['enclosure'])) {
          $offset = $data[1];
          $row = [];
          foreach ($data[0] as $i => $content) {
            if (isset($titles[$i])) {
              $row[$titles[$i]] = $content;
            }
          }
          $result[] = $row;
          if ($limit > 0 && \count($result) >= $limit) {
            \fclose($fh);
            return $result;
          }
        } else {
          break;
        }
      }
      \fclose($fh);
      return $result;
    }
    return NULL;
  }

  public function getIterator(): \Traversable {
    if (NULL === $this->_values) {
      $offset = 0;
      $this->_values = $this->fetchAssoc($offset);
    }
    return new \ArrayIterator($this->_values ?: []);
  }

  /**
   * Open the file and return the resource handle
   *
   * @return \Resource
   */
  protected function _getFileResource() {
    return \fopen($this->_fileName, 'rb');
  }

  /**
   * Use the second line of a given file resource to determine the csv style.
   *
   * @param \Resource $fh
   *
   * @return array
   */
  protected function _getStyle($fh) {
    $result = [
      'separator' => ',',
      'enclosure' => '"',
    ];
    \fgets($fh, $this->_maxLineSize);
    // better take the second line
    $line = \fgets($fh, $this->_maxLineSize);
    $separator = $this->_getFirstCharacter($line, [',', ';', "\t"]);
    if (NULL !== $separator && '' !== $separator) {
      $result['separator'] = $separator;
    }
    $enclosure = $this->_getFirstCharacter($line, ['"', "'"]);
    if (NULL !== $enclosure && '' !== $enclosure) {
      $result['enclosure'] = $enclosure;
    }
    \fseek($fh, 0, SEEK_SET);
    return $result;
  }

  /**
   * Get that char of a list of character that occurs in a string first
   *
   * @param string $string string to check
   * @param array $characters array of characters
   *
   * @return string $char character that occurs first, otherwise an empty string
   */
  protected function _getFirstCharacter($string, $characters) {
    $charPos = [];
    foreach ($characters as $char) {
      $position = \strpos($string, $char);
      if (FALSE !== $position) {
        $charPos[$position] = $char;
      }
    }
    if (\count($charPos) > 0) {
      // order chars by position
      \ksort($charPos);
      // result is char with lowest position
      return (string)\array_shift($charPos);
    }
    return '';
  }

  /**
   * Read a line from csv, parse it into an array and return array and new offset
   *
   * @param resource $fh
   * @param string $delimiter
   * @param string $enclosure
   *
   * @return array|false
   */
  protected function _readLine($fh, $delimiter, $enclosure) {
    $delimiter = \preg_quote($delimiter, '(');
    $enclosure = \preg_quote($enclosure, '(');
    $escape = \preg_quote($enclosure, '(');
    $prefix = '(?:^)';
    $postfix = "(?:$delimiter|$)";
    $quotedValue = "(?:$enclosure((?:[^$enclosure]|$escape$enclosure)*)$enclosure)";
    $unquotedValue = "([^$delimiter$enclosure]*)";
    $pattern = "($prefix(?:$quotedValue|$unquotedValue)$postfix)S";

    $buffer = '';
    $result = [];
    $offset = 0;
    do {
      $tmpBuffer = \fgets($fh, $this->_maxLineSize);
      if (FALSE === $tmpBuffer) {
        /* most likely EOF, but may be any error
           e.g. the csv may be invalid */
        return FALSE;
      }

      // strip a newline at the end that is not part of any data
      $bufferLength = \strlen($tmpBuffer);
      $lineEnd = "\n";
      if ("\n" === $tmpBuffer[$bufferLength - 1]) {
        if ($bufferLength > 1 && "\r" === $tmpBuffer[$bufferLength - 2]) {
          $buffer .= \substr($tmpBuffer, 0, $bufferLength - 2);
          $lineEnd = "\r\n";
        } else {
          $buffer .= \substr($tmpBuffer, 0, $bufferLength - 1);
        }
      } else {
        $buffer .= $tmpBuffer;
      }
      $bufferLength = \strlen($buffer);

      /* no error checking for an invalid pattern,
         that should already result in a notice */
      while (1 === \preg_match($pattern, \substr($buffer, $offset), $matches, PREG_OFFSET_CAPTURE) &&
        $offset < $bufferLength) {
        if (empty($matches[1][0]) && isset($matches[2][0])) {
          $result[] = $matches[2][0];
        } else {
          $result[] = \preg_replace("($escape(.))", '$1', $matches[1][0]);
        }
        $offset += \strlen($matches[0][0]);
      }
      // put the newline back we earlier removed
      $buffer .= $lineEnd;
      // also get the next line if this line can not be fully consumed
    } while ($offset < $bufferLength);
    return [$result, \ftell($fh)];
  }
}
