<?php
abstract class PapayaMediaFileInfo extends PapayaObject implements \ArrayAccess, \IteratorAggregate {

  private $_fileName;
  private $_properties;

  public function __construct($fileName) {
    $this->_fileName = $fileName;
  }

  public function getFileName() {
    return $this->_fileName;
  }

  public function isSupported(array $fileProperties = []) {
    return TRUE;
  }

  public function getIterator() {
    return new ArrayIterator($this->getProperties());
  }

  protected function fetchProperties() {
    return [
      'filesize' => filesize($this->_fileName)
    ];
  }

  private function getProperties() {
    if (NULL === $this->_properties) {
      $this->_properties = $this->fetchProperties();
    }
    return $this->_properties;
  }

  public function offsetExists($offset) {
    return array_key_exists($offset, $this->getProperties());
  }

  public function offsetGet($offset) {
    return $this->getProperties()[$offset];
  }

  public function offsetSet($offset, $value) {
    throw new BadMethodCallException(sprintf('Object %s is immutable.', static::class));
  }

  public function offsetUnset($offset) {
    throw new BadMethodCallException(sprintf('Object %s is immutable.', static::class));
  }

}