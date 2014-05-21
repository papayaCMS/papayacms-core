<?php

class PapayaTemplateSimpleVisitorOutput extends PapayaTemplateSimpleVisitor {

  private $_buffer = '';

  private $_callbacks = NULL;

  public function clear() {
    $this->_buffer = '';
  }

  public function __toString() {
    return $this->_buffer;
  }

  public function callbacks(PapayaTemplateSimpleVisitorOutputCallbacks $callbacks = NULL) {
    if (isset($callbacks)) {
      $this->_callbacks = $callbacks;
    } elseif (NULL == $this->_callbacks) {
      $this->_callbacks = new PapayaTemplateSimpleVisitorOutputCallbacks();
    }
    return $this->_callbacks;
  }

  public function visitNodeOutput(PapayaTemplateSimpleAstNodeOutput $node) {
    $this->_buffer .= $node->text;
  }

  public function visitNodeValue(PapayaTemplateSimpleAstNodeValue $node) {
    if ($value = $this->callbacks()->onGetValue($node->name)) {
      $this->_buffer .= (string)$value;
    } else {
      $this->_buffer .= (string)$node->default;
    }
  }

}