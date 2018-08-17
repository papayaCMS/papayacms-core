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

namespace Papaya\UI\Dialog;
/**
 * Superclass for dialog elements
 *
 * An dialog element can be a simple input field, a button or a complex element with several
 * child elements.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
abstract class Element extends \Papaya\UI\Control\Collection\Item {

  /**
   * Collect filtered dialog input data into $this->_dialog->data()
   */
  public function collect() {
    return $this->collection()->hasOwner();
  }

  /**
   * Get the parameter name
   *
   * If the dialog has a parameter group this will generate an additional parameter array level.
   *
   * If the key is an array is will be converted to a string
   * compatible to PHPs parameter array syntax.
   *
   * @param string|array $key
   * @param boolean $withGroup
   * @return string
   */
  protected function _getParameterName($key, $withGroup = TRUE) {
    $name = new \Papaya\UI\Dialog\Field\Parameter\Name(
      $key, $this->hasDialog() ? $this->getDialog() : NULL
    );
    return $name->get($withGroup);
  }

  /**
   * Check if the element is attached to a collection and the collection attached to a dialog
   *
   * @return bool
   */
  public function hasDialog() {
    if ($this->hasCollection() &&
      $this->collection()->hasOwner()) {
      return ($this->collection()->owner() instanceof \Papaya\UI\Dialog);
    }
    return FALSE;
  }

  /**
   * Return the dialog the elements collection is attached to.
   *
   * @return null|\Papaya\UI\Dialog
   */
  public function getDialog() {
    if ($this->hasDialog()) {
      /** @noinspection PhpIncompatibleReturnTypeInspection */
      return $this->collection()->owner();
    }
    return NULL;
  }
}
