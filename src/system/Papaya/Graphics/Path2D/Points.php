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


namespace Papaya\Graphics\Path2D {

  abstract class Points implements Segment, \ArrayAccess {

    /**
     * @var string
     */
    private $_type;

    /**
     * @var array
     */
    private $_points;

    /**
     * @param string $type
     * @param array ...$points
     */
    public function __construct($type, ...$points) {
      $this->_type = $type;
      $this->_points = $points;
    }

    public function __toString() {
      $result = $this->_type;
      foreach ($this->_points as $point) {
        $result .= ' '.$point[0].' '.$point[1];
      }
      return $result;
    }

    public function offsetExists($offset): bool {
      return isset($this->_points[$offset]);
    }

    public function offsetGet($offset) {
      return $this->_points[$offset];
    }

    public function offsetSet($offset, $value) {
      throw new \BadMethodCallException(sprintf('%s are immutable', __CLASS__));
    }

    public function offsetUnset($offset) {
      throw new \BadMethodCallException(sprintf('%s are immutable', __CLASS__));
    }
  }
}
