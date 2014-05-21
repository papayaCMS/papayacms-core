<?php
class PluginLoader_SampleClass extends PapayaObject {

  public $data = NULL;

  public function setData($data = NULL) {
    $this->data = $data;
  }

}

class PluginLoader_SampleClassEditable
  extends PapayaObject
  implements PapayaPluginEditable {

  public $content = NULL;

  public function content(PapayaPluginEditableContent $content = NULL) {
    if (isset($content)) {
      $this->content = $content;
    } elseif (NULL === $this->content) {
      $this->content = new PapayaPluginEditableContent();
    }
    return $this->content;
  }

}