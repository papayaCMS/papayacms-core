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
namespace Papaya\Graphics\GD {

  use Papaya\Graphics\ImageTypes;

  class GDImage {

    /**
     * @var GDLibrary
     */
    private $_gd;

    private $_resource;

    private $_contexts = [
      '2d' => NULL
    ];

    public function __construct($imageResource, GDLibrary $gd = NULL) {
      $this->_gd = $gd;
      $this->_resource = $imageResource;
    }

    public function __destruct() {
      imagedestroy($this->_resource);
    }

    public function getResource() {
      return $this->_resource;
    }

    public function getLibrary() {
      if (NULL === $this->_gd) {
        $this->_gd = new GDLibrary();
      }
      return $this->_gd;
    }

    public function getWidth() {
      return imagesx($this->_resource);
    }

    public function getHeight() {
      return imagesy($this->_resource);
    }

    public function save($imageType, $to = null, $quality = NULL, $compressed = FALSE) {
      if (isset($quality)) {
        $quality = (int)$quality;
        if (!($quality > 0 && $quality < 1)) {
          $quality = NULL;
        }
      }
      if ($this->getLibrary()->canSave($imageType)) {
        if (is_string($imageType)) {
          $imageType = strtolower($imageType);
        }
        switch ($imageType) {
        case IMAGETYPE_BMP:
        case ImageTypes::MIMETYPE_BMP:
          /** @noinspection PhpElementIsNotAvailableInCurrentPhpVersionInspection */
          return imagebmp($this->_resource, $to, $compressed);
        case IMAGETYPE_GIF:
        case ImageTypes::MIMETYPE_GIF:
          return imagegif($this->_resource, $to);
        case IMAGETYPE_JPEG:
        case ImageTypes::MIMETYPE_JPEG:
          if ($quality) {
            return imagejpeg($this->_resource, $to, round($quality * 100));
          }
          return imagejpeg($this->_resource, $to);
        case IMAGETYPE_PNG:
        case ImageTypes::MIMETYPE_PNG:
          if ($quality) {
            return imagepng($this->_resource, $to, round($quality * 9));
          }
          return imagepng($this->_resource, $to);
        case IMAGETYPE_WBMP:
        case ImageTypes::MIMETYPE_WBMP:
          return imagewbmp($this->_resource, $to);
        case IMAGETYPE_WEBP:
        case ImageTypes::MIMETYPE_WEBP:
          if ($quality) {
            return imagewebp($this->_resource, $to, round($quality * 100));
          }
          return imagewebp($this->_resource, $to);
        }
      }
      return FALSE;
    }

    public function filter(GDFilter ...$filters) {
      foreach ($filters as $filter) {
        $filter->applyTo($this->_resource);
      }
      $this->_contexts['2d'] = NULL;
    }

    public function getContext($type = '2d') {
      if ($type !== '2d') {
        return new \LogicException('Only 2d canvas context is supported at the moment.');
      }
      if (isset($this->_contexts[$type])) {
        return $this->_contexts[$type];
      }
      return $this->_contexts[$type] = new GDCanvasContext($this);
    }
  }
}

