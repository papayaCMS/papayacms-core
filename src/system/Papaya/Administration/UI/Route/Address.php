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

  /**
   * Parse URL into a route address
   *
   * Everything after the $basePath up to the first ? or # will be split
   * by / and .
   *
   *   'administration.settings' -> ['administration', 'settings']
   *   'css/main' -> ['css', 'main']
   *
   * @package Papaya\Administration\UI\Route
   */
  class Address implements \IteratorAggregate, \Countable, \ArrayAccess {
    /**
     * @var \Papaya\URL\Current
     */
    private $_url;

    /**
     * @var null|array
     */
    private $_parts;

    /**
     * @var string
     */
    private $_basePath;

    /**
     * Address constructor.
     *
     * @param string $basePath
     * @param \Papaya\URL|null $url
     */
    public function __construct($basePath, \Papaya\URL $url = NULL) {
      $this->_basePath = $basePath;
      if (NULL === $url) {
        $this->_url = new \Papaya\URL\Current();
      }
    }

    /**
     * Returns the route path as an traversable list
     *
     * @return \ArrayIterator|\Traversable
     */
    public function getIterator() {
      return new \ArrayIterator($this->getParts());
    }

    /**
     * @return int
     */
    public function count() {
      return \count($this->getParts());
    }

    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset) {
      return \array_key_exists($offset, $this->getParts());
    }

    /**
     * @param int $offset
     * @return string
     */
    public function offsetGet($offset) {
      return $this->offsetExists($offset) ? $this->getParts()[$offset] : NULL;
    }

    /**
     * @param int $offset
     * @param string $value
     */
    public function offsetSet($offset, $value) {
      throw new \LogicException(\sprintf('%s is immutable', static::class));
    }

    /**
     * @param int $offset
     */
    public function offsetUnset($offset) {
      throw new \LogicException(\sprintf('%s is immutable', static::class));
    }

    /**
     * Lazy parsing for the route path
     *
     * @return array|null
     */
    private function getParts() {
      if (NULL === $this->_parts) {
        $pattern = '('.\preg_quote($this->_basePath, '(').'/(?<path>[^?#]*))';
        if (\preg_match($pattern, $this->_url->path, $matches)) {
          $this->_parts = \preg_split('([/.])', $matches['path']) ?: [];
        } else {
          $this->_parts = [];
        }
      }
      return $this->_parts;
    }

    /**
     * Return the route as an . separated string
     *
     * @param int $level - level depth starting with 0
     * @param int $offset - offset starting with 0
     * @return string
     */
    public function getRoute($level, $offset = 0) {
      return \implode('.', \array_slice($this->getParts(), $offset, $level + 1));
    }
  }
}
