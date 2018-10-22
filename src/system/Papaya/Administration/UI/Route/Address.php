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
namespace Papaya\Administration\UI\Route {

  class Address implements \IteratorAggregate, \Countable, \ArrayAccess {
    /**
     * @var \Papaya\URL\Current
     */
    private $_url;
    /**
     * @var null|array
     */
    private $_path;

    public function __construct(\Papaya\URL $url = NULL) {
      if (NULL === $url) {
        $this->_url = new \Papaya\URL\Current();
      }
    }

    public function getIterator() {
      return new \ArrayIterator($this->getPath());
    }

    private function getPath() {
      if (NULL === $this->_path) {
        if (\preg_match('([^?#]*/(?<path>[^?#/]*))', $this->_url->path, $matches)) {
          $this->_path = \explode('.', $matches['path']) ?: [];
        } else {
          $this->_path = [];
        }
      }
      return $this->_path;
    }

    public function getRoute($level) {
      return implode('.', array_slice($this->getPath(), 0, $level + 1));
    }

    public function count() {
      return count($this->getPath());
    }

    public function offsetExists($offset) {
      return array_key_exists($offset, $this->getPath());
    }

    public function offsetGet($offset) {
      return $this->offsetExists($offset) ? $this->getPath()[$offset] : NULL;
    }

    public function offsetSet($offset, $value) {
      throw new \LogicException(sprintf('%s is immutable', static::class));
    }

    public function offsetUnset($offset) {
      throw new \LogicException(sprintf('%s is immutable', static::class));
    }
  }
}
