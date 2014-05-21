<?php

abstract class PapayaContentStructureNode extends PapayaObject {

  private $_properties = array();

  public function __construct($properties) {
    $this->_properties = $properties;
  }

  public function __isset($name) {
    try {
      $value = $this->$name;
      return isset($value);
    } catch (UnexpectedValueException $e) {
      return FALSE;
    }
  }

  public function __get($name) {
    $getter = 'get'.PapayaUtilStringIdentifier::toCamelCase($name, TRUE);
    if (method_exists($this, $getter)) {
      return call_user_func(array($this, $getter));
    } elseif (array_key_exists($name, $this->_properties)) {
      return $this->_properties[$name];
    }
    throw new UnexpectedValueException(
      sprintf(
        'Can not read unknown property "%s::$%s".',
        get_class($this),
        $name
      )
    );
  }

  public function __set($name, $value) {
    $setter = 'set'.PapayaUtilStringIdentifier::toCamelCase($name, TRUE);
    if (method_exists($this, $setter)) {
      call_user_func(array($this, $setter), $value);
    } else {
      $this->setValue($name, $value);
    }
  }

  protected function setValue($name, $value) {
    if (array_key_exists($name, $this->_properties)) {
      $this->_properties[$name] = $value;
    } else {
      throw new UnexpectedValueException(
        sprintf(
          'Can not write unknown property "%s::$%s".',
          get_class($this),
          $name
        )
      );
    }
  }

  public function setName($name) {
    PapayaUtilStringXml::isQName($name);
    $this->_properties['name'] = $name;
  }

}