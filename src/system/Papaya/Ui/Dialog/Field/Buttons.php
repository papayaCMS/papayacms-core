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

namespace Papaya\Ui\Dialog\Field;
/**
 * A dialog field with several buttons
 *
 * @package Papaya-Library
 * @subpackage Ui
 *
 * @property string|\PapayaUiString $caption
 * @property \Papaya\Ui\Dialog\Buttons $buttons
 */
class Buttons extends \Papaya\Ui\Dialog\Field {

  /**
   * Grouped input buttons
   *
   * @var \Papaya\Ui\Dialog\Buttons
   */
  protected $_buttons = NULL;

  /**
   * declare dynamic properties
   *
   * @var array
   */
  protected $_declaredProperties = array(
    'buttons' => array('buttons', 'buttons')
  );


  /**
   * Group buttons getter/setter
   *
   * @param \Papaya\Ui\Dialog\Buttons $buttons
   * @return \Papaya\Ui\Dialog\Buttons
   */
  public function buttons(\Papaya\Ui\Dialog\Buttons $buttons = NULL) {
    if (isset($buttons)) {
      $this->_buttons = $buttons;
      if ($this->hasCollection() && $this->collection()->hasOwner()) {
        $buttons->owner($this->collection()->owner());
      }
    }
    if (is_null($this->_buttons)) {
      $this->_buttons = new \Papaya\Ui\Dialog\Buttons(
        $this->hasDialog() ? $this->getDialog() : NULL
      );
    }
    return $this->_buttons;
  }

  /**
   * Validate field group
   *
   * @return boolean
   */
  public function validate() {
    return TRUE;
  }

  /**
   * Collect field group data
   *
   * @return boolean
   */
  public function collect() {
    if (parent::collect() &&
      isset($this->_buttons)) {
      $this->_buttons->collect();
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Append group and buttons in this group to the DOM.
   *
   * @param \Papaya\Xml\Element $parent
   */
  public function appendTo(\Papaya\Xml\Element $parent) {
    if (isset($this->_buttons) && count($this->_buttons) > 0) {
      $field = $this->_appendFieldTo($parent);
      $field
        ->appendElement('buttons')
        ->append($this->_buttons);
    }
  }
}
