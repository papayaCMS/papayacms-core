<?php
/**
* An navigation item with a name attribute. Used for specific navigation items, that need
* individual handling in the template.
*
* @copyright 2011 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Ui
* @version $Id: Named.php 36714 2012-02-08 11:38:01Z weinert $
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
class PapayaUiNavigationItemNamed extends PapayaUiNavigationItem {

  /**
  * Use the parent method to create and append to xml element not. Set an attribute name
  * for the create xml element using the member variable.
  *
  * @see papaya-lib/system/Papaya/Ui/Navigation/PapayaUiNavigationItem#appendTo($parent)
  */
  public function appendTo(PapayaXmlElement $parent) {
    $result = parent::appendTo($parent);
    $result->setAttribute(
      'name', PapayaUtilStringIdentifier::toUnderscoreLower((string)$this->_sourceValue)
    );
    return $result;
  }
}