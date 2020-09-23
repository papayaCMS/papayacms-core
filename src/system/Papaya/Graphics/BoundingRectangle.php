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

  class BoundingRectangle {

    private $_left;
    private $_top;
    private $_width;
    private $_height;

    public function __construct($left, $top, $width, $height) {
      $this->_left = $left;
      $this->_top = $top;
      $this->_width = $width;
      $this->_height = $height;
    }

    public function getOffset() {
      return [$this->_left, $this->_top];
    }

    public function getSize() {
      return [$this->_width, $this->_height];
    }

    public function getLeft() {
      return $this->_left;
    }

    public function getTop() {
      return $this->_top;
    }

    public function getWidth() {
      return $this->_width;
    }

    public function getHeight() {
      return $this->_height;
    }
  }
}
