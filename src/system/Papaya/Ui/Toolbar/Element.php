<?php
/**
* Superclass for menu elements. All menu elements must be children of this class
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
* @version $Id: Element.php 36477 2011-12-03 13:25:26Z weinert $
*/

/**
* Superclass for menu elements. All menu elements must be children of this class.
*
* @package Papaya-Library
* @subpackage Ui
*/
abstract class PapayaUiToolbarElement extends PapayaUiControlCollectionItem {

  /**
  * reference (link) object
  *
  * @var PapayaUiReference
  */
  protected $_reference = NULL;

  /**
  * Getter/Setter for the reference object (the link url)
  *
  * @param PapayaUiReference $reference
  * @return PapayaUiReference
  */
  public function reference(PapayaUiReference $reference = NULL) {
    if (isset($reference)) {
      $this->_reference = $reference;
    }
    if (is_null($this->_reference)) {
      $this->_reference = new PapayaUiReference();
      $this->_reference->papaya($this->papaya());
    }
    return $this->_reference;
  }
}