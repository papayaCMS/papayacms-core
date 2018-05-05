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
* A simple single line input field with a caption.
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogFieldInput extends \PapayaUiDialogField {

  /**
  * Field maximum input length
  * @var integer
  */
  protected $_maximumLength = 0;

  /**
  * An input field is always an single line text input field.
  *
  * However here are variants and not all of them require special php logic. The
  * type is included in the xml so the xslt template can access it and add special handling like
  * css classes for defensive javascript.
  *
  * @var string
  */
  protected $_type = 'text';

  /**
  * Initialize object, set caption, field name and maximum length
  *
  * @param string|\PapayaUiString $caption
  * @param string $name
  * @param integer $length
  * @param mixed $default
  * @param \PapayaFilter|NULL $filter
  */
  public function __construct(
    $caption,
    $name,
    $length = 1024,
    $default = NULL,
    \PapayaFilter $filter = NULL
  ) {
    $this->setCaption($caption);
    $this->setName($name);
    $this->setMaximumLength($length);
    $this->setDefaultValue($default);
    if (isset($filter)) {
      $this->setFilter($filter);
    }
  }

  /**
  * Set the maximum field length of this element.
  *
  * @param integer $maximumLength
  * @return \PapayaUiDialogFieldInput
  */
  public function setMaximumLength($maximumLength) {
    \PapayaUtilConstraints::assertInteger($maximumLength);
    if ($maximumLength > 0) {
      $this->_maximumLength = $maximumLength;
    } else {
      $this->_maximumLength = -1;
    }
  }

  /**
  * Set the type of this input field.
  *
  * An input field is always an single line text input field. However here are variants and
  * not all of them require special php logic. The type is included in the xml so the xslt template
  * can access it and add special handling like css classes for defensive javascript.
  *
  * The method can uses by descendant classes, too.
  */
  public function setType($type) {
    \PapayaUtilConstraints::assertString($type);
    \PapayaUtilConstraints::assertNotEmpty($type);
    $this->_type = $type;
  }

  /**
  * Read the type of this input field.
  *
  * @return string
  */
  public function getType() {
    return $this->_type;
  }

  /**
  * Append field and input ouptut to DOM
  *
  * @param \PapayaXmlElement $parent
  */
  public function appendTo(\PapayaXmlElement $parent) {
    $field = $this->_appendFieldTo($parent);
    $field->appendElement(
      'input',
      array(
        'type' => $this->getType(),
        'name' => $this->_getParameterName($this->getName()),
        'maxlength' => $this->_maximumLength
      ),
      $this->getCurrentValue()
    );
  }
}
