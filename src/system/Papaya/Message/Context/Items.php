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

/**
 * Message context containing simple plain text
 *
 * @package Papaya-Library
 * @subpackage Messages
 */
class Items
  implements
  \Papaya\Message\Context\Interfaces\Items,
  \Papaya\Message\Context\Interfaces\XHTML,
  \Papaya\Message\Context\Interfaces\Text {
  /**
   * List label/caption
   *
   * @var string
   */
  private $_label = '';

  /**
   * list items
   *
   * @var array
   */
  private $_items = [];

  /**
   * Create list context
   *
   * @param $label
   * @param array $items
   */
  public function __construct($label, array $items) {
    $this->_label = $label;
    $this->_items = $items;
  }

  public function getLabel() {
    return $this->_label;
  }

  /**
   * Return list as simple array
   *
   * @return string
   */
  public function asArray() {
    return $this->_items;
  }

  /**
   * Get a string representation of the list
   *
   * @return string
   */
  public function asString() {
    return \implode("\n", $this->_items);
  }

  /**
   * Get a xhtml representation of the list
   *
   * @return string
   */
  public function asXhtml() {
    if (\count($this->_items) > 0) {
      $result = '<ol>';
      foreach ($this->_items as $item) {
        $result .= '<li>'.\Papaya\Utility\Text\XML::escape($item).'</li>';
      }
      $result .= '</ol>';
      return $result;
    }
    return '';
  }
}
