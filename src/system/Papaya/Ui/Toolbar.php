<?php
/**
* A toolbar gui control. This is a list of elements like buttons, separators and selects.
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
* @version $Id: Toolbar.php 38905 2013-11-04 13:40:19Z weinert $
*/

/**
* A toolbar gui control. This is a list of elements like buttons, separators and selects.
*
* @package Papaya-Library
* @subpackage Ui
*
* @property PapayaUiToolbarElements $elements
*/
class PapayaUiToolbar extends PapayaUiControl {

  /**
  * menu elements collection
  *
  * @var NULL|PapayaUiToolbarElements
  */
  protected $_elements = NULL;

  /**
  * Delcare public properties
  *
  * @var array
  */
  protected $_declaredProperties = array(
    'elements' => array('elements', 'elements')
  );

  /**
   * Getter/setter for elements collection
   *
   * @param PapayaUiToolbarElements $elements
   * @return \PapayaUiToolbarElements
   */
  public function elements(PapayaUiToolbarElements $elements = NULL) {
    if (isset($elements)) {
      $this->_elements = $elements;
      $this->_elements->owner($this);
    }
    if (is_null($this->_elements)) {
      $this->_elements = new PapayaUiToolbarElements($this);
    }
    return $this->_elements;
  }

  /**
  * Append toolbar and elements and set identifier if available
  *
  * @see papaya-lib/system/Papaya/Ui/Control/PapayaUiControlCollection#appendTo($parent)
  *
  * @param PapayaXmlElement $parent
  * @return PapayaXmlElement|NULL
  */
  public function appendTo(PapayaXmlElement $parent) {
    if (count($this->elements()) > 0) {
      $toolbar = $parent->appendElement('toolbar');
      $this->elements()->appendTo($toolbar);
      return $toolbar;
    }
    return NULL;
  }

}