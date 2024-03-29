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
namespace Papaya\UI;

use Papaya\BaseObject\Interfaces\StringCastable;
use Papaya\Request;
use Papaya\Utility;
use Papaya\XML;

/**
 * Abstract superclass implementing basic dialog features
 *
 * @package Papaya-Library
 * @subpackage UI
 *
 * @property string|StringCastable $caption
 * @property string $image
 * @property Dialog\Element\Description $description
 * @property Dialog\Fields $fields
 * @property Dialog\Buttons $buttons
 * @property Request\Parameters $hiddenFields
 * @property Request\Parameters $hiddenValues
 * @property Request\Parameters $data
 * @property Dialog\Options $options
 */
class Dialog extends Control\Interactive {
  /**
   * Default dialog form method and parameter handling
   *
   * @var int
   */
  protected $_parameterMethod = self::METHOD_POST;

  /**
   * Dialog form action
   *
   * @var null|string
   */
  private $_action;

  /**
   * Dialog form content encoding
   *
   * @var string
   */
  private $_encoding = 'application/x-www-form-urlencoded';

  /**
   * Dialog caption text  *
   *
   * @var string|StringCastable
   */
  private $_caption = '';

  /**
   * Dialog caption image  *
   *
   * @var string
   */
  protected $_image = '';

  /**
   * Dialogs should cache the execution result.
   *
   * @var null|bool
   */
  protected $_executionResult;

  /**
   * Dialogs should cache the submit check result.
   *
   * @var null|bool
   */
  protected $_isSubmittedResult;

  /**
   * Hidden values are output as hidden input field but not part of the parameter group
   *
   * @var Request\Parameters|null
   */
  private $_hiddenValues;

  /**
   * Hidden fields are output as hidden input fields, the parameter group value ist used for them
   *
   * @var Request\Parameters
   */
  private $_hiddenFields;

  /**
   * Token helper object
   *
   * @var Tokens|null
   */
  private $_tokens;

  /**
   * Error list object
   *
   * @var Dialog\Errors|null
   */
  private $_errors;

  /**
   * Dialog input fields
   *
   * @var Dialog\Fields
   */
  private $_fields;

  /**
   * Dialog buttons
   *
   * @var Dialog\Buttons
   */
  private $_buttons;

  /**
   * Dialog data
   *
   * @var Request\Parameters
   */
  private $_data;

  /**
   * Dialog options  *
   *
   * @var Dialog\Options
   */
  private $_options;

  /**
   * Dialog description data (additional properties)
   *
   * @var Dialog\Element\Description
   */
  private $_description;

  /**
   * Dialog owner - used to verify token
   *
   * @var object
   */
  protected $_owner;

  /**
   * declare dynamic properties
   *
   * @var array
   */
  protected $_declaredProperties = [
    'caption' => ['caption', 'caption'],
    'image' => ['_image', '_image'],
    'fields' => ['fields', 'fields'],
    'buttons' => ['buttons', 'buttons'],
    'hiddenFields' => ['hiddenFields', 'hiddenFields'],
    'hiddenValues' => ['hiddenValues', 'hiddenValues'],
    'data' => ['data', 'data'],
    'options' => ['options', 'options'],
    'description' => ['description', 'description']
  ];

  /**
   * Create object and set owner if provided.
   *
   * The owner is used to verify the dialog token.
   *
   * @param object|null $owner
   *
   * @throws \UnexpectedValueException
   */
  public function __construct($owner = NULL) {
    Utility\Constraints::assertObjectOrNull($owner);
    $this->_owner = $owner;
  }

  public function getOwner(): ?object {
    return $this->_owner;
  }

