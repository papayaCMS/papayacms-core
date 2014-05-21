<?php
/**
* Message string context containing a group of other context objects
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
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
* @subpackage Messages
* @version $Id: Group.php 38830 2013-09-19 13:54:26Z weinert $
*/

/**
* Message string context containing a group of other context objects
*
* This class is used for debug and error mesages, to provide additional information about
* the callstack.
*
* @package Papaya-Library
* @subpackage Messages
*/
class PapayaMessageContextGroup
  implements
    PapayaMessageContextInterfaceString,
    PapayaMessageContextInterfaceXhtml,
    Iterator,
    Countable {

  /**
  * context group elements
  * @var array
  */
  private $_elements = array();

  /**
  * Append a new context element to group
  *
  * @param PapayaMessageContextInterface $context
  * @return PapayaMessageContextGroup $this
  */
  public function append(PapayaMessageContextInterface $context) {
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
      if ($element instanceof PapayaMessageContextInterfaceLabeled) {
        $result .= "\n\n".$element->getLabel();
      }
      if ($element instanceof PapayaMessageContextInterfaceString) {
        $result .= "\n\n".$element->asString();
      } elseif ($element instanceof PapayaMessageContextInterfaceXhtml) {
        $result .= "\n\n".PapayaUtilStringHtml::stripTags($element->asXhtml());
      }
    }
    return substr($result, 2);
  }

  /**
  * Get context elements as xhtml output
  */
  public function asXhtml() {
    $result = '';
    foreach ($this as $element) {
      $result .= '<div class="group">';
      if ($element instanceof PapayaMessageContextInterfaceLabeled) {
        $result .= '<h3>'.PapayaUtilStringXml::escape($element->getLabel()).'</h3>';
      }
      if ($element instanceof PapayaMessageContextInterfaceXhtml) {
        $result .= $element->asXhtml();
      } elseif ($element instanceof PapayaMessageContextInterfaceString) {
        $result .= str_replace(
          "\n", "\n<br />", PapayaUtilStringXml::escape($element->asString())
        );
      }
      $result .= '</div>';
    }
    return $result;
  }

  /**
  * Iterator: Rewind position
  *
  * @return void
  */
  public function rewind() {
    reset($this->_elements);
  }

  /**
  * Iterator: Get current element value
  * @return FALSE|PapayaMessageContextInterface
  */
  public function current() {
    return current($this->_elements);
  }

  /**
  * Iterator: Get current element key
  *
  * @return integer|NULL
  */
  public function key() {
    return key($this->_elements);
  }

  /**
  * Iterator: Move position to next element
  *
  * @return FALSE|PapayaMessageContextInterface
  */
  public function next() {
    return next($this->_elements);
  }

  /**
  * Iterator: Check if current position hold a valid element
  *
  * @return boolean
  */
  public function valid() {
    return $this->current() !== FALSE;
  }

  /**
  * Countable: return number of elements
  *
  * @return integer
  */
  public function count() {
    return count($this->_elements);
  }
}
