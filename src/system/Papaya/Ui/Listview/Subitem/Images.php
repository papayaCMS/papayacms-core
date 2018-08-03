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

namespace Papaya\Ui\Listview\Subitem;
/**
 * A listview subitem displaying multiple icons from a given list.
 *
 * @package Papaya-Library
 * @subpackage Ui
 *
 * @property integer $align
 * @property \Papaya\Ui\Icon\Collection $icons
 * @property string $selection
 * @property integer $selectionMode
 * @property array $actionParameters
 */
class Images extends Image\Toggle {

  /**
   * Validate the icon indizes using the values of the selection array
   *
   * @var integer
   */
  const VALIDATE_VALUES = 1;

  /**
   * Validate the icon indizes using the keys of the selection array
   *
   * @var integer
   */
  const VALIDATE_KEYS = 2;

  /**
   * Validate the icon indizes using the selection value as an bitmask. The icon indizes need to be
   * integers for that.
   *
   * @var integer
   */
  const VALIDATE_BITMASK = 3;

  /**
   * how to validate if an icon should be displayed
   *
   * @var integer
   */
  protected $_selectionMode = self::VALIDATE_VALUES;

  /**
   * Allow to assign the internal (protected) variables using a public property
   *
   * @var array
   */
  protected $_declaredProperties = array(
    'align' => array('getAlign', 'setAlign'),
    'icons' => array('_icons', 'setIcons'),
    'selection' => array('_selection', '_selection'),
    'selectionMode' => array('_selectionMode', '_selectionMode'),
    'actionParameters' => array('_actionParameters', 'setActionParameters'),
  );

  /**
   * Create subitme and store icon list and selection index.
   *
   * @param \Papaya\Ui\Icon\Collection $icons
   * @param mixed $selection
   * @param int $selectionMode
   * @param array $actionParameters
   */
  public function __construct(
    \Papaya\Ui\Icon\Collection $icons,
    $selection,
    $selectionMode = self::VALIDATE_VALUES,
    array $actionParameters = NULL
  ) {
    parent::__construct($icons, $selection, $actionParameters);
    $this->_selectionMode = $selectionMode;
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
        'align' => \Papaya\Ui\Option\Align::getString($this->getAlign())
      )
    );
    $list = $subitem->appendElement('glyphs');
    /** @var \Papaya\Ui\Icon $icon */
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
   * @return boolean
   */
  protected function validateSelection($index) {
    switch ($this->selectionMode) {
      case self::VALIDATE_BITMASK :
        $result = (int)$this->_selection & (int)$index;
      break;
      case self::VALIDATE_KEYS :
        $result = array_key_exists($index, (array)$this->_selection);
      break;
      case self::VALIDATE_VALUES :
      default :
        $result = in_array($index, (array)$this->_selection, FALSE);
      break;
    }
    return $result;
  }
}
