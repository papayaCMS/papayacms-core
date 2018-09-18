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

namespace Papaya\UI\Toolbar;

/**
 * A menu/toolbar select box. This creates a seperate form (method get) with a <select>-field.
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property \Papaya\UI\Reference $reference
 * @property string $parameterName
 * @property string|\Papaya\UI\Text $caption
 * @property \Traversable|array $options
 * @property string|\Papaya\UI\Text $defaultOption
 * @property string|int|bool $currentValue
 * @property mixed defaultValue
 * @property mixed defaultCaption
 */
class Select extends Element {
  /**
   * parameter name for the select box
   *
   * @var string
   */
  protected $_parameterName = '';

  /**
   * select box caption
   *
   * @var string
   */
  protected $_caption = '';

  /**
   * Options list. If this parameter is an object it needs to implement Traversable.
   *
   * @var array|\Traversable
   */
  protected $_options;

  /**
   * Caption for a default option, added as first element. The value of this option element will
   * be an empty string.
   *
   * @var string|\Papaya\UI\Text
   */
  protected $_defaultCaption = '';

  /**
   * the default value is only used if a default caption is provided.
   *
   * @var string|int|bool
   */
  protected $_defaultValue = '';

  /**
   * buffer variable for the current value, if it is null it will be fetched from the request.
   *
   * @var string|int|bool|null
   */
  protected $_currentValue;

  /**
   * Define public properties.
   *
   * @var array
   */
  protected $_declaredProperties = [
    'caption' => ['_caption', '_caption'],
    'options' => ['options', 'options'],
    'defaultCaption' => ['_defaultCaption', '_defaultCaption'],
    'defaultValue' => ['_defaultValue', '_defaultValue'],
    'reference' => ['reference', 'reference'],
    'parameterName' => ['_parameterName', '_parameterName'],
    'currentValue' => ['getCurrentValue', 'setCurrentValue']
  ];

  /**
   * Initialize object, store parameter name and options
   *
   * @param string|array $parameterName
   * @param array|\Traversable $options
   */
  public function __construct($parameterName, $options) {
    $this->_parameterName = $parameterName;
    $this->options($options);
  }

  /**
   * Store options list. The options must be an array or implement the Traversable interface.
   *
   * @param array|\Traversable $options
   * @throws \InvalidArgumentException
   * @return array|\Traversable
   */
  public function options($options = NULL) {
    if (isset($options)) {
      if (\is_array($options) ||
        ($options instanceof \Traversable)) {
        $this->_options = $options;
      } else {
        throw new \InvalidArgumentException(
          'Argument $options must be an array or implement Traversable.'
        );
      }
    }
    return $this->_options;
  }

  /**
   * Check if a current value is set. If not read the current value from the request and return it.
   *
   * @return string|int|bool
   */
  public function getCurrentValue() {
    if (\is_null($this->_currentValue)) {
      $name = new \Papaya\Request\Parameters\Name(
        $this->_parameterName, $this->reference()->getParameterGroupSeparator()
      );
      $this->_currentValue = $this->validateCurrentValue(
        $this->papaya()->request->getParameter((string)$name, $this->_defaultValue)
      );
    }
    return $this->_currentValue;
  }

  /**
   * Simple setter for the current value
   *
   * @param string|int|bool $value
   */
  public function setCurrentValue($value) {
    $this->_currentValue = $this->validateCurrentValue($value);
  }

  /**
   * Checks if the given value equals one of the keys in the options, return default value if not.
   *
   * @param $currentValue
   * @return mixed
   */
  private function validateCurrentValue($currentValue) {
    foreach ($this->_options as $value => $caption) {
      if ($value == $currentValue) {
        return $value;
      }
    }
    return $this->_defaultValue;
  }

  /**
   * Append select xml elements to xml document
   *
   * @param \Papaya\XML\Element $parent
   * @return \Papaya\XML\Element
   */
  public function appendTo(\Papaya\XML\Element $parent) {
    $select = $parent->appendElement(
      'combo',
      [
        'name' => new \Papaya\Request\Parameters\Name(
          $this->_parameterName, $this->reference()->getParameterGroupSeparator()
        ),
        'action' => $this->reference()->getRelative(NULL, FALSE)
      ]
    );
    foreach ($this->reference()->getParametersList() as $name => $value) {
      $select->appendElement(
        'parameter', ['name' => $name, 'value' => $value]
      );
    }
    $caption = (string)$this->_caption;
    if (!empty($caption)) {
      $select->setAttribute('title', $caption);
    }
    $default = (string)$this->defaultCaption;
    if (!empty($default)) {
      $select->appendElement(
        'option', ['value' => (string)$this->_defaultValue], (string)$default
      );
    }
    $currentValue = $this->getCurrentValue();
    foreach ($this->_options as $value => $caption) {
      $option = $select->appendElement(
        'option', ['value' => (string)$value], (string)$caption
      );
      if ($currentValue == $value) {
        $option->setAttribute('selected', 'selected');
      }
    }
    return $select;
  }
}
