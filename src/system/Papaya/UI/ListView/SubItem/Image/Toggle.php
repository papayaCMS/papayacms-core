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
namespace Papaya\UI\ListView\SubItem\Image;

use Papaya\UI;
use Papaya\XML;

/**
 * A listview subitem displaying an icon from a given list.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property int $align
 * @property UI\Icon\Collection $icons
 * @property string $selection
 * @property array $actionParameters
 */
class Toggle extends UI\ListView\SubItem {
  /**
   * A list of icons
   *
   * @var UI\Icon\Collection
   */
  protected $_icons;

  /**
   * index of the selected icon in the list
   *
   * @var mixed
   */
  protected $_selection;

  /**
   * Allow to assign the internal (protected) variables using a public property
   *
   * @var array
   */
  protected $_declaredProperties = [
    'align' => ['getAlign', 'setAlign'],
    'icons' => ['_icons', 'setIcons'],
    'selection' => ['_selection', '_selection'],
    'actionParameters' => ['_actionParameters', 'setActionParameters'],
  ];

  /**
   * Create subitem and store icon list and selection index.
   *
   * @param UI\Icon\Collection $icons
   * @param mixed $selection
   * @param array $actionParameters
   */
  public function __construct(UI\Icon\Collection $icons, $selection, array $actionParameters = NULL) {
    $this->setIcons($icons);
    $this->_selection = $selection;
    $this->_actionParameters = $actionParameters;
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
    $iconIndex = (string)$this->_selection;
    if (
      isset($this->_icons[$iconIndex]) &&
      ($icon = $this->_icons[$iconIndex]) &&
      $icon instanceof UI\Icon
    ) {
      $icon->appendTo($subitem);
    }
    return $subitem;
  }

  /**
   * Set icons list, the type hint ensures that a valid icon list is set.
   *
   * @param UI\Icon\Collection $icons
   */
  public function setIcons(UI\Icon\Collection $icons) {
    $this->_icons = $icons;
  }
}