  /**
   * Check if the dialog was submitted.
   *
   * It checks if the request method matches die dialog method and verifies a checksum created
   * from all hidden fields.
   *
   * If not disabled, validate the csrf token. A dialog without or with an invalid csrf token is
   * considered not submitted by default. The csrf token can be disabled using the "useToken"
   * option.
   *
   * The result of this method is cached. A second call to this method will return the result
   * of the first, without really validating the options again.
   *
   * @return bool
   */
  public function isSubmitted() {
    if (NULL === $this->_isSubmittedResult) {
      $requestMethod = $this->papaya()->request->getMethod();
      $validMethods = [
        'get' => [self::METHOD_GET, self::METHOD_MIXED_GET, self::METHOD_MIXED_POST],
        'post' => [self::METHOD_POST, self::METHOD_MIXED_GET, self::METHOD_MIXED_POST]
      ];
      if (
        isset($validMethods[$requestMethod]) &&
        \in_array($this->parameterMethod(), $validMethods[$requestMethod], TRUE)
      ) {
        $confirmation = $this->parameters()->get('confirmation');
        if (
          (
            !$this->options()->useConfirmation ||
            ($this->hiddenFields()->isEmpty() && 'true' === $confirmation) ||
            $confirmation === $this->hiddenFields()->getChecksum()
          ) &&
          (
            !$this->options()->useToken ||
            $this->tokens()->validate($this->parameters()->get('token', ''), $this->_owner)
          )
        ) {
          return $this->_isSubmittedResult = TRUE;
        }
      }
      $this->_isSubmittedResult = FALSE;
    }
    return $this->_isSubmittedResult;
  }

  /**
   * Execute dialog and collect data after validation.
   *
   * The result is cached, so the validation and collection runs only one time.
   *
   * @return bool
   *
   * @throws \UnexpectedValueException
   * @throws \LogicException
   */
  public function execute() {
    if (NULL === $this->_executionResult) {
      if ($this->isSubmitted() && $this->fields()->validate()) {
        $this->fields()->collect();
        $this->buttons()->collect();
        return $this->_executionResult = TRUE;
      }
      $this->_executionResult = FALSE;
    }
    return $this->_executionResult;
  }

  /**
   * Append the dialog output to a DOM
   *
   * @param XML\Element $parent
   *
   * @return XML\Element|null
   *
   * @throws \UnexpectedValueException
   * @throws \LogicException
   * @throws \InvalidArgumentException
   */
  public function appendTo(XML\Element $parent) {
    $dialog = $parent->appendElement(
      'dialog-box',
      [
        'action' => $this->action(),
        'method' => $this->getMethodString()
      ]
    );
    $encoding = $this->getEncoding();
    if (!(empty($encoding) || 'application/x-www-form-urlencoded' === $encoding)) {
      $dialog->setAttribute('enctype', $encoding);
    }
    if (!empty($this->_caption)) {
      $dialog->appendElement(
        'title',
        [
          'caption' => (string)$this->_caption,
          'icon' => (string)$this->image
        ]
      );
    }
    $this->description()->appendTo($dialog);
    $this->options()->appendTo($dialog);
    $this->appendHidden($dialog, $this->hiddenValues());
    $this->appendHidden($dialog, $this->hiddenFields(), $this->parameterGroup());
    $values = new Request\Parameters();
    if ($this->options()->useConfirmation) {
      $values->set(
        'confirmation',
        $this->hiddenFields()->isEmpty() ? 'true' : $this->hiddenFields()->getChecksum()
      );
    }
    if ($this->options()->useToken) {
      $values->set('token', $this->tokens()->create($this->_owner));
    }
    $this->appendHidden($dialog, $values, $this->parameterGroup());
    $this->fields()->appendTo($dialog);
    $this->buttons()->appendTo($dialog);
    $dialog->appendTo($parent);
    return $dialog;
  }

  /**
   * @param string $encoding
   *
   * @throws \UnexpectedValueException
   */
  public function setEncoding($encoding) {
    Utility\Constraints::assertContains(
      [
        'application/x-www-form-urlencoded',
        'multipart/form-data',
        'text/plain'
      ],
      $encoding,
      'Invalid form encoding.'
    );
    $this->_encoding = $encoding;
  }

  public function getEncoding() {
    return $this->_encoding;
  }

