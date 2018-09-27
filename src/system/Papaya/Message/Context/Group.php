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
 * Message string context containing a group of other context objects
 *
 * This class is used for debug and error mesages, to provide additional information about
 * the callstack.
 *
 * @package Papaya-Library
 * @subpackage Messages
 */
class Group
  implements Interfaces\Text, Interfaces\XHTML, \Iterator, \Countable {
  /**
   * context group elements
   *
   * @var array
   */
  private $_elements = [];

  /**
   * Append a new context element to group
   *
   * @param Data $context
   *
   * @return \Papaya\Message\Context\Group $this
   */
  public function append(Data $context) {
    $this->_elements[] = $context;
    return $this;
  }

  /**
   * Get context elements as string output
   *
   * @return string
   */
  public function asString() {
    $result = '';
    foreach ($this as $element) {
      if ($element instanceof Interfaces\Labeled) {
        $result .= "\n\n".$element->getLabel();
      }
      if ($element instanceof Interfaces\Text) {
        $result .= "\n\n".$element->asString();
      } elseif ($element instanceof Interfaces\XHTML) {
        $result .= "\n\n".Utility\Text\HTML::stripTags($element->asXhtml());
      }
    }
    return \substr($result, 2);
  }

  /**
   * Get context elements as xhtml output
   */
  public function asXhtml() {
    $result = '';
    foreach ($this as $element) {
      $result .= '<div class="group">';
      if ($element instanceof Interfaces\Labeled) {
        $result .= '<h3>'.Utility\Text\XML::escape($element->getLabel()).'</h3>';
      }
      if ($element instanceof Interfaces\XHTML) {
        $result .= $element->asXhtml();
      } elseif ($element instanceof Interfaces\Text) {
        $result .= \str_replace(
          "\n", "\n<br />", Utility\Text\XML::escape($element->asString())
        );
      }
      $result .= '</div>';
    }
    return $result;
  }

  /**
   * Iterator: Rewind position
   */
  public function rewind() {
    \reset($this->_elements);
  }

  /**
   * Iterator: Get current element value
   *
   * @return false|Data
   */
  public function current() {
    return \current($this->_elements);
  }

  /**
   * Iterator: Get current element key
   *
   * @return int|null
   */
  public function key() {
    return \key($this->_elements);
  }

  /**
   * Iterator: Move position to next element
   *
   * @return false|Data
   */
  public function next() {
    return \next($this->_elements);
  }

  /**
   * Iterator: Check if current position hold a valid element
   *
   * @return bool
   */
  public function valid() {
    return FALSE !== $this->current();
  }

  /**
   * Countable: return number of elements
   *
   * @return int
   */
  public function count() {
    return \count($this->_elements);
  }
}
