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
namespace Papaya\Router {

  /**
   * Parse URL into a route address
   *
   * Everything after the $basePath up to the first ? or # will be split
   * by / and .
   *
   *   'administration.settings' -> ['administration', 'settings']
   *   'css/main' -> ['css', 'main']
   *
   * @package Papaya\Router\Route
   */
  abstract class Path implements \IteratorAggregate, \Countable, \ArrayAccess {
    /**
     * Lazy parsing for the route path
     *
     * @param int $offset
     * @return array|null
     */
    abstract public function getRouteArray($offset = 0);

    /**
     * Lazy parsing for the route path
     *
     * @param $offset
     * @return string
     */
    abstract public function getSeparator($offset);

    /**
     * Returns the route path as an traversable list
     *
     * @return \ArrayIterator|\Traversable
     */
    public function getIterator(): \Traversable {
      return new \ArrayIterator($this->getRouteArray());
    }

    /**
     * @return int
     */
    public function count(): int {
      return \count($this->getRouteArray());
    }

    /**
     * @param int $offset
     * @return bool
     */
    public function offsetExists($offset): bool {
      return \array_key_exists($offset, $this->getRouteArray());
    }

    /**
     * @param int $offset
     * @return string
     */
    public function offsetGet($offset) {
      return $this->offsetExists($offset) ? $this->getRouteArray()[$offset] : NULL;
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
     * Return the route as a string
     *
     * @param int $level - level depth starting with 0
     * @param int $offset - offset starting with 0
     * @return string
     */
    public function getRouteString($level, $offset = 0) {
      $parts = $this->getRouteArray();
      $result = '';
      if ($level < 0) {
        $level = \count($parts);
      }
      for ($i = $offset, $c = \count($parts); $i < $c && $i <= $level; $i++) {
        if ($i > $offset) {
          $result .= $this->getSeparator($i - 1);
        }
        $result .= $parts[$i];
      }
      return $result;
    }
  }
}
