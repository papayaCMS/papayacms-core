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
namespace Papaya\Message\Context;

use Papaya\Utility;

/**
 * Papaya message context for tabular data
 *
 * @package Papaya-Library
 * @subpackage Message
 */
class Table
  implements Interfaces\Table, Interfaces\Text, Interfaces\XHTML {
  /**
   * Field/column identifiers
   *
   * @var array
   */
  private $_fields = [];

  /**
   * Field/column captions
   *
   * @var array|null
   */
  private $_captions;

  /**
   * Data rows
   *
   * @var array
   */
  private $_rows = [];

  /**
   * @var string
   */
  private $_label;

  /**
   * Initialize object and set the label property
   *
   * @param string $label
   */
  public function __construct($label) {
    $this->_label = $label;
  }

  /**
   * Get the context label (group caption)
   *
   * @return string
   */
  public function getLabel() {
    return $this->_label;
  }

  /**
   * Set the column identifiers and captions using a key => value array.
   *
   * This will delete all existing rows
   *
   * @param array $columns
   *
   * @throws \InvalidArgumentException
   */
  public function setColumns(array $columns) {
    if (\count($columns) > 0) {
      $this->_captions = $columns;
      $this->_fields = \array_keys($columns);
      $this->_rows = [];
    } else {
      throw new \InvalidArgumentException(
        \sprintf(
          'Argument $columns of %s::%s can not be empty.',
          __CLASS__,
          __METHOD__
        )
      );
    }
  }

  /**
   * Get the table column headers
   *
   * return array
   */
  public function getColumns() {
    return $this->_captions;
  }

  public function getFields() {
    return $this->_fields;
  }

  /**
   * Add a row to the context.
   *
   * If no columns are set, they are compiled from the keys in the $values argument.
   *
   * @param array $values
   */
  public function addRow(array $values) {
    if (NULL === $this->_captions) {
      foreach ($values as $field => $content) {
        if (!\in_array($field, $this->_fields, FALSE)) {
          $this->_fields[] = $field;
        }
      }
    }
    $this->_rows[] = $values;
  }

  /**
   * Get a table row specified by the row index.
   *
   * This method return an array with all column identifiers as keys and the values found
   * in the current row. If a column identifier has no value for a column, NULL is used.
   *
   * @param int $position
   *
   * @return array
   */
  public function getRow($position) {
    $result = [];
    $row = isset($this->_rows[$position]) ? $this->_rows[$position] : [];
    foreach ($this->_fields as $field) {
      $result[$field] = isset($row[$field]) ? $row[$field] : NULL;
    }
    return $result;
  }

  /**
   * Return the row count
   *
   * @return int
   */
  public function getRowCount() {
    return \count($this->_rows);
  }

  /**
   * Compile a simple plain text output and return it
   *
   * This will result in a list of records rather then a table.
   *
   * @return string
   */
  public function asString() {
    $result = '';
    if (\count($this->_rows) > 0) {
      foreach (\array_keys($this->_rows) as $rowIndex) {
        foreach ($this->getRow($rowIndex) as $column => $content) {
          if (NULL !== $this->_captions) {
            if (
              NULL !== $content &&
              isset($this->_captions[$column])
            ) {
              $result .= $this->_captions[$column].': '.$content."\n";
            }
          } else {
            $result .= '- '.$content."\n";
          }
        }
        $result .= "\n";
      }
    }
    return $result;
  }

  /**
   * Compile a array output from table, with one element for each table row
   *
   * @return array
   */
  public function asArray() {
    $result = [];
    if (\count($this->_rows) > 0) {
      foreach (\array_keys($this->_rows) as $rowIndex) {
        $line = '';
        foreach ($this->getRow($rowIndex) as $column => $content) {
          if (NULL !== $this->_captions) {
            if (
              NULL !== $content &&
              isset($this->_captions[$column])
            ) {
              $line .= ', '.$this->_captions[$column].': '.$content;
            }
          } else {
            $line .= '| '.$content.' ';
          }
        }
        $result[] = \substr($line, 2);
      }
    }
    return $result;
  }

  /**
   * Compile a xhtml table output from the context and return it.
   *
   * @return string
   */
  public function asXhtml() {
    $hasCaptions = NULL !== $this->_captions;
    $hasRows = \count($this->_rows) > 0;
    if ($hasCaptions || $hasRows) {
      $result = '<table class="logContext" summary="">';
      if ($hasCaptions) {
        $result .= '<thead><tr>';
        foreach ($this->_captions as $caption) {
          $result .= '<th>'.Utility\Text\XML::escape($caption).'</th>';
        }
        $result .= '</tr></thead>';
      }
      if ($hasRows) {
        $result .= '<tbody>';
        foreach (\array_keys($this->_rows) as $rowIndex) {
          $result .= '<tr>';
          foreach ($this->getRow($rowIndex) as $content) {
            $result .= '<td>'.Utility\Text\XML::escape($content).'</td>';
          }
          $result .= '</tr>';
        }
        $result .= '</tbody>';
      }
      $result .= '</table>';
      return $result;
    }
    return '';
  }
}
