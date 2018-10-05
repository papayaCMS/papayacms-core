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
namespace Papaya\UI;

use Papaya\BaseObject;
use Papaya\XML;

/**
 * A list object that is used to output user messages into xml.
 *
 * This are visible messages for a user that is requesting a page. They can be occurred messages
 * or messages that are used in javascript.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Messages extends BaseObject\Collection implements XML\Appendable {
  /**
   * create list object and store child superclass limit
   */
  public function __construct() {
    parent::__construct(Message::class);
  }

  /**
   * If the list contains items, append them and return the list xml element.
   *
   * @param XML\Element $parent
   *
   * @return XML\Element|null
   */
  public function appendTo(XML\Element $parent) {
    if (!$this->isEmpty()) {
      $list = $parent->appendElement('messages');
      /** @var Message $item */
      foreach ($this as $item) {
        $list->append($item);
      }
      return $list;
    }
    return NULL;
  }

  /**
   * Return object items as xml string
   *
   * @see appendTo
   *
   * @return string
   */
  public function getXML() {
    if (!$this->isEmpty()) {
      $document = new XML\Document();
      $root = $document->appendElement('root');
      $this->appendTo($root);
      return $document->saveXML($root->firstChild);
    }
    return '';
  }
}
