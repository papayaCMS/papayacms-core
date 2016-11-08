<?php

class PapayaUiListviewItemRadio extends PapayaUiListviewItem {

  private $_fieldName = '';

  /**
   * @var PapayaUiDialog
   */
  private $_dialog;

  private $_value = '';

  private $_checked = NULL;

  /**
   * @param string $image
   * @param PapayaUiString|string $caption
   * @param PapayaUiDialog $dialog
   * @param bool $fieldName
   * @param $value
   */
  public function __construct($image, $caption, PapayaUiDialog $dialog, $fieldName, $value) {
    parent::__construct($image, $caption);
    $this->_dialog = $dialog;
    $this->_fieldName = $fieldName;
    $this->_value = $value;
  }

  public function appendTo(PapayaXmlElement $parent) {
    $node = parent::appendTo($parent);
    $input = $node->appendElement(
      'input',
      [
        'type' => 'radio',
        'name' => new PapayaUiDialogFieldParameterName($this->_dialog, $this->_fieldName),
        'value' => $this->_value
      ]
    );
    if ($this->isChecked()) {
      $input->setAttribute('checked', 'checked');
    }
  }

  public function isChecked() {
    if (NULL === $this->_checked) {
      $this->_checked = FALSE;
      if ($this->_dialog->parameters()->get($this->_fieldName, '') === (string)$this->_value) {
        $this->_checked = TRUE;
      } elseif ($this->_dialog->data()->get($this->_fieldName, '') === (string)$this->_value) {
        $this->_checked = TRUE;
      }
    }
    return $this->_checked;
  }
}