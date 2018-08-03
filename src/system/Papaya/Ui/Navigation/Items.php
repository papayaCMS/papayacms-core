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

namespace Papaya\Ui\Navigation;
/**
 * An navigation items list.
 *
 * @package Papaya-Library
 * @subpackage Ui
 */
class Items extends \Papaya\Ui\Control\Collection {

  private $_reference;

  /**
   * Only \Papaya\Ui\Dialog\PapayaUiDialogElement objects are allows in this list
   *
   * @var string
   */
  protected $_itemClass = Item::class;

  protected $_tagName = 'links';

  /**
   * Getter/Setter for a reference subobject to create detail page links
   *
   * @param \Papaya\Ui\Reference $reference
   * @return \Papaya\Ui\Reference
   */
  public function reference(\Papaya\Ui\Reference $reference = NULL) {
    if (NULL !== $reference) {
      $this->_reference = $reference;
    } elseif (NULL === $this->_reference) {
      $this->_reference = new \Papaya\Ui\Reference\Page();
      $this->_reference->papaya($this->papaya());
    }
    return $this->_reference;
  }
}
