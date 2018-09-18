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

namespace Papaya\UI\Message;

/**
 * User message with an xml fragment as message text.
 *
 * The given string is appended as xml fragment, so it needs to be a valid xml fragment. This
 * means it does not need a root node, but it has to be possible to create text and element nodes
 * from it.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class XML extends Text {
  /**
   * Use the parent method to append the element and append the xml fragment to the new
   * message xml element node.
   *
   * @param \Papaya\XML\Element $parent
   * @return \Papaya\XML\Element
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    $message = parent::appendMessageElement($parent);
    if ($xmlFragment = $this->getContent()) {
      $message->appendXML($xmlFragment);
    }
    return $message;
  }
}
