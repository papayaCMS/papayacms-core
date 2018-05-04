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
* A select field (dropdown) based on a list of values, one value can be selected
*
* @package Papaya-Library
* @subpackage Ui
*/
class PapayaUiDialogFieldSelect extends PapayaUiDialogField {

  const VALUE_USE_KEY = 0;
  const VALUE_USE_CAPTION = 1;

  private $_valueMode = self::VALUE_USE_KEY;

  /**
  * field name
  * @var string
  */
  protected $_name = '';

  /**
  * option values
  * @var array
  */
  protected $_values = array();

  /**
  * type of the select control, used in the xslt template
  *
  * @var string
  */
  protected $_type = 'dropdown';

  /**
  * callbacks
  * @var PapayaObjectCallbacks
  */
  protected $_callbacks = NULL;

  /**
   * Initialize object ans set caption, name and value list.
   *
   *
   * @param string|PapayaUiString $caption
   * @param string $name
   * @param array|Traversable $values
   * @param bool $mandatory
   * @param int $mode
   */
  public function __construct(
    $caption, $name, $values, $mandatory = TRUE, $mode = self::VALUE_USE_KEY
  ) {
    $this->setCaption($caption);
    $this->setName($name);
    $this->setValueMode($mode);
    $this->setValues($values);
    $this->setMandatory($mandatory);
  }

  /**
   * Set the value mode, allow to use keys or captions, this will reset the filter, too.
   *
   * @param integer $mode
   */
  public function setValueMode($mode) {
    PapayaUtilConstraints::assertInteger($mode);
    $this->_valueMode = $mode;
    $this->setFilter($this->_createFilter());
  }

  /**
   * Return the current value mode
   * @return integer
   */
  public function getValueMode() {
    return $this->_valueMode;
  }

  /**
  * Select option values setter.
  *
  * array('value' => 'label', ...)
  *
  * @param array|Traversable $values
  */
  public function setValues($values) {
    PapayaUtilConstraints::assertArrayOrTraversable($values);
    $this->_values = $values;
    $this->setFilter($this->_createFilter());
  }

  /**
   * Return the value array|iterator
   */
  public function getValues() {
    return $this->_values;
  }

  /**
  * If the values are set, it is nessessary to create a filter based on the values.
  */
  protected function _createFilter() {
    $values = $this->getValues();
    if ($values instanceof \RecursiveIterator) {
      $values = new \RecursiveIteratorIterator($values);
    }
    if ($this->getValueMode() == self::VALUE_USE_KEY) {
      return new \PapayaFilterListKeys($values);
    } else {
      return new \PapayaFilterList($values);
    }
  }

  /**
   * Append select field to DOM
   *
   * @param PapayaXmlElement $parent
   * @return \PapayaXmlElement
   */
  public function appendTo(PapayaXmlElement $parent) {
    $this->_appendOptions(
      $this->_appendSelect(
        $this->_appendFieldTo($parent)
      ),
      $this->getValues()
    );
    return $parent;
  }

  /**
  * Append the select element itself to the DOM (the field element is the parent)
  *
  * @param PapayaXmlElement $parent
  * @return PapayaXmlElement
  */
  protected function _appendSelect(PapayaXmlElement $parent) {
    return $parent->appendElement(
      'select',
      array(
        'name' => $this->_getParameterName($this->getName()),
        'type' => $this->_type,
      )
    );
  }

  /**
   * Append select field option elements to DOM
   *
   * @param PapayaXmlElement $parent
   * @param RecursiveIterator|Traversable|array $options
   * @return PapayaXmlElement
   */
  protected function _appendOptions(PapayaXmlElement $parent, $options) {
    PapayaUtilConstraints::assertArrayOrTraversable($options);
    $isRecursiveIterator = ($options instanceof \RecursiveIterator);
    foreach ($options as $index => $option) {
      if ($isRecursiveIterator && $options->hasChildren()) {
        $group = $this->_appendOptionGroup($parent, $option, $index);
        $this->_appendOptions($group, $options->getChildren());
      } else {
        $this->_appendOption($parent, $option, $index);
      }
    }
  }

  /**
   * Append an option group element to the DOM
   *
   * @param PapayaXmlElement $parent
   * @param mixed $option
   * @param mixed $index
   * @return PapayaXmlElement
   */
  protected function _appendOptionGroup(PapayaXmlElement $parent, $option, $index) {
    $caption = $this->callbacks()->getOptionGroupCaption($option, $index);
    $caption = empty($caption) ? (string)$option : $caption;
    return $parent->appendElement(
      'group',
      array('caption' => $caption)
    );
  }

  /**
  * Append one option element to DOM. This calls callbacks to get the option caption
  * and data attributes.
  *
  * @param PapayaXmlElement $parent
  * @param mixed $option
  * @param mixed $index
  * @return PapayaXmlElement
  */
  protected function _appendOption(PapayaXmlElement $parent, $option, $index) {
    $caption = $this->callbacks()->getOptionCaption($option, $index);
    $caption = empty($caption) ? (string)$option : $caption;
    $value = ($this->getValueMode() == self::VALUE_USE_KEY) ? $index : $caption;
    $node = $parent->appendElement(
      'option',
      array(
        'value' => (string)$value
      ),
      $caption
    );
    if ($this->_isOptionSelected($this->getCurrentValue(), $value)) {
      $node->setAttribute('selected', 'selected');
    }
    $data = $this->callbacks()->getOptionData($option, $index);
    if (!empty($data)) {
      foreach ($data as $name => $attrValue) {
        $node->setAttribute(
          'data-'.$name, is_scalar($attrValue) ? $attrValue : json_encode($attrValue)
        );
      }
    }
    return $node;
  }

  /**
   * Determine if the option is selected using the current value and the option value.
   *
   * @param mixed $currentValue
   * @param string $optionValue
   * @return bool
   */
  protected function _isOptionSelected($currentValue, $optionValue) {
    return $currentValue == $optionValue;
  }

  /**
  * Getter/Setter for the callbacks, if you set your own callback object, make sure it has the
  * needed definitions.
  *
  * @param PapayaUiDialogFieldSelectCallbacks $callbacks
  * @return PapayaUiDialogFieldSelectCallbacks
  */
  public function callbacks(PapayaUiDialogFieldSelectCallbacks $callbacks = NULL) {
    if (isset($callbacks)) {
      $this->_callbacks = $callbacks;
    } elseif (is_null($this->_callbacks)) {
      $this->_callbacks = new \PapayaUiDialogFieldSelectCallbacks();
    }
    return $this->_callbacks;
  }
}
