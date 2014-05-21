<?php
/**
* A field that outputs xhtml inside the dialog.
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
* @version $Id: Xhtml.php 39408 2014-02-27 16:00:49Z weinert $
*/

/**
* A field that outputs xhtml inside the dialog.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogFieldXhtml extends PapayaUiDialogField {

  /**
  * Xhtml content
  *
  * @var string
  */
  private $_content = NULL;

  private $_dom = NULL;

  /**
  * Create object and assign needed values.
  *
  * @param string|PapayaUiString|PapayaXmlElement $content
  */
  public function __construct($content = NULL) {
    if (isset($content)) {
      $this->content($content);
    }
  }

  /**
   * Getter/Setter for xhtml content.
   *
   * @param string|PapayaUiString|PapayaXmlElement $content
   * @throws InvalidArgumentException
   * @return PapayaXmlElement
   */
  public function content($content = NULL) {
    if (isset($content)) {
      if ($content instanceof PapayaXmlElement) {
        $this->_content = $content;
      } elseif (is_string($content) || $content instanceof PapayaUiString) {
        $this->content()->appendXml((string)$content);
      } else {
        throw new InvalidArgumentException('Content must be string or valid xml element object');
      }
    } elseif (is_null($this->_content)) {
      $this->_dom = new PapayaXmlDocument();
      $this->_content = $this->_dom->appendElement('xhtml');
    }
    return $this->_content;
  }

  /**
  * Append xhtml field to dialog xml dom.
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $field = $this->_appendFieldTo($parent);
    if ($this->content()->hasChildNodes()) {
      $field->appendChild(
        $field->ownerDocument->importNode($this->content(), TRUE)
      );
    }
  }
}