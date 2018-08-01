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
* An navigation item with a name attribute. Used for specific navigation items, that need
* individual handling in the template.
*
* The given name is converted to an identifer using lowercase letters and underscore separators.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiNavigationItemNamed extends \PapayaUiNavigationItem {

  /**
  * Use the parent method to create and append to xml element not. Set an attribute name
  * for the create xml element using the member variable.
  *
  * @see papaya-lib/system/Papaya/Ui/Navigation/PapayaUiNavigationItem#appendTo($parent)
  */
  public function appendTo(\Papaya\Xml\Element $parent) {
    $result = parent::appendTo($parent);
    $result->setAttribute(
      'name', \Papaya\Utility\Text\Identifier::toUnderscoreLower((string)$this->_sourceValue)
    );
    return $result;
  }
}
