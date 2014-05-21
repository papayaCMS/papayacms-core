<?php
/**
* Abstract superclass implementing basic dialog features
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
* @version $Id: Dialog.php 39725 2014-04-07 17:19:34Z weinert $
*/

/**
* Abstract superclass implementing basic dialog features
*
* @package Papaya-Library
* @subpackage Ui
*
* @property string|PapayaUiString $caption
* @property string $image
* @property PapayaUiDialogFields $fields
* @property PapayaUiDialogButtons $buttons
* @property PapayaRequestParameters $hiddenFields
* @property PapayaRequestParameters $hiddenValues
* @property PapayaRequestParameters $data
* @property PapayaUiDialogOptions $options
*/
class PapayaUiDialog extends PapayaUiControlInteractive {

  /**
  * Default dialog form method and parameter handling
  * @var integer
  */
  protected $_parameterMethod = self::METHOD_POST;

  /**
  * Dialog form action
  * @var NULL|string
  */
  private $_action = NULL;

  /**
   * Dialog form content encoding
   * @var string
   */
  private $_encoding = 'application/x-www-form-urlencoded';

  /**
  * Dialog caption text  *
  * @var string|PapayaUiString
  */
  private $_caption = '';

  /**
  * Dialog caption image  *
  * @var string
  */
  protected $_image = '';

  /**
  * Dialogs should cache the execution result.
  *
  * @var NULL|boolean
  */
  protected $_executionResult = NULL;

  /**
  * Dialogs should cache the submit check result.
  *
  * @var NULL|boolean
  */
  protected $_isSubmittedResult = NULL;

  /**
  * Hidden values are output as hidden input field but not part of the parameter group
  * @var PapayaRequestParameters|NULL
  */
  private $_hiddenValues = NULL;

  /**
  * Hidden fields are output as hidden input fields, the parameter group value ist used for them
  * @var PapayaRequestParameters|NULL
  */
  private $_hiddenFields = NULL;

  /**
  * Token helper object
  * @var PapayaUiTokens|NULL
  */
  private $_tokens = NULL;

  /**
  * Error list object
  * @var PapayaUiDialogErrors|NULL
  */
  private $_errors = NULL;

  /**
  * Dialog input fields
  * @var PapayaUiDialogFields
  */
  private $_fields = NULL;

  /**
  * Dialog buttons
  * @var PapayaUiDialogButtons
  */
  private $_buttons = NULL;

  /**
  * Dialog data
  * @var PapayaRequestParameters
  */
  private $_data = NULL;

  /**
  * Dialog options  *
  * @var PapayaUiDialogOptions
  */
  private $_options = NULL;

  /**
  * Dialog description data (additional properties)
  * @var PapayaUiDialogElementDescription
  */
  private $_description = NULL;

  /**
  * Dialog owner - used to verify token
  * @var object
  */
  protected $_owner = NULL;

  /**
  * declare dynamic properties
  *
  * @var array
  */
  protected $_declaredProperties = array(
    'caption' => array('caption', 'caption'),
    'image' => array('_image', '_image'),
    'fields' => array('fields', 'fields'),
    'buttons' => array('buttons', 'buttons'),
    'hiddenFields' => array('hiddenFields', 'hiddenFields'),
    'hiddenValues' => array('hiddenValues', 'hiddenValues'),
    'data' => array('data', 'data'),
    'options' => array('options', 'options'),
    'description' => array('description', 'description')
  );

