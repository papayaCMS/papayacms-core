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

namespace Papaya\Ui\Dialog;
/**
 * Superclass for dialog fields
 *
 * A field can not only be a simple input, but a group of inputs that contains other fields.
 *
 * In Addition to the collect() method, wich collects the user inputs, fields have a validate()
 * method which is executed before.
 *
 * @package Papaya-Library
 * @subpackage Ui
 */
abstract class Field extends Element {

  /**
   * Field caption
   *
   * @var string|\Papaya\Ui\Text
   */
  private $_caption = '';

  /**
   * Field Hint
   *
   * @var string|\Papaya\Ui\Text
   */
  private $_hint = '';

  /**
   * Field description
   *
   * @var NULL|\Papaya\Ui\Dialog\Element\Description
   */
  private $_description = NULL;

  /**
   * Field name
   *
   * @var string
   */
  private $_name = '';

  /**
   * Field identifier
   *
   * @var string
   */
  private $_id = '';

  /**
   * field default value
   *
   * @var mixed
   */
  private $_defaultValue = NULL;

  /**
   * field disabled status
   *
   * @var boolean
   */
  private $_disabled = FALSE;

  /**
   * field mandatory status
   *
   * @var boolean
   */
  private $_mandatory = FALSE;

  /**
   * Filter used to check/filter the input
   *
   * @var \Papaya\Filter
   */
  private $_filter = NULL;

  /**
   * Cached validation result
   *
   * @var NULL|Boolean
   */
  protected $_validationResult = NULL;

  /**
   * Validation execption
   *
   * @var NULL|\Papaya\Filter\Exception
   */
  protected $_exception = NULL;

  /**
   * Set caption for this field, this can be a label or a title or something different depending
   * on the field implementation
   *
   * The caption value itself can be an string or a \Papaya\Ui\PapayaUiString object. The getter will
   * cast it to a string.
   *
   * @param string|\Papaya\Ui\Text $caption
   * @throws \UnexpectedValueException
   */
  public function setCaption($caption) {
    if (is_string($caption) || $caption instanceof \Papaya\Ui\Text) {
      $this->_caption = $caption;
    } else {
      throw new \UnexpectedValueException(
        sprintf(
          'Unexpected value type: Expected "string" or "Papaya\Ui\PapayaUiString" but "%s" given.',
          is_object($caption) ? get_class($caption) : gettype($caption)
        )
      );
    }
  }

  /**
   * Casts the caption value to a string and returns it.
   *
   * @return string
   */
  public function getCaption() {
    return (string)$this->_caption;
  }

  /**
   * A hint/short description for the field. This is shown to the user to help him input the
   * correct value.
   *
   * The hint value can be an string or a \Papaya\Ui\PapayaUiString object. The getter will
   * cast it to a string.
   *
   * @param string|\Papaya\Ui\Text $hint
   * @throws \UnexpectedValueException
   */
  public function setHint($hint) {
    if (is_string($hint) || $hint instanceof \Papaya\Ui\Text) {
      $this->_hint = $hint;
    } else {
      throw new \UnexpectedValueException(
        sprintf(
          'Unexpected value type: Expected "string" or "Papaya\Ui\PapayaUiString" but "%s" given.',
          is_object($hint) ? get_class($hint) : gettype($hint)
        )
      );
    }
  }

  /**
   * Casts the hint value to a string and returns it.
   *
   * @return string
   */
  public function getHint() {
    return (string)$this->_hint;
  }

  /**
   * Sets an id for the field.
   *
   * @param string $id
   */
  public function setId($id) {
    \Papaya\Utility\Constraints::assertString($id);
    \Papaya\Utility\Constraints::assertNotEmpty($id);
    $this->_id = $id;
  }

  /**
   * Return the id set for the field
   *
   * @return string
   */
  public function getId() {
    return $this->_id;
  }

  /**
   * Sets an name for the field.
   *
   * This will be uses for the parameter name and defines which value is read from the dialog
   * parameters and which value are set into the dialog data.
   *
   * A field without a name can not set a dialog data value. At lease not with the default
   * implementation of collect().
   *
   * @param string $name
   */
  public function setName($name) {
    \Papaya\Utility\Constraints::assertString($name);
    \Papaya\Utility\Constraints::assertNotEmpty($name);
    $this->_name = $name;
  }

  /**
   * Get the field name
   *
   * @return string
   */
  public function getName() {
    return $this->_name;
  }

  /**
   * Sets a default value for the field.
   *
   * If a parameter/data value for the field is provided, the default value is ignored.
   *
   * But if no filter object was provided, the current value is casted to the same variable type
   * like the default value.
   *
   * @param mixed $defaultValue
   */
  public function setDefaultValue($defaultValue) {
    $this->_defaultValue = $defaultValue;
  }

  /**
   * Get the default value for the field.
   *
   * @return mixed
   */
  public function getDefaultValue() {
    return $this->_defaultValue;
  }

  /**
   * Set a disabled status for the field, how this is handled depends on the field.
   *
   * In the default implementation, disabled field will ignore request parameter values
   * (only read the default value and data) and output a status field.
   *
   * @param boolean $disabled
   */
  public function setDisabled($disabled) {
    $this->_disabled = (bool)$disabled;
  }

  /**
   * Get a disabled status for the field
   *
   * @return bool
   */
  public function getDisabled() {
    return $this->_disabled;
  }

  /**
   * Set a mandatory status for the field, this will be used in to define the actual filter object
   * returned by getFilter and the xml output.
   *
   * @param boolean $mandatory
   */
  public function setMandatory($mandatory) {
    $this->_mandatory = (bool)$mandatory;
  }

