<?php
/**
 * Iterate the argument and output it as a CSV.
 *
 * @copyright 2016 by papaya Software GmbH - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 * You can redistribute and/or modify this script under the terms of the GNU General Public
 * License (GPL) version 2, provided that the copyright and license notes, including these
 * lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.
 *
 * @package Papaya-Library
 * @subpackage Response
 * @version $Id: File.php 39115 2014-02-05 15:47:37Z weinert $
 */

/**
 * Iterate the argument and output it as a CSV.
 *
 * @package Papaya-Library
 * @subpackage Response
 */
class PapayaResponseContentCsv implements PapayaResponseContent {

  /**
   * string content buffer
   * @var Traversable
   */
  private $_traversable;

  private $_quote = '"';
  private $_separator = ',';
  private $_linebreak = "\r\n";
  private $_encodedLinebreak = '\\n';

  /**
   * @param Traversable $traversable
   * @param string $lineEnd
   */
  public function __construct(Traversable $traversable, array $columns = NULL, callable $onMapValue = NULL) {
    $this->_traversable = $traversable;
    $this->_columns = $columns;
    $this->_onMapValue = $onMapValue ?: function($value) { return $value; };
  }

  /**
   * Return content length for the http header
   *
   * @return integer
   */
  public function length() {
    return -1;
  }

  /**
   * Output string content to standard output
   *
   * @return string
   */
  public function output() {
    $onMapValue = $this->_onMapValue;
    if (is_array($this->_columns)) {
      $this->outputCSVLine($this->_columns);
      flush();
      foreach ($this->_traversable as $values) {
        $row = [];
        foreach ($this->_columns as $key => $label) {
          if (isset($values[$key])) {
            $row[] = $onMapValue($values[$key], $key);
          } else {
            $row[] = '';
          }
        }
        echo $this->outputCSVLine($row);
        flush();
      }
    } else {
      foreach ($this->_traversable as $values) {
        $row = [];
        foreach ($values as $key => $value) {
          $row[] = $onMapValue($value, $key);
        }
        echo $this->outputCSVLine($row);
        flush();
      }
    }
  }

  private function outputCSVLine($values) {
    $seperator = FALSE;
    foreach (array_values($values) as $value) {
      if ($seperator) {
        echo $seperator;
      } else {
        $seperator = $this->_separator;
      }
      echo $this->csvQuote($value);
    }
    echo $this->_linebreak;
  }

  /**
   * Prepare a header or data value for csv. The value is escaped and quotes if needed.
   *
   * @param string $value
   * @return string
   */
  private function csvQuote($value) {
    $quotesNeeded =
      '('.preg_quote($this->_quote).'|'.preg_quote($this->_separator).'|[\r\n])';
    if (preg_match($quotesNeeded, $value)) {
      $encoded = preg_replace(
        array(
          '('.preg_quote($this->_quote).')',
          "(\r\n|\n\r|[\r\n])"
        ),
        array(
          $this->_quote.'$0',
          $this->_encodedLinebreak
        ),
        $value
      );
      return $this->_quote.$encoded.$this->_quote;
    } else {
      return $value;
    }
  }
}