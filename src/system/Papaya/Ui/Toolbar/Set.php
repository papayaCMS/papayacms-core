<?php
/**
* A menu element set. This is a sublist of menu elements like buttons.
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
* @version $Id: Set.php 39403 2014-02-27 14:25:16Z weinert $
*/

/**
* A menu element set. This is a sublist of menu elements like buttons.
*
* This allows to append the toolbar elements to a specific part of a toolbar (defined by the set)
*
* @package Papaya-Library
* @subpackage Ui
*
* @property PapayaUiToolbarElements $elements
*/
class PapayaUiToolbarSet
  extends PapayaUiToolbarElement {

  /**
  * Group elements collection
  *
  * @var NULL|PapayaUiToolbarElements
  */
  protected $_elements = NULL;

  /**
  * Declare properties
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
      $this->_elements->allowGroups = FALSE;
    }
    return $this->_elements;
  }

  /**
  * Append group and elements to the output xml.
  *
  * @param PapayaXmlElement $parent
  * @return NULL
   */
  public function appendTo(PapayaXmlElement $parent) {
    $this->elements()->appendTo($parent);
    return NULL;
  }
}