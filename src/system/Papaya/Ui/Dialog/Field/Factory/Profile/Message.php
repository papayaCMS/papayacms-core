<?php
/**
* Field factory profiles for an information, confirmation, warning or error
*
* @copyright 2012 by papaya Software GmbH - All rights reserved.
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
* @version $Id: Message.php 37444 2012-08-21 08:54:45Z weinert $
*/

/**
* Field factory profile for an information, confirmation, warning or error
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogFieldFactoryProfileMessage extends PapayaUiDialogFieldFactoryProfile {

  /**
   * @see PapayaUiDialogFieldFactoryProfile::getField()
   * @return PapayaUiDialogFieldInput
   */
  public function getField() {
    $field = new PapayaUiDialogFieldMessage(
      (int)$this->options()->parameters,
      $this->options()->default
    );
    return $field;
  }
}