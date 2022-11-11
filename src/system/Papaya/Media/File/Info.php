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
namespace Papaya\Media\File {

  use Papaya\Application;

  abstract class Info implements Application\Access, \ArrayAccess, \IteratorAggregate {
    use Application\Access\Aggregation;

    /**
     * @var string
     */
    private $_file;

    /**
     * @var array
     */
    private $_properties;

    /**
     * @var string
     */
    private $_originalFileName;

    /**
     * Info constructor.
     *
     * @param string $file
     * @param string $originalFileName
     */
    public function __construct($file, $originalFileName = '') {
      $this->_file = $file;
      $this->_originalFileName = $originalFileName;
    }

    /**
     * @return string
     */
    public function getFile() {
      return $this->_file;
    }

    /**
     * @return string
     */
    public function getOriginalFileName() {
      return $this->_originalFileName;
    }

    /**
     * @param array $fileProperties
     * @return bool
     */
    public function isSupported(
      /** @noinspection PhpUnusedParameterInspection */
      array $fileProperties = []
    ) {
      return TRUE;
    }

    /**
     * @return \Traversable
     */
    public function getIterator(): \Traversable {
      return new \ArrayIterator($this->getProperties());
    }

    /**
     * @return array
     */
    protected function fetchProperties() {
      return [
        'filesize' => \filesize($this->_file)
      ];
    }

    /**
     * @return array
     */
    private function getProperties() {
      if (NULL === $this->_properties) {
        $this->_properties = $this->fetchProperties();
      }
      return $this->_properties;
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetExists($offset): bool {
      return \array_key_exists($offset, $this->getProperties());
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function offsetGet($offset): mixed {
      return $this->getProperties()[$offset];
    }

    /**
     * @param string $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value): void {
      throw new \BadMethodCallException(\sprintf('Object %s is immutable.', static::class));
    }

    /**
     * @param string $offset
     */
    public function offsetUnset($offset): void {
      throw new \BadMethodCallException(\sprintf('Object %s is immutable.', static::class));
    }
  }
}