  /**
   * Get a mandatory status for the field
   *
   * @return bool
   */
  public function getMandatory() {
    return $this->_mandatory;
  }

  /**
   * Sets a filter object for the field.
   *
   * Filter objects are used to check and filter user inputs
   *
   * @param \Papaya\Filter $filter
   */
  public function setFilter(\Papaya\Filter $filter) {
    $this->_filter = $filter;
  }

  /**
   * Gets a filter object for the field. If the field is not mandatory the filter will be prefixed
   * with \Papaya\Filter\PapayaFilterEmpty
   *
   * Filter objects are used to check and filter user inputs
   *
   * @return NULL|\Papaya\Filter
   */
  public function getFilter() {
    if ($this->_mandatory && isset($this->_filter)) {
      return $this->_filter;
    } elseif (isset($this->_filter)) {
      return new \Papaya\Filter\LogicalOr($this->_filter, new \Papaya\Filter\EmptyValue());
    } else {
      return NULL;
    }
  }

  /**
   * Getter/Setter for the description subobject.
   *
   * @param \Papaya\Ui\Dialog\Element\Description $description
   * @return \Papaya\Ui\Dialog\Element\Description
   */
  public function description(\Papaya\Ui\Dialog\Element\Description $description = NULL) {
    if (isset($description)) {
      $this->_description = $description;
    } elseif (is_null($this->_description)) {
      $this->_description = new \Papaya\Ui\Dialog\Element\Description();
      $this->_description->papaya($this->papaya());
    }
    return $this->_description;
  }

  /**
   * Validate dialog input for this field
   *
   * @return boolean
   */
  public function validate() {
    if (isset($this->_validationResult)) {
      return $this->_validationResult;
    }
    return $this->_validationResult = $this->_validateFilter($this->getFilter());
  }

  /**
   * Validate current value against the filter object if it is here.
   *
   * @param \Papaya\Filter|NULL $filter
   * @return boolean
   */
  protected function _validateFilter($filter) {
    if (isset($filter) && $filter instanceof \Papaya\Filter) {
      try {
        return $filter->validate($this->getCurrentValue());
      } catch (\Papaya\Filter\Exception $e) {
        $this->handleValidationFailure($e);
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Handle a field validation failure.
   *
   * This will set the exception property and call a handle function on the dialog object.
   *
   * @param \Exception $e
   */
  public function handleValidationFailure(\Exception $e) {
    $this->_validationResult = FALSE;
    $this->_exception = $e;
    if ($this->hasCollection() &&
      $this->collection()->hasOwner()) {
      $this->collection()->owner()->handleValidationFailure($e, $this);
    }
  }

  /**
   * Collect input values in the data property of the dialog object.
   *
   * Puts the current value into the dialog data object using the field name property.
   *
   * If a filter object was provided it will be used to filter the current value.
   *
   * If a default value was provided (but no filter object) the current value will be casted to the
   * variable type of the default value.
   *
   * If neither filter object or default value was provided the original current value will be used.
   *
   * @return boolean;
   */
  public function collect() {
    $name = $this->getName();
    if (parent::collect() && !empty($name)) {
      if ($filter = $this->getFilter()) {
        $value = $filter->filter($this->getCurrentValue());
        $this->collection()->owner()->data()->set(
          $name, is_null($value) ? $this->getDefaultValue() : $value
        );
      } elseif (NULL !== $this->getDefaultValue()) {
        $value = $this->getCurrentValue();
        if (is_object($this->getDefaultValue())) {
          $value = (string)$value;
        } else {
          settype($value, gettype($this->getDefaultValue()));
        }
        $this->collection()->owner()->data()->set($name, $value);
      } else {
        $this->collection()->owner()->data()->set(
          $name, $this->getCurrentValue()
        );
      }
    }
    return TRUE;
  }

  /**
   * Get the current field value.
   *
   * If the dialog object has a matching paremeter it is used. Otherwise the data object of the
   * dialog is checked and used.
   *
   * If neither dialog parameter or data is available, the default value is returned.
   *
   * @return mixed
   */
  public function getCurrentValue() {
    $name = $this->getName();
    if (!empty($name) && ($dialog = $this->getDialog())) {
      if (!$this->getDisabled() && $dialog->parameters()->has($name)) {
        return $dialog->parameters()->get($name);
      } elseif ($dialog->data()->has($name)) {
        if (NULL !== ($value = $dialog->data()->get($name))) {
          return $value;
        }
      }
    }
    return $this->getDefaultValue();
  }

  /**
   * Append field outer elements to DOM
   *
   * @param \Papaya\Xml\Element $parent
   * @return \Papaya\Xml\Element
   */
  protected function _appendFieldTo(\Papaya\Xml\Element $parent) {
    if ($this->hasCollection() &&
      $this->collection()->hasOwner() &&
      !$this->collection()->owner()->isSubmitted()) {
      $isValid = TRUE;
    } else {
      $isValid = $this->validate();
    }
    $field = $parent->appendElement(
      'field',
      array(
        'caption' => $this->getCaption(),
        'class' => $this->_getFieldClass(),
        'error' => $isValid ? 'no' : 'yes',
        'hint' => $this->getHint(),
        'id' => $this->getId(),
        'disabled' => $this->getDisabled() ? 'yes' : '',
        'mandatory' => $this->getMandatory() ? 'yes' : ''
      )
    );
    $this->description()->appendTo($field);
    return $field;
  }

  /**
   * Return field class name without stripped prefix.
   *
   * @param string $prefix
   * @return string
   */
  protected function _getFieldClass($prefix = 'PapayaUi') {
    $class = str_replace('\\', '', get_class($this));
    if (0 === strpos($class, $prefix)) {
      $class = substr($class, strlen($prefix));
    }
    return $class;
  }
}
