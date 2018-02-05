<?php
abstract class PapayaMediaFileInfo extends PapayaObject implements \ArrayAccess, \IteratorAggregate {

  private $_file;
  private $_properties;
  private $_originalFileName;

  public function __construct($file, $originalFileName = '') {
    $this->_file = $file;
    $this->_originalFileName = $originalFileName;
  }

  public function getFile() {
    return $this->_file;
  }

  public function getOriginalFileName() {
    return $this->_originalFileName;
  }

  public function isSupported(array $fileProperties = []) {
    return TRUE;
  }

  public function getIterator() {
    return new ArrayIterator($this->getProperties());
  }

  protected function fetchProperties() {
    return [
      'filesize' => filesize($this->_file)
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