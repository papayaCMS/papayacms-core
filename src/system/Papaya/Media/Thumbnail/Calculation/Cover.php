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

  class Cover implements Calculation {
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
      $targetSize = $this->getTargetSize();
      return Calculation::MODE_FIX.'_'.$targetSize[0].'x'.$targetSize[1];
    }

    private function getThumbnailSize() {
      $factorX = $this->_width / $this->_minimumWidth;
      $factorY = $this->_height / $this->_minimumHeight;
      $thumbnailWidth = $this->_minimumWidth;
      $thumbnailHeight = $this->_minimumHeight;
      if ($factorX <= $factorY) {
        $thumbnailHeight = (int)round($this->_height / $factorX);
      } else {
        $thumbnailWidth = (int)round($this->_width / $factorY);
      }
      return [$thumbnailWidth, $thumbnailHeight];
    }

    public function getTargetSize() {
      return $this->getThumbnailSize();
    }

    public function getSource() {
      return new BoundingRectangle(0, 0, $this->_width, $this->_height);
    }

    public function getDestination() {
      list($thumbnailWidth, $thumbnailHeight) = $this->getThumbnailSize();
      return new BoundingRectangle(0, 0, $thumbnailWidth, $thumbnailHeight);
    }
  }
}
