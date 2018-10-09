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
namespace Papaya\Media\Thumbnail\Calculation;

class Maximum {
  private $_width;

  private $_height;

  private $_maximumWidth;

  private $_maximumHeight;

  public function __construct($width, $height, $maximumWidth, $maximumHeight) {
    $this->_width = $width;
    $this->_height = $height;
    $this->_maximumWidth = $maximumWidth;
    $this->_maximumHeight = $maximumHeight;
  }

  public function getTargetSize() {
    $factorX = $this->_width / $this->_maximumWidth;
    $factorY = $this->_height / $this->_maximumHeight;
    $targetWidth = $this->_maximumWidth;
    $targetHeight = $this->_maximumHeight;
    if ($factorX >= $factorY) {
      $targetHeight = \round($this->_height / $factorX);
    } else {
      $targetWidth = \round($this->_width / $factorY);
    }
    return [$targetWidth, $targetHeight];
  }

  public function getSource() {
    return [
      'x' => 0, 'y' => 0, 'width' => $this->_width, 'height' => $this->_height
    ];
  }

  public function getDestination() {
    list($targetWidth, $targetHeight) = $this->getTargetSize();
    return [
      'x' => 0, 'y' => 0, 'width' => $targetWidth, 'height' => $targetHeight
    ];
  }
}
