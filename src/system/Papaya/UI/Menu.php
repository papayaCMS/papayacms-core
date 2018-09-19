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
namespace Papaya\UI;

/**
 * A menu gui control. This is a list of menu elements like buttons, separators and selects,
 * maybe grouped.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property string $identifier
 * @property \Papaya\UI\Toolbar\Elements $elements
 */
class Menu extends Toolbar {
  /**
   * An identifier/name for the menu
   *
   * @var string
   */
  protected $_identifier = '';

  /**
   * Delcare public properties
   *
   * @var array
   */
  protected $_declaredProperties = [
    'identifier' => ['_identifier', '_identifier'],
    'elements' => ['elements', 'elements']
  ];

  /**
   * Append menu and elements and set identifier if available
   *
   * @param \Papaya\XML\Element $parent
   *
   * @return \Papaya\XML\Element|null
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    if (\count($this->elements()) > 0) {
      $menu = $parent->appendElement('menu');
      if (!empty($this->_identifier)) {
        $menu->setAttribute('ident', (string)$this->_identifier);
      }
      $this->elements()->appendTo($menu);
      return $menu;
    }
    return;
  }
}
