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
namespace Papaya\UI\Navigation;

use Papaya\UI;

/**
 * An navigation items list.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
class Items extends UI\Control\Collection {
  private $_reference;

  /**
   * Only \Papaya\UI\Dialog\Item objects are allows in this list
   *
   * @var string
   */
  protected $_itemClass = Item::class;

  protected $_tagName = 'links';

  /**
   * Getter/Setter for a reference subobject to create detail page links
   *
   * @param UI\Reference $reference
   *
   * @return UI\Reference
   */
  public function reference(UI\Reference $reference = NULL) {
    if (NULL !== $reference) {
      $this->_reference = $reference;
    } elseif (NULL === $this->_reference) {
      if ($this->papaya()->pageReferences) {
        $this->_reference = $this->papaya()->pageReferences->create();
      } else {
        $this->_reference = new UI\Reference();
        $this->_reference->papaya($this->papaya());
      }
    }
    return $this->_reference;
  }
}
