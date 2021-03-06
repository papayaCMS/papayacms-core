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

use Papaya\UI;
use Papaya\XML;

/**
 * A simple single line input field with a caption.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property string|\Papaya\UI\Text $caption
 * @property UI\Dialog\Buttons $buttons
 */
class Buttons extends UI\Dialog\Field {
  /**
   * Grouped input buttons
   *
   * @var UI\Dialog\Buttons
   */
  protected $_buttons;

  /**
   * declare dynamic properties
   *
   * @var array
   */
  protected $_declaredProperties = [
    'caption' => ['getCaption', 'setCaption'],
    'buttons' => ['_buttons', '_buttons']
  ];

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
   * @param UI\Dialog\Buttons $buttons
   *
   * @return UI\Dialog\Buttons
   */
  public function buttons(UI\Dialog\Buttons $buttons = NULL) {
    if (NULL !== $buttons) {
      $this->_buttons = $buttons;
      if ($this->hasCollection() && $this->collection()->hasOwner()) {
        $buttons->owner($this->collection()->owner());
      }
    }
    if (NULL === $this->_buttons) {
      $this->_buttons = new UI\Dialog\Buttons(
        $this->hasDialog() ? $this->getDialog() : NULL
      );
    }
    return $this->_buttons;
  }

  /**
   * Return the owner collection of the item.
   *
   * @param UI\Control\Collection $collection
   *
   * @return UI\Control\Collection
   */
  public function collection(UI\Control\Collection $collection = NULL) {
    $result = parent::collection($collection);
    if (NULL !== $collection && $collection->hasOwner()) {
      $this->buttons()->owner($collection->owner());
    }
    return $result;
  }

  /**
   * Validate field group
   *
   * @return bool
   */
  public function validate() {
    return TRUE;
  }

  /**
   * Collect field group data
   *
   * @return bool
   */
  public function collect() {
    if (NULL !== $this->_buttons && parent::collect()) {
      $this->_buttons->collect();
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Append group and buttons in this group to the DOM.
   *
   * @param XML\Element $parent
   */
  public function appendTo(XML\Element $parent) {
    if (NULL !== $this->_buttons && \count($this->_buttons) > 0) {
      $group = $parent->appendElement(
        'field-group',
        [
          'caption' => $this->getCaption(),
          'id' => $this->getId()
        ]
      );
      $this->_buttons->appendTo($group);
    }
  }
}
