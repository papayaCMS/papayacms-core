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
* A field that output a message inside the dialog
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogFieldInformation extends \PapayaUiDialogField {

  /**
  * Information text
  *
  * @var string|PapayaUiString
  */
  protected $_text = '';

  /**
  * Message image
  *
  * @var string
  */
  protected $_image = '';

  /**
  * Create object and assign needed values
  *
  * @param string|\PapayaUiString $text
  * @param string $image
  */
  public function __construct($text, $image = NULL) {
    $this->_text = $text;
    $this->_image = $image;
  }

  /**
  * Append message field to dialog xml dom
  *
  * @param \PapayaXmlElement $parent
  */
  public function appendTo(\PapayaXmlElement $parent) {
    $field = $this->_appendFieldTo($parent);
    $message = $field->appendElement(
      'message', array(), (string)$this->_text
    );
    $image = empty($this->_image) ? '' : $this->papaya()->images[$this->_image];
    if (!empty($image)) {
      $message->setAttribute('image', $image);
    }
  }
}
