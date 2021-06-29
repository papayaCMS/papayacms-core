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

namespace Papaya\Response\Content {

  use Papaya\Response;

  /**
   * Iterate the argument and output it as a CSV.
   *
   * @package Papaya-Library
   * @subpackage Response
   */
  class CSV implements Response\Content {

    public const FLAG_ENABLE_BOM = 1;

    /**
     * string content buffer
     *
     * @var \Traversable
     */
    private $_traversable;

    /**
     * @var string
     */
    private $_quote = '"';

    /**
     * @var string[]
     */
    private $_separators = [',', ';', "\t"];

    /**
     * @var string
     */
    private $_separator = ',';

    /**
     * @var string
     */
    private $_linebreak = "\r\n";

    /**
     * @var string
     */
    private $_encodedLinebreak = '\\n';

    /**
     * @var array
     */
    private $_columns;

    /**
     * @var CSV\Callbacks
     */
    private $_callbacks;

    /**
     * @var bool
     */
    private $_flags;

    /**
     * @param \Traversable $traversable
     * @param array|null $columns
     * @param int $flags
     */
    public function __construct(\Traversable $traversable, array $columns = NULL, $flags = 0) {
      $this->_traversable = $traversable;
      $this->_columns = $columns;
      $this->_flags = $flags;
    }

    public function setSeparator($separator) {
      if (!in_array($separator, $this->_separators, TRUE)) {
        throw new \InvalidArgumentException(
          sprintf('Invalid separator char: "%s"', $separator)
        );
      }
      $this->_separator = $separator;
    }

    /**
     * Getter/Setter for the callbacks, if you set your own callback object, make sure it has the
     * needed definitions.
     *
     * @param CSV\Callbacks $callbacks
     *
     * @return CSV\Callbacks
     */
    public function callbacks(CSV\Callbacks $callbacks = NULL) {
      if (NULL !== $callbacks) {
        $this->_callbacks = $callbacks;
      } elseif (NULL === $this->_callbacks) {
        $this->_callbacks = new CSV\Callbacks();
        $this->_callbacks->onMapRow = function ($value) {
          return $value;
        };
        $this->_callbacks->onMapField = function ($value) {
          return $value;
        };
      }
      return $this->_callbacks;
    }

    /**
     * Return content length for the http header
     *
     * @return int
     */
    public function length() {
      return -1;
    }

    /**
     * Output string content to standard output
     */
    public function output() {
      $callbacks = $this->callbacks();
      if (\Papaya\Utility\Bitwise::inBitmask(self::FLAG_ENABLE_BOM, $this->_flags)) {
        echo "\xEF\xBB\xBF";
        flush();
      }
      if (\is_array($this->_columns)) {
        $this->outputCSVLine($this->_columns);
        \flush();
        foreach ($this->_traversable as $values) {
          $values = $callbacks->onMapRow($values);
          $row = [];
          foreach ($this->_columns as $key => $label) {
            if (isset($values[$key])) {
              $row[] = $callbacks->onMapField($values[$key], $key);
            } else {
              $row[] = '';
            }
          }
          echo $this->outputCSVLine($row);
          \flush();
        }
      } else {
        foreach ($this->_traversable as $values) {
          $values = $callbacks->onMapRow($values);
          $row = [];
          foreach ($values as $key => $value) {
            $row[] = $callbacks->onMapField($value, $key);
          }
          echo $this->outputCSVLine($row);
          \flush();
        }
      }
    }

    /**
     * @param array $values
     */
    private function outputCSVLine($values) {
      $separator = FALSE;
      foreach (\array_values($values) as $value) {
        if ($separator) {
          echo $separator;
        } else {
          $separator = $this->_separator;
        }
        echo $this->csvQuote($value);
      }
      echo $this->_linebreak;
    }

    /**
     * Prepare a header or data value for csv. The value is escaped and quotes if needed.
     *
     * @param string $value
     *
     * @return string
     */
    private function csvQuote($value) {
      $quotesNeeded =
        '('.\preg_quote($this->_quote, '(').'|'.\preg_quote($this->_separator, '(').'|[\r\n])';
      if (\preg_match($quotesNeeded, $value)) {
        $encoded = \preg_replace(
          [
            '('.\preg_quote($this->_quote, '(').')',
            "(\r\n|\n\r|[\r\n])",
          ],
          [
            $this->_quote.'$0',
            $this->_encodedLinebreak,
          ],
          $value
        );
        return $this->_quote.$encoded.$this->_quote;
      }
      return $value;
    }
  }
}
