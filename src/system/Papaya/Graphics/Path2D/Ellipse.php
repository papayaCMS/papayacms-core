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

  class Ellipse implements Segment {

    private $_centerX;
    private $_centerY;
    private $_radiusX;
    private $_radiusY;

    /**
     * @param int $centerX
     * @param int $centerY
     * @param int $radiusX
     * @param int $radiusY
     */
    public function __construct($centerX, $centerY, $radiusX, $radiusY) {
      $this->_centerX = $centerX;
      $this->_centerY = $centerY;
      $this->_radiusX = $radiusX;
      $this->_radiusY = $radiusY;
    }

    public function getCenterX() {
      return $this->_centerX;
    }
    public function getCenterY() {
      return $this->_centerY;
    }
    public function getRadiusX() {
      return $this->_radiusX;
    }
    public function getRadiusY() {
      return $this->_radiusY;
    }
  }
}
