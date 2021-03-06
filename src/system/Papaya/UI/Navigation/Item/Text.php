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
namespace Papaya\UI\Navigation\Item;

use Papaya\UI;
use Papaya\XML;

/**
 * An navigation item with a caption text.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Text extends UI\Navigation\Item {
  /**
   * Use the parent method to create and append to xml element not. Set the text content
   * for the create xml element using the source member variable.
   *
   * @see \Papaya\UI\Navigation\Item#appendTo($parent)
   *
   * @param XML\Element $parent
   *
   * @return XML\Element
   */
  public function appendTo(XML\Element $parent) {
    $result = parent::appendTo($parent);
    $result->appendText(
      (string)$this->_sourceValue
    );
    return $result;
  }
}