  /**
   * Get request method as string (used for form methods)
   *
   * @return string
   */
  protected function getMethodString() {
    $methods = [
      self::METHOD_POST => 'post',
      self::METHOD_GET => 'get',
      self::METHOD_MIXED_POST => 'post',
      self::METHOD_MIXED_GET => 'get'
    ];
    return $methods[$this->parameterMethod()];
  }

  /**
   * Access hidden parameter values
   *
   * This function gives you access to parameters object holding the hidden values of the
   * dialog. Hidden values do not use the parameter group name.
   *
   * @param Request\Parameters $values
   *
   * @return Request\Parameters
   */
  public function hiddenValues(Request\Parameters $values = NULL) {
    if (NULL !== $values) {
      $this->_hiddenValues = $values;
    }
    if (NULL === $this->_hiddenValues) {
      $this->_hiddenValues = new Request\Parameters();
    }
    return $this->_hiddenValues;
  }

  /**
   * Access hidden fields
   *
   * This function gives you access to parameters object holding the hidden fields of the
   * dialog. Hidden fields use the parameter group name.
   *
   * @param Request\Parameters $values
   * @return Request\Parameters
   */
  public function hiddenFields(Request\Parameters $values = NULL) {
    if (NULL !== $values) {
      $this->_hiddenFields = $values;
    } elseif (NULL === $this->_hiddenFields) {
      $this->_hiddenFields = new Request\Parameters();
    }
    return $this->_hiddenFields;
  }

  /**
   * Getter/Setter for csrf token manager including implicit create
   *
   * @param Tokens $tokens
   *
   * @return Tokens
   */
  public function tokens(Tokens $tokens = NULL) {
    if (NULL !== $tokens) {
      $this->_tokens = $tokens;
    } elseif (NULL === $this->_tokens) {
      $this->_tokens = new Tokens();
      $this->_tokens->papaya($this->papaya());
    }
    return $this->_tokens;
  }

  /**
   * Getter/Setter for the dialog form action
   *
   * If it is read without a write before it will return the current request url
   * without query string.
   *
   * @param string|null $action
   *
   * @return string
   */
  public function action($action = NULL) {
    if (NULL !== $action) {
      $this->_action = NULL === $action ? NULL : (string)$action;
    }
    if (NULL === $this->_action && ($url = $this->papaya()->request->getURL())) {
      $this->_action = $url->getPathURL();
    }
    return $this->_action;
  }

  /**
   * Append a group hidden elements to the output (recursive function)
   *
   * @param XML\Element $parent
   * @param Request\Parameters $values
   * @param string|null $path
   *
   * @return XML\Element
   *
   * @throws \InvalidArgumentException
   */
  protected function appendHidden(
    XML\Element $parent, Request\Parameters $values, $path = NULL
  ) {
    foreach ($values as $name => $value) {
      $nameObject = $this->getParameterName($name);
      if (NULL !== $path) {
        $nameObject->prepend($path);
      }
      $namePath = (string)$nameObject;
      if (\is_array($value)) {
        $this->appendHidden($parent, $values->getGroup($name), $namePath);
      } else {
        $parent->appendElement(
          'input', ['type' => 'hidden', 'name' => $namePath, 'value' => $value]
        );
      }
    }
    return $parent;
  }

  /**
   * Parses a parameter name into an {@see \Papaya\Request\Parameters\Papaya\Request\Parameters\Name}. The object can
   * be casted to string. If the dialog uses the method "GET" the request parameter level sepearator will be used.
   *
   * @param string|array $name
   *
   * @return Request\Parameters\Name
   *
   * @throws \InvalidArgumentException
   */
  public function getParameterName($name) {
    $parts = new Request\Parameters\Name($name);
    if (self::METHOD_GET === $this->parameterMethod()) {
      $parts->separator($this->papaya()->request->getParameterGroupSeparator());
    }
    return $parts;
  }

