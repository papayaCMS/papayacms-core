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
namespace Papaya\UI\Dialog;

use Papaya\Filter;
use Papaya\UI;
use Papaya\Utility;
use Papaya\XML;

/**
 * Superclass for dialog fields
 *
 * A field can not only be a simple input, but a group of inputs that contains other fields.
 *
 * In Addition to the collect() method, which collects the user inputs, fields have a validate()
 * method which is executed before.
 *
 * @package Papaya-Library
 * @subpackage UI
 */
abstract class Field extends Element {
  /**
   * Field caption
   *
   * @var string|UI\Text
   */
  private $_caption = '';

  /**
   * Field Hint
   *
   * @var string|UI\Text
   */
  private $_hint = '';

  /**
   * Field description
   *
   * @var null|Element\Description
   */
  private $_description;

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
  private $_defaultValue;

  /**
   * field disabled status
   *
   * @var bool
   */
  private $_disabled = FALSE;

  /**
   * field mandatory status
   *
   * @var bool
   */
  private $_mandatory = FALSE;

  /**
   * Filter used to check/filter the input
   *
   * @var Filter
   */
  private $_filter;

  /**
   * Cached validation result
   *
   * @var null|bool
   */
  protected $_validationResult;

  /**
   * Validation execption
   *
   * @var null|Filter\Exception
   */
  protected $_exception;

  /**
   * Set caption for this field, this can be a label or a title or something different depending
   * on the field implementation
   *
   * The caption value itself can be an string or a \Papaya\UI\Text object. The getter will
   * cast it to a string.
   *
   * @param string|UI\Text $caption
   *
   * @throws \UnexpectedValueException
   */
  public function setCaption($caption) {
    Utility\Constraints::assertStringCastable($caption);
    $this->_caption = $caption;
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
   * The hint value can be an string or a \Papaya\UI\Text object. The getter will
   * cast it to a string.
   *
   * @param string|UI\Text $hint
   *
   * @throws \UnexpectedValueException
   */
  public function setHint($hint) {
    Utility\Constraints::assertStringCastable($hint);
    $this->_hint = $hint;
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
    Utility\Constraints::assertString($id);
    Utility\Constraints::assertNotEmpty($id);
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
    Utility\Constraints::assertString($name);
    Utility\Constraints::assertNotEmpty($name);
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
   * @param bool $disabled
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
   * @param bool $mandatory
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
   * @param Filter $filter
   */
  public function setFilter(Filter $filter) {
    $this->_filter = $filter;
  }

  /**
   * Gets a filter object for the field. If the field is not mandatory the filter will be prefixed
   * with \Papaya\Filter\EmptyValue
   *
   * Filter objects are used to check and filter user inputs
   *
   * @return null|Filter
   */
  public function getFilter() {
    if ($this->_mandatory && NULL !== $this->_filter) {
      return $this->_filter;
    }
    if (NULL !== $this->_filter) {
      return new Filter\LogicalOr($this->_filter, new Filter\EmptyValue());
    }
    return NULL;
  }

  /**
   * Getter/Setter for the description subobject.
   *
   * @param Element\Description $description
   *
   * @return Element\Description
   */
  public function description(Element\Description $description = NULL) {
    if (NULL !== $description) {
      $this->_description = $description;
    } elseif (NULL === $this->_description) {
      $this->_description = new Element\Description();
      $this->_description->papaya($this->papaya());
    }
    return $this->_description;
  }

  /**
   * Validate dialog input for this field
   *
   * @return bool
   */
  public function validate() {
    if (NULL !== $this->_validationResult) {
      return $this->_validationResult;
    }
    return $this->_validationResult = $this->_validateFilter($this->getFilter());
  }

  /**
   * Validate current value against the filter object if it is here.
   *
   * @param Filter|null $filter
   *
   * @return bool
   */
  protected function _validateFilter($filter) {
    if ($filter instanceof Filter) {
      try {
        return $filter->validate($this->getCurrentValue());
      } catch (Filter\Exception $e) {
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
    if (parent::collect() && '' !== \trim($name)) {
      if ($filter = $this->getFilter()) {
        $value = $filter->filter($this->getCurrentValue());
        $this->collection()->owner()->data()->set(
          $name, NULL === $value ? $this->getDefaultValue() : $value
        );
      } elseif (NULL !== $this->getDefaultValue()) {
        $value = $this->getCurrentValue();
        if (\is_object($this->getDefaultValue())) {
          $value = (string)$value;
        } else {
          \settype($value, \gettype($this->getDefaultValue()));
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
      }
      if ($dialog->data()->has($name) && NULL !== ($value = $dialog->data()->get($name))) {
        return $value;
      }
    }
    return $this->getDefaultValue();
  }

  /**
   * Append field outer elements to DOM
   *
   * @param XML\Element $parent
   *
   * @return XML\Element
   */
  protected function _appendFieldTo(XML\Element $parent) {
    if ($this->hasCollection() &&
      $this->collection()->hasOwner() &&
      !$this->collection()->owner()->isSubmitted()) {
      $isValid = TRUE;
    } else {
      $isValid = $this->validate();
    }
    $field = $parent->appendElement(
      'field',
      [
        'caption' => $this->getCaption(),
        'class' => $this->_getFieldClass(),
        'error' => $isValid ? 'no' : 'yes',
        'hint' => $this->getHint(),
        'id' => $this->getId(),
        'disabled' => $this->getDisabled() ? 'yes' : '',
        'mandatory' => $this->getMandatory() ? 'yes' : ''
      ]
    );
    $this->description()->appendTo($field);
    return $field;
  }

  /**
   * Return field class name without stripped prefix.
   *
   * @param string $prefix
   *
   * @return string
   */
  protected function _getFieldClass($prefix = 'PapayaUI') {
    $class = \str_replace('\\', '', \get_class($this));
    if (0 === \strpos($class, $prefix)) {
      $class = \substr($class, \strlen($prefix));
    }
    return $class;
  }
}
