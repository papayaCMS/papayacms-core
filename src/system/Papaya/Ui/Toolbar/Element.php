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

namespace Papaya\Ui\Toolbar;
/**
 * Superclass for menu elements. All menu elements must be children of this class.
 *
 * @package Papaya-Library
 * @subpackage Ui
 */
abstract class Element extends \Papaya\Ui\Control\Collection\Item {

  /**
   * reference (link) object
   *
   * @var \Papaya\Ui\Reference
   */
  protected $_reference;

  /**
   * Getter/Setter for the reference object (the link url)
   *
   * @param \Papaya\Ui\Reference $reference
   * @return \Papaya\Ui\Reference
   */
  public function reference(\Papaya\Ui\Reference $reference = NULL) {
    if (NULL !== $reference) {
      $this->_reference = $reference;
    } elseif (NULL === $this->_reference) {
      $this->_reference = new \Papaya\Ui\Reference();
      $this->_reference->papaya($this->papaya());
    }
    return $this->_reference;
  }
}
