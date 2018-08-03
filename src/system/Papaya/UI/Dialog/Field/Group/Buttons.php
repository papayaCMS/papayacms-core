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

namespace Papaya\UI\Dialog\Field\Group;
/**
 * A simple single line input field with a caption.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property string|\Papaya\UI\Text $caption
 * @property \Papaya\UI\Dialog\Buttons $buttons
 */
class Buttons extends \Papaya\UI\Dialog\Field {

  /**
   * Grouped input buttons
   *
   * @var \Papaya\UI\Dialog\Buttons
   */
  protected $_buttons = NULL;

  /**
   * declare dynamic properties
   *
   * @var array
   */
  protected $_declaredProperties = array(
    'caption' => array('getCaption', 'setCaption'),
    'buttons' => array('_buttons', '_buttons')
  );

  /**
   * Initialize object, set caption
   *
   * @param string|\Papaya\UI\Text $caption
   */
  public function __construct($caption) {
    $this->setCaption($caption);
  }

  /**
   * Group buttons getter/setter
   *
   * @param \Papaya\UI\Dialog\Buttons $buttons
   * @return \Papaya\UI\Dialog\Buttons
   */
  public function buttons(\Papaya\UI\Dialog\Buttons $buttons = NULL) {
    if (isset($buttons)) {
      $this->_buttons = $buttons;
      if ($this->hasCollection() && $this->collection()->hasOwner()) {
        $buttons->owner($this->collection()->owner());
      }
    }
    if (is_null($this->_buttons)) {
      $this->_buttons = new \Papaya\UI\Dialog\Buttons(
        $this->hasDialog() ? $this->getDialog() : NULL
      );
    }
    return $this->_buttons;
  }

  /**
   * Return the owner collection of the item.
   *
   * @param \Papaya\UI\Control\Collection $collection
   * @return \Papaya\UI\Control\Collection
   */
  public function collection(\Papaya\UI\Control\Collection $collection = NULL) {
    $result = parent::collection($collection);
    if ($collection != NULL && $collection->hasOwner()) {
      $this->buttons()->owner($collection->owner());
    }
    return $result;
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
      $group = $parent->appendElement(
        'field-group',
        array(
          'caption' => $this->getCaption(),
          'id' => $this->getId()
        )
      );
      $this->_buttons->appendTo($group);
    }
  }
}
