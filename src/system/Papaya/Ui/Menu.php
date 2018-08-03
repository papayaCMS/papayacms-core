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

/**
* A menu gui control. This is a list of menu elements like buttons, separators and selects,
* maybe grouped.
*
* @package Papaya-Library
* @subpackage Ui
*
* @property string $identifier
* @property \Papaya\Ui\Toolbar\Elements $elements
*/
class PapayaUiMenu extends \Papaya\Ui\Toolbar {

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
  protected $_declaredProperties = array(
    'identifier' => array('_identifier', '_identifier'),
    'elements' => array('elements', 'elements')
  );

  /**
  * Append menu and elements and set identifier if available
  *
  * @param \Papaya\Xml\Element $parent
  * @return \Papaya\Xml\Element|NULL
  */
  public function appendTo(\Papaya\Xml\Element $parent) {
    if (count($this->elements()) > 0) {
      $menu = $parent->appendElement('menu');
      if (!empty($this->_identifier)) {
        $menu->setAttribute('ident', (string)$this->_identifier);
      }
      $this->elements()->appendTo($menu);
      return $menu;
    }
    return NULL;
  }

}
