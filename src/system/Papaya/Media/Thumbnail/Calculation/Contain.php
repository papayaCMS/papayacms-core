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

  class Contain implements Calculation {
    private $_width;

    private $_height;

    private $_maximumWidth;

    private $_maximumHeight;

    /**
     * @var bool
     */
    private $_addPadding;

    public function __construct($width, $height, $maximumWidth, $maximumHeight, $pad = FALSE) {
      $this->_width = (int)$width;
      $this->_height = (int)$height;
      $this->_maximumWidth = (int)$maximumWidth;
      $this->_maximumHeight = (int)$maximumHeight;
      $this->_addPadding = (bool)$pad;
    }

    private function getThumbnailSize() {
      $factorX = $this->_width / $this->_maximumWidth;
      $factorY = $this->_height / $this->_maximumHeight;
      $thumbnailWidth = $this->_maximumWidth;
      $thumbnailHeight = $this->_maximumHeight;
      if ($factorX >= $factorY) {
        $thumbnailHeight = (int)round($this->_height / $factorX);
      } else {
        $thumbnailWidth = (int)round($this->_width / $factorY);
      }
      return [$thumbnailWidth, $thumbnailHeight];
    }

    public function getTargetSize() {
      if ($this->_addPadding) {
        return [$this->_maximumWidth, $this->_maximumHeight];
      }
      return $this->getThumbnailSize();
    }

    public function getSource() {
      return new BoundingRectangle(0, 0, $this->_width, $this->_height);
    }

    public function getDestination() {
      list($thumbnailWidth, $thumbnailHeight) = $this->getThumbnailSize();
      if ($this->_addPadding) {
        return new BoundingRectangle(
          (int)round(($this->_maximumWidth - $thumbnailWidth) / 2),
          (int)round(($this->_maximumHeight - $thumbnailHeight) / 2),
          $thumbnailWidth,
          $thumbnailHeight
        );
      }
      return new BoundingRectangle(0, 0, $thumbnailWidth, $thumbnailHeight);
    }
  }
}
