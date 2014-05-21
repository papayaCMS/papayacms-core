<?php
/**
* Dialog element description item encapsulationing a simple link.
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
* @version $Id: Property.php 39406 2014-02-27 15:07:55Z weinert $
*/

/**
* Dialog element description item encapsulationing a simple link.
*
* @property string $name
* @property string $value
 *
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogElementDescriptionProperty extends PapayaUiDialogElementDescriptionItem {

  protected $_name = '';
  protected $_value = '';

  protected $_declaredProperties = array(
    'name' => array('_name', 'setName'),
    'value' => array('_value', '_value')
  );

  /**
  * Create object, and store name and value data
  *
  * @param string $name
  * @param string $value
  */
  public function __construct($name, $value) {
    $this->setName($name);
    $this->_value = $value;
  }

  /**
  * Name can not be empty - not a very strong validation, but should be enough for the most cases.
  *
  * @param string $name
  */
  public function setName($name) {
    PapayaUtilConstraints::assertNotEmpty($name);
    $this->_name = $name;
  }

  /**
  * Append description element with href attribute to parent xml element.
  *
  * @param PapayaXmlElement $parent
  * @return PapayaXmlElement
  */
  public function appendTo(PapayaXmlElement $parent) {
    return $parent->appendElement(
      'property',
      array(
        'name' => (string)$this->_name,
        'value' => (string)$this->_value
      )
    );
  }
}