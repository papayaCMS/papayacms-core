<?php

class PapayaUiStringPlaceholders extends PapayaUiString {


  /**
  * Allow to cast the object into a string, replacing the {key} placeholders in the string.
  *
  * return string
  */
  public function __toString() {
    if (is_null($this->_string)) {
      $this->_string = preg_replace_callback(
        '(\\{(?P<key>[^}\r\n ]+)\\})u',
        array($this, 'replacePlaceholders'),
        $this->_pattern
      );
    }
    return $this->_string;
  }

  public function replacePlaceholders($match) {
    if (isset($match['key']) && isset($this->_values[$match['key']])) {
      return $this->_values[$match['key']];
    }
    return '';
  }
}