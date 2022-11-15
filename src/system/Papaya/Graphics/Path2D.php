<?php
/*
 * papaya CMS
 *
 * @copyright 2000-2020 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */


namespace Papaya\Graphics {

  class Path2D implements \IteratorAggregate, \Countable, \ArrayAccess {

    private $_segments = [];

    public function closePath() {
      if (\count($this->_segments) < 2) {
        return;
      }
      if (!($this->_segments[0] instanceof Path2D\Move)) {
        throw new \LogicException('Can not close path. First segment is not a move providing a starting point.');
      }
      $this->_segments[] = new Path2D\Line(...$this->_segments[0]->getTargetPoint());
    }

    /**
     * @param int $x
     * @param int $y
     */
    public function moveTo($x, $y) {
      $this->_segments[] = new Path2D\Move($x, $y);
    }

    /**
     * @param int $x
     * @param int $y
     */
    public function lineTo($x, $y) {
      $this->_segments[] = new Path2D\Line($x, $y);
    }

    /**
     * @param int $centerX
     * @param int $centerY
     * @param int $radiusX
     * @param int $radiusY
     */
    public function ellipse($centerX, $centerY, $radiusX, $radiusY) {
      $this->_segments[] = new Path2D\Ellipse($centerX, $centerY, $radiusX, $radiusY);
    }

    /**
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     */
    public function rect($x, $y, $width, $height) {
      $this->_segments[] = new Path2D\Rectangle($x, $y, $width, $height);
    }

    /** Interfaces */

    public function getIterator(): \Traversable {
      return new \ArrayIterator($this->_segments);
    }

    public function count(): int {
      return \count($this->_segments);
    }

    public function offsetExists($offset): bool {
      return isset($this->_segments[$offset]);
    }

    public function offsetGet($offset): Segment {
      return $this->_segments[$offset];
    }

    public function offsetSet($offset, $value): void {
      throw new \BadMethodCallException('Please use the specific methods to modify the path.');
    }

    public function offsetUnset($offset): void {
      throw new \BadMethodCallException('Please use the specific methods to modify the path.');
    }
  }
}
