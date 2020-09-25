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

  use Papaya\Graphics\Color;
  use Papaya\Graphics\ImageTypes;

  if (!defined('IMG_WEBP')) {
    define('IMG_WEBP', 0);
  }
  if (!defined('IMG_BMP')) {
    define('IMG_BMP', 64);
  }
  if (!defined('IMAGETYPE_WEBP')) {
    define('IMAGETYPE_WEBP', 18);
  }

  class GDLibrary {

    private static $_imageTypes = [
      IMAGETYPE_BMP => [IMG_BMP, 'imagecreatefrombmp', 'imagebmp'],
      ImageTypes::MIMETYPE_BMP => [IMG_BMP, 'imagecreatefrombmp', 'imagebmp'],
      IMAGETYPE_GIF => [IMG_GIF, 'imagecreatefromgif', 'imagegif'],
      ImageTypes::MIMETYPE_GIF => [IMG_GIF, 'imagecreatefromgif', 'imagegif'],
      IMAGETYPE_JPEG => [IMG_JPG, 'imagecreatefromjpeg', 'imagejpeg'],
      ImageTypes::MIMETYPE_JPEG => [IMG_JPG, 'imagecreatefromjpeg', 'imagejpeg'],
      IMAGETYPE_PNG => [IMG_PNG, 'imagecreatefrompng', 'imagepng'],
      ImageTypes::MIMETYPE_PNG => [IMG_PNG, 'imagecreatefrompng', 'imagepng'],
      IMAGETYPE_WBMP => [IMG_WBMP, 'imagecreatefromwbmp', 'imagewbmp'],
      ImageTypes::MIMETYPE_WBMP => [IMG_WBMP, 'imagecreatefromwbmp', 'imagewbmp'],
      IMAGETYPE_WEBP => [IMG_WEBP, 'imagecreatefromwebp', 'imagewebp'],
      ImageTypes::MIMETYPE_WEBP => [IMG_WEBP, 'imagecreatefromwebp', 'imagewebp'],
    ];

    public function create($width, $height, Color $backgroundColor = NULL) {
      if (NULL === $backgroundColor) {
        $backgroundColor = Color::createGray(255, 0);
      }
      $imageResource = imagecreatetruecolor($width, $height);
      imagesavealpha($imageResource, TRUE);
      imagealphablending($imageResource, FALSE);
      $colorIndex = imagecolorallocatealpha(
        $imageResource,
        $backgroundColor->red,
        $backgroundColor->green,
        $backgroundColor->blue,
        127 - round($backgroundColor->alpha / 255 * 127)
      );
      imagefilledrectangle(
        $imageResource, 0,0, $width, $height, $colorIndex
      );
      imagealphablending($imageResource, TRUE);
      return new GDImage($imageResource, $this);
    }

    /**
     * @param string $fileName
     * @return GDImage $fileName
     */
    public function load($fileName) {
      $imageType = $this->identifyType($fileName);
      $source = NULL;
      if ($this->canLoad($imageType)) {
        $definition = isset(self::$_imageTypes[$imageType]) ? self::$_imageTypes[$imageType] : NULL;
        $source = $definition[1]($fileName);
      }
      if ($source) {
        return new GDImage($source, $this);
      }
      return NULL;
    }

    /**
     * @param $fileName
     * @return int
     */
    public function identifyType($fileName) {
      list(, , $type) = getimagesize($fileName);
      return $type;
    }

    public function getImageSize($fileName) {
      list($width, $height) = getimagesize($fileName);
      return [$width, $height];
    }

    /**
     * @param int|string $type Image type constant or mimetype string
     * @return bool
     */
    public function canLoad($type) {
      if ($definition = $this->getDefinition($type)) {
        if( $definition[0]) {
          return (imagetypes() & $definition[0]) && is_callable($definition[1]);
        }
        return is_callable($definition[1]);
      }
      return FALSE;
    }

    /**
     * @param int|string $type Image type constant or mimetype string
     * @return bool
     */
    public function canSave($type) {
      if ($definition = $this->getDefinition($type)) {
        if( $definition[0]) {
          return (imagetypes() & $definition[0]) && is_callable($definition[2]);
        }
        return is_callable($definition[2]);
      }
      return FALSE;
    }

    private function getDefinition($type) {
      if (is_string($type)) {
        $type = strtolower($type);
      }
      return isset(self::$_imageTypes[$type]) ? self::$_imageTypes[$type] : NULL;
    }
  }
}
