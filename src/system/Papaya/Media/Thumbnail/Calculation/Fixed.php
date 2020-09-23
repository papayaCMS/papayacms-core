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

  class Fixed implements Calculation {
    private $_width;

    private $_height;

    private $_targetWidth;

    private $_targetHeight;

    public function __construct($width, $height, $targetWidth, $targetHeight) {
      $this->_width = (int)$width;
      $this->_height = (int)$height;
      $this->_targetWidth = (int)$targetWidth;
      $this->_targetHeight = (int)$targetHeight;
    }

    public function getTargetSize() {
      return [$this->_targetWidth, $this->_targetHeight];
    }

    public function getSource() {
      return new BoundingRectangle(0, 0, $this->_width, $this->_height);
    }

    public function getDestination() {
      return new BoundingRectangle(0, 0, $this->_targetWidth, $this->_targetHeight);
    }
  }
}
