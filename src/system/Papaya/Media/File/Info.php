<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */
namespace Papaya\Media\File;

abstract class Info extends \Papaya\Application\BaseObject implements \ArrayAccess, \IteratorAggregate {
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
    return new \ArrayIterator($this->getProperties());
  }

  protected function fetchProperties() {
    return [
      'filesize' => \filesize($this->_file)
    ];
  }

  private function getProperties() {
    if (NULL === $this->_properties) {
      $this->_properties = $this->fetchProperties();
    }
    return $this->_properties;
  }

  public function offsetExists($offset) {
    return \array_key_exists($offset, $this->getProperties());
  }

  public function offsetGet($offset) {
    return $this->getProperties()[$offset];
  }

  public function offsetSet($offset, $value) {
    throw new \BadMethodCallException(\sprintf('Object %s is immutable.', static::class));
  }

  public function offsetUnset($offset) {
    throw new \BadMethodCallException(\sprintf('Object %s is immutable.', static::class));
  }
}
