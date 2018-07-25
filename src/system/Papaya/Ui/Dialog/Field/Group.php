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
*
* @property string|PapayaUiString $caption
* @property PapayaUiDialogFields $fields
*/
class PapayaUiDialogFieldGroup extends \PapayaUiDialogField {

  /**
  * Grouped input fields
  * @var \PapayaUiDialogFields
  */
  protected $_fields = NULL;

  /**
  * declare dynamic properties
  *
  * @var array
  */
  protected $_declaredProperties = array(
    'caption' => array('getCaption', 'setCaption'),
    'fields' => array('fields', 'fields')
  );

  /**
  * Initialize object, set caption, field name and maximum length
  *
  * @param string|\PapayaUiString $caption
  */
  public function __construct($caption) {
    $this->setCaption($caption);
  }

  /**
   * Group fields getter/setter
   *
   * @param \PapayaUiDialogFields $fields
   * @return \PapayaUiDialogFields
   */
  public function fields(\PapayaUiDialogFields $fields = NULL) {
    if (isset($fields)) {
      $this->_fields = $fields;
      if ($this->hasCollection() && $this->collection()->hasOwner()) {
        $fields->owner($this->collection()->owner());
      }
    }
    if (is_null($this->_fields)) {
      $this->_fields = new \PapayaUiDialogFields(
        $this->hasDialog() ? $this->getDialog() : NULL
      );
    }
    return $this->_fields;
  }

  /**
  * Validate field group
  *
  * @return boolean
  */
  public function validate() {
    if (isset($this->_validationResult)) {
      return $this->_validationResult;
    }
    if (isset($this->_fields)) {
      $this->_validationResult = $this->_fields->validate();
    } else {
      $this->_validationResult = TRUE;
    }
    return  $this->_validationResult;
  }

  /**
   * Collect field group data
   *
   * @return boolean
   */
  public function collect() {
    if (parent::collect() &&
        isset($this->_fields)) {
      $this->_fields->collect();
      return TRUE;
    }
    return FALSE;
  }

  /**
  * Append group and fields in this group to the DOM.
  *
  * @param \PapayaXmlElement $parent
  */
  public function appendTo(\PapayaXmlElement $parent) {
    if (isset($this->_fields) && count($this->_fields) > 0) {
      $group = $parent->appendElement(
        'field-group',
        array(
          'caption' => $this->getCaption()
        )
      );
      $id = $this->getId();
      if (!empty($id)) {
        $group->setAttribute('id', $id);
      }
      $this->_fields->appendTo($group);
    }
  }

  /**
  * Return the owner collection of the item.
  *
  * @param \PapayaUiControlCollection $collection
  * @return \PapayaUiControlCollection
  */
  public function collection(\PapayaUiControlCollection $collection = NULL) {
    $result = parent::collection($collection);
    if ($collection != NULL && $collection->hasOwner()) {
      $this->fields()->owner($collection->owner());
    }
    return $result;
  }
}