  /**
  * Create object and set owner if provided.
  *
  * The owner is used to verify the dialog token.
  *
  * @param object|NULL $owner
  */
  public function __construct($owner = NULL) {
    PapayaUtilConstraints::assertObjectOrNull($owner);
    $this->_owner = $owner;
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
  * @return boolean
  */
  public function isSubmitted() {
    if (is_null($this->_isSubmittedResult)) {
      $requestMethod = $this->papaya()->request->getMethod();
      $validMethods = array(
        'get' => array(self::METHOD_GET, self::METHOD_MIXED_GET, self::METHOD_MIXED_POST),
        'post' => array(self::METHOD_POST, self::METHOD_MIXED_GET, self::METHOD_MIXED_POST)
      );
      if (isset($validMethods[$requestMethod]) &&
          in_array($this->parameterMethod(), $validMethods[$requestMethod])) {
        $confirmation = $this->parameters()->get('confirmation');
        if (!$this->options()->useConfirmation ||
            ($this->hiddenFields()->isEmpty() && $confirmation == 'true') ||
            $confirmation == $this->hiddenFields()->getChecksum()) {
          if (!$this->options()->useToken ||
              $this->tokens()->validate($this->parameters()->get('token', ''), $this->_owner)) {
            return $this->_isSubmittedResult = TRUE;
          }
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
  * @return boolean
  */
  public function execute() {
    if (is_null($this->_executionResult)) {
      if ($this->isSubmitted()) {
        if ($this->fields()->validate()) {
          $this->fields()->collect();
          $this->buttons()->collect();
          return $this->_executionResult = TRUE;
        }
      }
      $this->_executionResult = FALSE;
    }
    return $this->_executionResult;
  }

  /**
  * Append the dialog output to a DOM
  *
  * @param PapayaXmlElement $parent
  * @return PapayaXmlElement|NULL
  */
  public function appendTo(PapayaXmlElement $parent) {
    $dialog = $parent->appendElement(
      'dialog-box',
      array(
        'action' => $this->action(),
        'method' => $this->getMethodString()
      )
    );
    $encoding = $this->getEncoding();
    if (!(empty($encoding) || $encoding == 'application/x-www-form-urlencoded')) {
      $dialog->setAttribute('enctype', $encoding);
    }
    if (!empty($this->_caption)) {
      $dialog->appendElement(
        'title',
        array(
          'caption' => (string)$this->_caption,
          'icon' => (string)$this->image
        )
      );
    }
    $this->description()->appendTo($dialog);
    $this->options()->appendTo($dialog);
    $this->appendHidden($dialog, $this->hiddenValues());
    $this->appendHidden($dialog, $this->hiddenFields(), $this->parameterGroup());
    $values = new PapayaRequestParameters();
    if ($this->options()->useConfirmation) {
      $values->set(
        'confirmation',
        $this->hiddenFields()->isEmpty() ? 'true' : $this->hiddenFields()->getCheckSum()
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

  public function setEncoding($encoding) {
    PapayaUtilConstraints::assertContains(
      array(
        'application/x-www-form-urlencoded',
        'multipart/form-data',
        'text/plain'
      ),
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
    $methods = array(
      self::METHOD_POST => 'post',
      self::METHOD_GET => 'get',
      self::METHOD_MIXED_POST => 'post',
      self::METHOD_MIXED_GET => 'get'
    );
    return $methods[$this->parameterMethod()];
  }

  /**
  * Access hidden parameter values
  *
  * This function gives you access to parameters object holding the hidden values of the
  * dialog. Hidden values do not use the parameter group name.
  *
  * @param PapayaRequestParameters $values
  * @return PapayaRequestParameters
  */
  public function hiddenValues(PapayaRequestParameters $values = NULL) {
    if (isset($values)) {
      $this->_hiddenValues = $values;
    }
    if (is_null($this->_hiddenValues)) {
      $this->_hiddenValues = new PapayaRequestParameters();
    }
    return $this->_hiddenValues;
  }

  /**
  * Access hidden fields
  *
  * This function gives you access to parameters object holding the hidden fields of the
  * dialog. Hidden fields use the parameter group name.
  *
  * @param PapayaRequestParameters|NULL $values
  * @return PapayaRequestParameters
  */
  public function hiddenFields(PapayaRequestParameters $values = NULL) {
    if (isset($values)) {
      $this->_hiddenFields = $values;
    }
    if (is_null($this->_hiddenFields)) {
      $this->_hiddenFields = new PapayaRequestParameters();
    }
    return $this->_hiddenFields;
  }

  /**
  * Getter/Setter for csrf token manager including implizit create
  *
  * @param PapayaUiTokens $tokens
  * @return PapayaUiTokens
  */
  public function tokens(PapayaUiTokens $tokens = NULL) {
    if (isset($tokens)) {
      $this->_tokens = $tokens;
    } elseif (is_null($this->_tokens)) {
      $this->_tokens = new PapayaUiTokens();
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
  * @param string|NULL $action
  * @return string
  */
  public function action($action = NULL) {
    if (isset($action)) {
      $this->_action = empty($action) ? NULL : (string)$action;
    }
    if (NULL === $this->_action) {
      $this->_action = $this->papaya()->request->getUrl()->getPathUrl();
    }
    return $this->_action;
  }

  /**
  * Append a group hidden elements to the output (recursive function)
  *
  * @param PapayaXmlElement $parent
  * @param PapayaRequestParameters $values
  * @param string|NULL $path
  * @return PapayaXmlElement
  */
  protected function appendHidden(
    PapayaXmlElement $parent, PapayaRequestParameters $values, $path = NULL
  ) {
    foreach ($values as $name => $value) {
      $nameObject = $this->getParameterName($name);
      if (isset($path)) {
        $nameObject->prepend($path);
      }
      $namePath = (string)$nameObject;
      if (is_array($value)) {
        $this->appendHidden($parent, $values->getGroup($name), $namePath);
      } else {
        $parent->appendElement(
          'input', array('type' => 'hidden', 'name' => $namePath, 'value' => $value)
        );
      }
    }
    return $parent;
  }

  /**
  * Parses a parameter name into an {@see PapayaRequestParametersName}. The object can be casted to
  * string. If the dialog uses the method "GET" the request parameter level sepearator will be used.
  *
  * @param string|array $name
  * @return PapayaRequestParametersName
  */
  public function getParameterName($name) {
    $parts = new PapayaRequestParametersName($name);
    if ($this->parameterMethod() == self::METHOD_GET) {
      $parts->separator($this->papaya()->request->getParameterGroupSeparator());
    }
    return $parts;
  }

  /**
  * Getter/Setter for the dialog errors
  *
  * Error handler
  *
  * @param PapayaUiDialogErrors|NULL $errors
  * @return PapayaUiDialogErrors
  */
  public function errors(PapayaUiDialogErrors $errors = NULL) {
    if (isset($errors)) {
      $this->_errors = $errors;
    }
    if (is_null($this->_errors)) {
      $this->_errors = new PapayaUiDialogErrors();
    }
    return $this->_errors;
  }

  /**
  * Collect errors from fields
  *
  * @param Exception $exception
  * @param PapayaUiDialogField $field
  */
  public function handleValidationFailure(Exception $exception, PapayaUiDialogField $field) {
    $this->_executionResult = FALSE;
    $this->errors()->add($exception, $field);
  }

  /**
   * Getter/Setter for dialog options object
   *
   * @param PapayaUiDialogOptions $options
   * @return PapayaUiDialogOptions
   */
  public function options(PapayaUiDialogOptions $options = NULL) {
    if (isset($options)) {
      $this->_options = $options;
    }
    if (is_null($this->_options)) {
      $this->_options = new PapayaUiDialogOptions();
    }
    return $this->_options;
  }

  /**
  * Get/Set dialog title
  *
  * For now this is only a string/PapayaUiString but i can imagine that it will become
  * a more complex subobject later allowing an icon and buttons.
  *
  * @param string|PapayaUiString $caption
  * @return string|PapayaUiString
  */
  public function caption($caption = NULL) {
    if (isset($caption)) {
      $this->_caption = $caption;
    }
    return $this->_caption;
  }

  /**
  * Deprecated alias for {@see self::caption()}.
  *
  * @param string|PapayaUiString $caption
  * @return string|PapayaUiString
  */
  public function title($caption) {
    return $this->caption($caption);
  }

  /**
   * Dialog fields getter/setter
   *
   * @param PapayaUiDialogFields $fields
   * @return \PapayaUiDialogFields
   */
  public function fields($fields = NULL) {
    if (isset($fields)) {
      if ($fields instanceof PapayaUiDialogFields) {
        $this->_fields = $fields;
        $fields->owner($this);
      } else {
        PapayaUtilConstraints::assertArrayOrTraversable($fields);
        foreach ($fields as $field) {
          $this->fields()->add($field);
        }
      }
    }
    if (is_null($this->_fields)) {
      $this->_fields = new PapayaUiDialogFields($this);
      $this->_fields->papaya($this->papaya());
    }
    return $this->_fields;
  }

  /**
   * Dialog buttons getter/setter
   *
   * @param PapayaUiDialogButtons $buttons
   * @return PapayaUiDialogButtons
   */
  public function buttons(PapayaUiDialogButtons $buttons = NULL) {
    if (isset($buttons)) {
      $this->_buttons = $buttons;
      $buttons->owner($this);
    }
    if (is_null($this->_buttons)) {
      $this->_buttons = new PapayaUiDialogButtons($this);
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
   * @param PapayaRequestParameters $data
   * @return PapayaRequestParameters
   */
  public function data(PapayaRequestParameters $data = NULL) {
    if (isset($data)) {
      $this->_data = $data;
    }
    if (is_null($this->_data)) {
      $this->_data = new PapayaRequestParameters();
      $this->_data->merge($this->hiddenFields());
    }
    return $this->_data;
  }

  /**
  * Getter/Setter for the description subobject.
  *
  * @param PapayaUiDialogElementDescription
  * @return PapayaUiDialogElementDescription
  */
  public function description(PapayaUiDialogElementDescription $description = NULL) {
    if (isset($description)) {
      $this->_description = $description;
    } elseif (is_null($this->_description)) {
      $this->_description = new PapayaUiDialogElementDescription();
      $this->_description->papaya($this->papaya());
    }
    return $this->_description;
  }
}
