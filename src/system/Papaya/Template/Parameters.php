<?php

class PapayaTemplateParameters extends PapayaObjectOptionsList {

  public function __construct(array $options = NULL) {
    $this['SYSTEM_TIME'] = date('Y-m-d H:i:s');
    $this['SYSTEM_TIME_OFFSET'] = date('O');
    $this['PAPAYA_VERSION'] = defined('PAPAYA_VERSION_STRING') ? PAPAYA_VERSION_STRING : '';
    parent::__construct($options);
  }
}