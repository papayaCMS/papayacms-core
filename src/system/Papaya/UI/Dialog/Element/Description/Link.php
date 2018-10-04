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
namespace Papaya\UI\Dialog\Element\Description;

use Papaya\UI;
use Papaya\XML;

/**
 * Dialog element description item encapsulationing a simple link.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Link extends Item {
  private $_reference;

  /**
   * Append description element with href attribute to parent xml element.
   *
   * @param XML\Element $parent
   *
   * @return XML\Element
   */
  public function appendTo(XML\Element $parent) {
    return $parent->appendElement(
      'link',
      [
        'href' => $this->reference()->getRelative()
      ]
    );
  }

  /**
   * Getter/Setter for the reference subobject.
   *
   * @param UI\Reference $reference
   *
   * @return UI\Reference
   */
  public function reference(UI\Reference $reference = NULL) {
    if (NULL !== $reference) {
      $this->_reference = $reference;
    } elseif (NULL === $this->_reference) {
      $this->_reference = new UI\Reference();
    }
    return $this->_reference;
  }
}
