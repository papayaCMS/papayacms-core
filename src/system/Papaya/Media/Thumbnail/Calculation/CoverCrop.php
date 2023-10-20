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
namespace Papaya\Media\Thumbnail\Calculation {

  use Papaya\Graphics\BoundingRectangle;
  use Papaya\Media\Thumbnail\Calculation;

  class CoverCrop implements Calculation {
    private $_width;

    private $_height;

    private $_minimumWidth;

    private $_minimumHeight;

    public function __construct($width, $height, $minimumWidth, $minimumHeight) {
      $this->_width = (int)$width;
      $this->_height = (int)$height;
      $this->_minimumWidth = (int)$minimumWidth;
      $this->_minimumHeight = (int)$minimumHeight;
    }
    public function getIdentifier() {
      return Calculation::MODE_COVER_CROPPED.'_'.$this->_minimumWidth.'x'.$this->_minimumHeight;
    }

    public function getTargetSize() {
      return [$this->_minimumWidth, $this->_minimumHeight];
    }

    public function getSource() {
      $factorX = $this->_width / $this->_minimumWidth;
      $factorY = $this->_height / $this->_minimumHeight;
      $width = $this->_width;
      $height = $this->_height;
      $left = 0;
      $top = 0;
      if ($factorX <= $factorY) {
        $height = (int)round($this->_minimumHeight * $factorX);
        $top = floor(($this->_height - $height) / 2);
      } else {
        $width = (int)round($this->_minimumWidth * $factorY);
        $left = floor(($this->_width - $width) / 2);
      }
      return new BoundingRectangle($left, $top, $width, $height);
    }

    public function getDestination() {
      return new BoundingRectangle(0, 0, $this->_minimumWidth, $this->_minimumHeight);
    }
  }
}
