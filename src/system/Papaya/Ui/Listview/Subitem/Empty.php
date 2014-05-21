<?php
/**
* An empty listview subitem.
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
* @version $Id: Empty.php 38958 2013-11-22 12:45:31Z weinert $
*/

/**
* An empty listview subitem.
*
* Empty subitems are needed to avoid
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiListviewSubitemEmpty extends PapayaUiListviewSubitem {

  /**
  * Append subitem xml data to parent node. In this case just an <subitem/> element
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $parent->appendElement('subitem');
  }
}