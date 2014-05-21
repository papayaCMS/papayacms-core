<?php
/**
* A list of dialog fields
*
* @copyright 2010 by papaya Software GmbH - All rights reserved.
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
* @version $Id: Fields.php 39429 2014-02-27 20:14:26Z weinert $
*/

/**
* A list of dialog fields
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogFields extends PapayaUiDialogElements {

  /**
  * Only PapayaUiDialogField objects are allows in this list
  * @var string
  */
  protected $_itemClass = 'PapayaUiDialogField';

  /**
  * Validate all dialog fields (check user inputs)
  *
  * If one of the fields returns FALSE, this will be the return value of the method. But still
  * all other fields will be checked.
  *
  * @return boolean
  */
  public function validate() {
    $result = TRUE;
    /** @var PapayaUiDialogField $field */
    foreach ($this->_items as $field) {
      if (!$field->validate()) {
        $result = FALSE;
      }
    }
    return $result;
  }
}