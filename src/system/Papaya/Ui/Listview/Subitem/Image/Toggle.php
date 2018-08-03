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

namespace Papaya\Ui\Listview\Subitem\Image;
/**
 * A listview subitem displaying an icon from a given list.
 *
 * @package Papaya-Library
 * @subpackage Ui
 *
 * @property integer $align
 * @property \Papaya\Ui\Icon\Collection $icons
 * @property string $selection
 * @property array $actionParameters
 */
class Toggle extends \Papaya\Ui\Listview\Subitem {

  /**
   * A list of icons
   *
   * @var \Papaya\Ui\Icon\Collection
   */
  protected $_icons = NULL;

  /**
   * index of the selected icon in the list
   *
   * @var mixed
   */
  protected $_selection = NULL;

  /**
   * Allow to assign the internal (protected) variables using a public property
   *
   * @var array
   */
  protected $_declaredProperties = array(
    'align' => array('getAlign', 'setAlign'),
    'icons' => array('_icons', 'setIcons'),
    'selection' => array('_selection', '_selection'),
    'actionParameters' => array('_actionParameters', 'setActionParameters'),
  );

  /**
   * Create subitme and store icon list and selection index.
   *
   * @param \Papaya\Ui\Icon\Collection $icons
   * @param mixed $selection
   * @param array $actionParameters
   */
  public function __construct(\Papaya\Ui\Icon\Collection $icons, $selection, array $actionParameters = NULL) {
    $this->setIcons($icons);
    $this->_selection = $selection;
    $this->_actionParameters = $actionParameters;
  }

  /**
   * Append the subitem to the listitem xml element. If the selected icon is not found
   * the subitem will be empty.
   *
   * @param \Papaya\Xml\Element
   * @return \Papaya\Xml\Element
   */
  public function appendTo(\Papaya\Xml\Element $parent) {
    $subitem = $parent->appendElement(
      'subitem',
      array(
        'align' => \PapayaUiOptionAlign::getString($this->getAlign())
      )
    );
    $iconIndex = (string)$this->_selection;
    if (isset($this->_icons[$iconIndex]) &&
      ($icon = $this->_icons[$iconIndex]) &&
      $icon instanceof \PapayaUiIcon) {
      $icon->appendTo($subitem);
    }
    return $subitem;
  }

  /**
   * Set icons list, the typehint ensures that a valid icon list is set.
   *
   * @param \Papaya\Ui\Icon\Collection $icons
   */
  public function setIcons(\Papaya\Ui\Icon\Collection $icons) {
    $this->_icons = $icons;
  }
}