  /**
   * Getter/Setter for the dialog errors
   *
   * Error handler
   *
   * @param Dialog\Errors|null $errors
   *
   * @return Dialog\Errors
   */
  public function errors(Dialog\Errors $errors = NULL) {
    if (NULL !== $errors) {
      $this->_errors = $errors;
    }
    if (NULL === $this->_errors) {
      $this->_errors = new Dialog\Errors();
    }
    return $this->_errors;
  }

  /**
   * Collect errors from fields
   *
   * @param \Exception $exception
   * @param Dialog\Field $field
   */
  public function handleValidationFailure(\Exception $exception, Dialog\Field $field) {
    $this->_executionResult = FALSE;
    $this->errors()->add($exception, $field);
  }

  /**
   * Getter/Setter for dialog options object
   *
   * @param Dialog\Options $options
   *
   * @return Dialog\Options
   */
  public function options(Dialog\Options $options = NULL) {
    if (NULL !== $options) {
      $this->_options = $options;
    }
    if (NULL === $this->_options) {
      $this->_options = new Dialog\Options();
    }
    return $this->_options;
  }

  /**
   * Get/Set dialog title
   *
   * For now this is only a string/\Papaya\UI\Text but i can imagine that it will become
   * a more complex subobject later allowing an icon and buttons.
   *
   * @param string|\Papaya\UI\Text $caption
   *
   * @return string|\Papaya\UI\Text
   */
  public function caption($caption = NULL) {
    if (NULL !== $caption) {
      $this->_caption = $caption;
    }
    return $this->_caption;
  }

  /**
   * Deprecated alias for {@see self::caption()}.
   *
   * @param string|\Papaya\UI\Text $caption
   *
   * @return string|\Papaya\UI\Text
   */
  public function title($caption) {
    return $this->caption($caption);
  }

  /**
   * Dialog fields getter/setter
   *
   * @param Dialog\Fields|array|\Traversable|null $fields
   *
   * @return Dialog\Fields
   *
   * @throws \UnexpectedValueException
   * @throws \LogicException
   */
  public function fields($fields = NULL) {
    if (NULL !== $fields) {
      if ($fields instanceof Dialog\Fields) {
        $this->_fields = $fields;
        $fields->owner($this);
      } else {
        Utility\Constraints::assertArrayOrTraversable($fields);
        /* @noinspection ForeachSourceInspection */
        foreach ($fields as $field) {
          $this->fields()->add($field);
        }
      }
    }
    if (NULL === $this->_fields) {
      $this->_fields = new Dialog\Fields($this);
      $this->_fields->papaya($this->papaya());
    }
    return $this->_fields;
  }

  /**
   * Dialog buttons getter/setter
   *
   * @param Dialog\Buttons $buttons
   *
   * @return Dialog\Buttons
   *
   * @throws \LogicException
   */
  public function buttons(Dialog\Buttons $buttons = NULL) {
    if (NULL !== $buttons) {
      $this->_buttons = $buttons;
      $buttons->owner($this);
    }
    if (NULL === $this->_buttons) {
      $this->_buttons = new Dialog\Buttons($this);
      $this->_buttons->papaya($this->papaya());
    }
    return $this->_buttons;
  }

  /**
   * Dialog data getter/setter
   *
   * The data object contains the user input data after the dialog was executed
   * if the validation was successful.
   *
   * The execution call the method collect() on each field and button to fill up this object.
   *
   * @param Request\Parameters $data
   *
   * @return Request\Parameters
   */
  public function data(Request\Parameters $data = NULL) {
    if (NULL !== $data) {
      $this->_data = $data;
    }
    if (NULL === $this->_data) {
      $this->_data = new Request\Parameters();
      $this->_data->merge($this->hiddenFields());
    }
    return $this->_data;
  }

  /**
   * Getter/Setter for the description subobject.
   *
   * @param Dialog\Element\Description
   *
   * @return Dialog\Element\Description
   */
  public function description(Dialog\Element\Description $description = NULL) {
    if (NULL !== $description) {
      $this->_description = $description;
    } elseif (NULL === $this->_description) {
      $this->_description = new Dialog\Element\Description();
      $this->_description->papaya($this->papaya());
    }
    return $this->_description;
  }
}
