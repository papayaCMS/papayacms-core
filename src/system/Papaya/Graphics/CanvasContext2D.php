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

  use Papaya\Graphics\Canvas\ImageData;


  /**
   * 2D canvas context interface
   *
   * @property array|string $strokeColor
   * @property array|string $fillColor
   */
  interface CanvasContext2D {

    /* pixel manipulation */

    /**
     * @param $width
     * @param $height
     * @return ImageData
     */
    public function createImageData($width, $height);

    /**
     * @return ImageData
     */
    public function getImageData();

    /**
     * @param ImageData $imageData
     * @param int $dx
     * @param int $dy
     * @param int|int $dirtyX
     * @param int|int $dirtyY
     * @param int|null $dirtyWidth
     * @param int|null $dirtyHeight
     * @return void
     */
    public function putImageData(
      ImageData $imageData,
      $dx, $dy,
      $dirtyX = 0, $dirtyY = 0, $dirtyWidth = NULL, $dirtyHeight = NULL
    );

    /* translate & transform */

    /**
     * @param int $x
     * @param int $y
     * @return $this
     */
    public function translate($x, $y);

    /**
     * @param float $hScale
     * @param float $hSkew
     * @param float $vSkew
     * @param float $vScale
     * @param int $hMove
     * @param int $vMove
     * @return $this
     */
    public function transform($hScale, $hSkew, $vSkew, $vScale, $hMove, $vMove);

    /**
     * @param float $hScale
     * @param float $hSkew
     * @param float $vSkew
     * @param float $vScale
     * @param int $hMove
     * @param int $vMove
     * @return $this
     */
    public function setTransform($hScale, $hSkew, $vSkew, $vScale, $hMove, $vMove);

    /**
     * @return mixed
     */
    public function resetTransform();

    /**
     * @param $image
     * @param int|null $x
     * @param int|null $y
     * @param int|null $width
     * @param int|null $height
     * @param int|null $dx
     * @param int|null $dy
     * @param int|null $dWidth
     * @param int|null $dHeight
     * @return void
     */
    public function drawImage(
      $image,
      $x = NULL, $y = NULL, $width = NULL, $height = NULL,
      $dx = NULL, $dy = NULL, $dWidth = NULL, $dHeight = NULL
    );

    /* Rectangle methods */

    /**
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @return void
     */
    public function clearRect($x, $y, $width, $height);

    /**
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @return void
     */
    public function fillRect($x, $y, $width, $height);

    /**
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @return void
     */
    public function strokeRect($x, $y, $width, $height);

    /* path methods */

    /**
     * @return void
     */
    public function stroke();

    /**
     * @return void
     */
    public function fill();

    /**
     * @return void
     */
    public function beginPath();

    /**
     *
     */
    public function closePath();

    /**
     * @param int $x
     * @param int $y
     * @return void
     */
    public function moveTo($x, $y);

    /**
     * @param int $x
     * @param int $y
     * @return void
     */
    public function lineTo($x, $y);

    /**
     * @param int $centerX
     * @param int $centerY
     * @param int $radiusX
     * @param int $radiusY
     * @return void
     */
    public function ellipse($centerX, $centerY, $radiusX, $radiusY);

    /**
     * @param int $x
     * @param int $y
     * @param int $width
     * @param int $height
     * @return void
     */
    public function rect($x, $y, $width, $height);

  }
}
