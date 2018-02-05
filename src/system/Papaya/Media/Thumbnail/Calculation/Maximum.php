<?php

class PapayaMediaThumbnailCalculationMaximum {

  private $_width;
  private $_height;
  private $_maximumWidth;
  private $_maximumHeight;

  public function __construct($width, $height, $maximumWidth, $maximumHeight) {
    $this->_width = $width;
    $this->_height = $height;
    $this->_maximumWidth = $width;
    $this->_maximumHeight = $height;
  }

  public function getTargetSize() {
    $factorX = $this->_width / $this->_maximumWidth;
    $factorY = $this->_height / $this->_maximumHeight;
    $targetWidth = $this->_maximumWidth;
    $targetHeight = $this->_maximumHeight;
    if ($factorX >= $factorY) {
      $targetHeight = round($this->_height / $factorX);
    } else {
      $targetWidth = round($this->_width / $factorY);
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
      'x' => 0, 'y' => 0, 'width' =>  $targetWidth, 'height' => $targetHeight
    ];
  }
}