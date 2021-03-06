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
namespace Papaya\UI\ListView\SubItem;

use Papaya\UI;
use Papaya\XML;

/**
 * A listview subitem displaying multiple icons from a given list.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property int $align
 * @property UI\Icon\Collection $icons
 * @property string $selection
 * @property int $selectionMode
 * @property array $actionParameters
 */
class Images extends Image\Toggle {
  /**
   * Validate the icon indices using the values of the selection array
   *
   * @var int
   */
  const VALIDATE_VALUES = 1;

  /**
   * Validate the icon indices using the keys of the selection array
   *
   * @var int
   */
  const VALIDATE_KEYS = 2;

  /**
   * Validate the icon indices using the selection value as an bitmask. The icon indices need to be
   * integers for that.
   *
   * @var int
   */
  const VALIDATE_BITMASK = 3;

  /**
   * how to validate if an icon should be displayed
   *
   * @var int
   */
  protected $_selectionMode = self::VALIDATE_VALUES;

  /**
   * Allow to assign the internal (protected) variables using a public property
   *
   * @var array
   */
  protected $_declaredProperties = [
    'align' => ['getAlign', 'setAlign'],
    'icons' => ['_icons', 'setIcons'],
    'selection' => ['_selection', '_selection'],
    'selectionMode' => ['_selectionMode', '_selectionMode'],
    'actionParameters' => ['_actionParameters', 'setActionParameters'],
  ];

  /**
   * Create subitem and store icon list and selection index.
   *
   * @param UI\Icon\Collection $icons
   * @param mixed $selection
   * @param int $selectionMode
   * @param array $actionParameters
   */
  public function __construct(
    UI\Icon\Collection $icons,
    $selection,
    $selectionMode = self::VALIDATE_VALUES,
    array $actionParameters = NULL
  ) {
    parent::__construct($icons, $selection, $actionParameters);
    $this->_selectionMode = $selectionMode;
  }

  /**
   * Append the subitem to the list item xml element. If the selected icon is not found
   * the subitem will be empty.
   *
   * @param XML\Element $parent
   *
   * @return XML\Element
   */
  public function appendTo(XML\Element $parent) {
    $subitem = $this->_appendSubItemTo($parent);
    $list = $subitem->appendElement('glyphs');
    /** @var \Papaya\UI\Icon $icon */
    foreach ($this->_icons as $index => $icon) {
      $icon = clone $icon;
      if (!$this->validateSelection($index)) {
        $icon->visible = FALSE;
      }
      $icon->appendTo($list);
    }
    return $subitem;
  }

  /**
   * Validate the icon index against the selection depending on the mode.
   *
   * @param mixed $index
   *
   * @return bool
   */
  protected function validateSelection($index) {
    switch ($this->selectionMode) {
      case self::VALIDATE_BITMASK :
        $result = (int)$this->_selection & (int)$index;
      break;
      case self::VALIDATE_KEYS :
        $result = \array_key_exists($index, (array)$this->_selection);
      break;
      case self::VALIDATE_VALUES :
      default :
        $result = \in_array($index, (array)$this->_selection, FALSE);
      break;
    }
    return $result;
  }
}
