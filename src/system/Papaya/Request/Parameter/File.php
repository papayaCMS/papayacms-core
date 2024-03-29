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
namespace Papaya\Request\Parameter;

use Papaya\File\System as FileSystem;
use Papaya\Request;
use Papaya\Utility;

/**
 * Encapsulate an uploaded file.
 *
 * @package Papaya-Library
 * @subpackage Request
 */
class File implements \ArrayAccess, \IteratorAggregate {
  private $_values = [
    'temporary' => NULL,
    'name' => '',
    'type' => 'application/octet-stream',
    'size' => 0,
    'error' => 0
  ];

  /**
   * @var Request\Parameters\Name
   */
  private $_name;

  private $_loaded = FALSE;

  private $_fileSystem;

  /**
   * Create file object, provide name and group
   *
   * @param string|Request\Parameters\Name $name
   * @param string $group
   */
  public function __construct($name, $group = NULL) {
    $this->_name = $name instanceof Request\Parameters\Name ? $name : new Request\Parameters\Name($name);
    if (NULL !== $group && '' !== \trim($group)) {
      $this->_name->prepend($group);
    }
  }

  /**
   * @return Request\Parameters\Name
   */
  public function getName() {
    return $this->_name;
  }

  /**
   * Return file path to the uploaded file
   *
   * @return string
   */
  public function __toString() {
    $this->lazyFetch();
    return (string)$this['temporary'];
  }

  /**
   * Return TRUE if here is an temporary uploaded file
   *
   * @return bool
   */
  public function isValid() {
    $this->lazyFetch();
    return !empty($this['temporary']);
  }

  /**
   * Get the file parameter data as an Iterator
   *
   * @see \IteratorAggregate::getIterator()
   *
   * @return \Iterator
   */
  public function getIterator(): \Traversable {
    $this->lazyFetch();
    return new \ArrayIterator($this->_values);
  }

  /**
   * @see \ArrayAccess::offsetExists()
   * @param string $offset
   * @return bool
   */
  public function offsetExists($offset): bool {
    if ('temporary' === $offset) {
      $this->lazyFetch();
      return isset($this->_values['temporary']);
    }
    return \array_key_exists($offset, $this->_values);
  }

  /**
   * @see \ArrayAccess::offsetGet()
   * @param string $offset
   * @return mixed
   */
  #[\ReturnTypeWillChange]
  public function offsetGet($offset) {
    $this->lazyFetch();
    return $this->_values[$offset];
  }

  /**
   * Block changes trough array syntax
   *
   * @see \ArrayAccess::offsetSet()
   * @param string $offset
   * @param mixed $value
   */
  public function offsetSet($offset, $value): void {
    $this->lazyFetch();
    throw new \LogicException('Values are loaded from $_FILES.');
  }

  /**
   * Block changes trough array syntax
   *
   * @see \ArrayAccess::offsetSet()
   * @param string $offset
   */
  public function offsetUnset($offset): void {
    $this->lazyFetch();
    throw new \LogicException('Values are loaded from $_FILES.');
  }

  /**
   * Fetch the file data from $_FILES
   */
  private function lazyFetch() {
    if (!$this->_loaded && \count($this->getName())) {
      $temporaryFile = $this->fetchValue('tmp_name');
      if (!empty($temporaryFile) &&
        $this->fileSystem()->getFile($temporaryFile)->isUploadedFile()) {
        $this->_values['temporary'] = $temporaryFile;
        $this->_values['name'] = $this->fetchValue('name', $this->_values['name']);
        $this->_values['type'] = $this->fetchValue('type', $this->_values['type']);
        $this->_values['size'] = $this->fetchValue('size', $this->_values['size']);
      }
      $this->_values['error'] = $this->fetchValue('error', 0);
    }
  }

  /**
   * Fetch a specific file value from $_FILES
   *
   * @param string $key
   * @param mixed $default
   * @return mixed
   */
  private function fetchValue($key, $default = NULL) {
    $name = clone $this->getName();
    $name->insertBefore(1, $key);
    return Utility\Arrays::getRecursive($_FILES, \iterator_to_array($name, FALSE), $default);
  }

  /**
   * Getter/Setter for the file system factory
   *
   * @param FileSystem\Factory $fileSystem
   *
   * @return FileSystem\Factory
   */
  public function fileSystem(FileSystem\Factory $fileSystem = NULL) {
    if (NULL !== $fileSystem) {
      $this->_fileSystem = $fileSystem;
    } elseif (NULL === $this->_fileSystem) {
      $this->_fileSystem = new FileSystem\Factory();
    }
    return $this->_fileSystem;
  }
}
