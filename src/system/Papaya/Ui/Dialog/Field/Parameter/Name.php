<?php

class PapayaUiDialogFieldParameterName {

  /**
   * @var PapayaUiDialog|NULL
   */
  private $_dialog;
  /**
   * @var string|array
   */
  private $_fieldName;

  /**
   * PapayaUiDialogFieldParameterName constructor.
   * @param PapayaUiDialog|NULL $dialog
   * @param $fieldName
   */
  public function __construct(PapayaUiDialog $dialog = NULL, $fieldName) {
    $this->_dialog = $dialog;
    $this->_fieldName = $fieldName;
  }

  /**
   * @param bool $withGroup
   * @return string
   */
  public function get($withGroup = TRUE) {
    if ($withGroup && $this->_dialog instanceof PapayaUiDialog) {
      $name = $this->_dialog->getParameterName($this->_fieldName);
      $prefix = $this->_dialog->parameterGroup();
      if (isset($prefix)) {
        $name->prepend($prefix);
      }
    } else {
      $name = new PapayaRequestParametersName($this->_fieldName);
    }
    return (string)$name;
  }

  /**
   * @return string
   */
  public function __toString() {
    return $this->get();
  }

}