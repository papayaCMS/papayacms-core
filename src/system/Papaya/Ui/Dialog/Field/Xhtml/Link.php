<?php
/**
* A field that outputs a link inside the dialog.
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
* @version $Id: Link.php 36305 2011-10-12 10:10:53Z roman $
*/

/**
* A field that outputs a link inside the dialog.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogFieldXhtmlLink extends PapayaUiDialogField {

  /**
  * Link url
  *
  * @var string
  */
  protected $_url = '';

  /**
  * Link caption
  *
  * @var string
  */
  protected $_urlCaption = '';

  /**
  * Create object and assign needed values.
  *
  * @param string|PapayaUiString $url
  * @param string|PapayaUiString $caption
  */
  public function __construct($url, $caption = NULL) {
    $this->_url = $url;
    if (!empty($caption)) {
      $this->_urlCaption = $caption;
    }
  }

  /**
  * Append xhtml field to dialog xml dom.
  *
  * @param PapayaXmlElement $parent
  */
  public function appendTo(PapayaXmlElement $parent) {
    $field = $this->_appendFieldTo($parent);
    $field
      ->appendElement('xhtml')
      ->appendElement('a', array('href' => $this->_url), (string)$this->_urlCaption);
  }
}