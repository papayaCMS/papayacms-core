<?php
/**
* A single subtitle element for a sheet
*
* @copyright 2014 by papaya Software GmbH - All rights reserved.
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
* @version $Id: Subtitle.php 39820 2014-05-13 15:48:35Z weinert $
*/

/**
* A single subtitle element for a sheet
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiSheetSubtitle extends PapayaUiControlCollectionItem {

  private $_text = '';

  /**
   * @param $text
   */
  public function __construct($text) {
    $this->_text = $text;
  }

  public function appendTo(PapayaXmlElement $parent) {
    return $parent->appendElement('subtitle', array(), (string)$this->_text);
  }
}