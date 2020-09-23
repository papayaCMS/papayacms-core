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

  use Papaya\Graphics\Canvas\ImageData;
  use Papaya\Graphics\Path2D;
  use Papaya\Graphics\CanvasContext2D;
  use Papaya\Graphics\ImageTypes;
  use Papaya\Utility\Arrays;

  /**
   * Class GDContext
   *
   * @property int[] $fillColor
   * @property int[] $strokeColor
   */
  class GDCanvasContext implements CanvasContext2D {

    private $_image;

    /**
     * @var ImageData
     */
    private $_imageData;

    /**
     * @var Path2D
     */
    private $_currentPath;


    private $_properties = [
      'fillcolor' => [0, 0, 0, 0],
      'strokecolor' => [255, 255, 255, 255]
    ];

    const SCALE_HORIZONTAL = 'a';
    const SKEW_HORIZONTAL = 'b';
    const SKEW_VERTICAL = 'c';
    const SCALE_VERTICAL = 'd';
    const MOVE_HORIZONTAL = 'e';
    const MOVE_VERTICAL = 'f';
    private $_matrix = [
      self::SCALE_HORIZONTAL => 1,
      self::SKEW_HORIZONTAL => 0,
      self::SKEW_VERTICAL => 0,
      self::SCALE_VERTICAL => 1,
      self::MOVE_HORIZONTAL => 0,
      self::MOVE_VERTICAL => 0
    ];

    public function __construct(GDImage $image) {
      $this->_image = $image;
    }

    private function getResource() {
      return $this->_image->getResource();
    }

    public function __isset($name) {
      $name = \strtolower($name);
      return isset($this->_properties[$name]);
    }

    public function __get($name) {
      $name = \strtolower($name);
      if (\method_exists($this, 'get'.$name)) {
        return $this->{'get'.$name}();
      }
      return $this->_properties[$name];
    }

    public function __set($name, $value) {
      $name = \strtolower($name);
      if (\method_exists($this, 'set'.$name)) {
        $this->{'set'.$name}($value);
        return;
      }
      $this->_properties[$name] = $value;
    }

    public function __unset($name) {
      throw new \LogicException(
        \sprintf(
          'You can not unset properties on a %s',
          __CLASS__
        )
      );
    }

    public function setFillColor($color) {
      $this->_properties['fillcolor'] = [
        Arrays::get($color, ['red', 0], 0),
        Arrays::get($color, ['green', 1], 0),
        Arrays::get($color, ['blue', 2], 0),
        Arrays::get($color, ['alpha', 3], 255)
      ];
    }

    public function setStrokeColor($color) {
      $this->_properties['strokecolor'] = [
        Arrays::get($color, ['red', 0], 0),
        Arrays::get($color, ['green', 1], 0),
        Arrays::get($color, ['blue', 2], 0),
        Arrays::get($color, ['alpha', 3], 255)
      ];
    }

    /* ImageData */

    /**
     * @param int $width
     * @param int $height
     * @return ImageData
     */
    public function createImageData($width, $height) {
      return new ImageData(
        \array_fill(0, $width * $height * 4, 0),
        $width,
        $height
      );
    }

    /**
     * @return ImageData
     */
    public function getImageData() {
      if (NULL === $this->_imageData) {
        $cache = [];
        $data = [];
        $resource = $this->getResource();
        $width = \imagesx($resource);
        $height = \imagesy($resource);
        for ($y = 0; $y < $height; $y++) {
          for ($x = 0; $x < $width; $x++) {
            $rgba = \imagecolorat($resource, $x, $y);
            if (isset($cache[$rgba])) {
              $pixel = $cache[$rgba];
            } else {
              $cache[$rgba] = $pixel = [
                ($rgba >> 16) & 0xFF,
                ($rgba >> 8) & 0xFF,
                $rgba & 0xFF,
                (127 - (($rgba & 0x7F000000) >> 24)) / 127 * 255
              ];
            }
            $data[] = $pixel[0];
            $data[] = $pixel[1];
            $data[] = $pixel[2];
            $data[] = $pixel[3];
          }
        }
        $this->_imageData = new ImageData($data, $width, $height);
      }
      return $this->_imageData;
    }

    /**
     * @param ImageData $imageData
     * @param int $dx
     * @param int $dy
     * @param int $dirtyX
     * @param int $dirtyY
     * @param int|null $dirtyWidth
     * @param int|null $dirtyHeight
     */
    public function putImageData(
      ImageData $imageData,
      $dx, $dy,
      $dirtyX = 0, $dirtyY = 0, $dirtyWidth = NULL, $dirtyHeight = NULL
    ) {
      $resource = $this->getResource();
      $data = $imageData->data;
      $height = $imageData->height;
      $width = $imageData->width;
      $dirtyX = (int)$dirtyX;
      $dirtyY = (int)$dirtyY;
      $dirtyWidth = $dirtyWidth !== NULL ? (int)$dirtyWidth : $width;
      $dirtyHeight = $dirtyHeight !== NULL ? (int)$dirtyHeight : $height;
      $limitBottom = \min($dirtyHeight, $height);
      $limitRight = \min($dirtyWidth, $width);
      for ($y = $dirtyY; $y < $limitBottom; $y++) {
        for ($x = $dirtyX; $x < $limitRight; $x++) {
          $sourceOffset = $y * $width + $x;
          $rgba = [
            $data[$sourceOffset],
            $data[$sourceOffset + 1],
            $data[$sourceOffset + 2],
            $data[$sourceOffset + 3],
          ];
          if (NULL !== $this->_imageData) {
            $targetOffset = ($x + $dx) * ($y + $dy) * 4;
            $this->_imageData->data[$targetOffset] = $rgba[0];
            $this->_imageData->data[$targetOffset + 1] = $rgba[1];
            $this->_imageData->data[$targetOffset + 2] = $rgba[2];
            $this->_imageData->data[$targetOffset + 3] = $rgba[3];
          }
          imagesetpixel(
            $resource, $x + $dx, $y + $dy, $this->getColorIndex(...$rgba)
          );
        }
      }
    }

    private function getColorIndex($red, $green, $blue, $alpha) {
      $resource = $this->getResource();
      $gdAlpha = 127 - ($alpha / 255 * 127);
      $color = imagecolorallocatealpha(
        $resource, $red, $green, $blue, $gdAlpha
      );
      if (FALSE === $color) {
        return imagecolorresolvealpha(
          $resource, $red, $green, $blue, $gdAlpha
        );
      }
      return $color;
    }

    /* Coordinates translate and transform */

    public function transform($hScale, $hSkew, $vSkew, $vScale, $hMove, $vMove) {
      $m = $this->_matrix;
      $this->_matrix = [
        self::SCALE_HORIZONTAL =>
          $m[self::SCALE_HORIZONTAL] * $hScale + $m[self::SKEW_VERTICAL] * $hSkew,
        self::SKEW_HORIZONTAL =>
          $m[self::SKEW_HORIZONTAL] * $hScale + $m[self::SCALE_VERTICAL] * $hSkew,
        self::SKEW_VERTICAL =>
          $m[self::SKEW_VERTICAL] * $vScale + $m[self::SCALE_HORIZONTAL] * $vSkew,
        self::SCALE_VERTICAL =>
          $m[self::SCALE_VERTICAL] * $vScale + $m[self::SKEW_HORIZONTAL] * $vSkew,
        self::MOVE_HORIZONTAL =>
          $m[self::SCALE_HORIZONTAL] * $hMove + $m[self::SKEW_VERTICAL] * $vMove + $m[self::MOVE_HORIZONTAL],
        self::MOVE_VERTICAL =>
          $m[self::SCALE_VERTICAL] * $vMove + $m[self::SKEW_HORIZONTAL] * $hMove + $m[self::MOVE_VERTICAL],
      ];
      return $this;
    }

    public function setTransform($hScale, $hSkew, $vSkew, $vScale, $hMove, $vMove) {
      $this->_matrix = [
        self::SCALE_HORIZONTAL => $hScale,
        self::SKEW_HORIZONTAL => $hSkew,
        self::SKEW_VERTICAL => $vSkew,
        self::SCALE_VERTICAL => $vScale,
        self::MOVE_HORIZONTAL => $hMove,
        self::MOVE_VERTICAL => $vMove
      ];
      return $this;
    }

    public function resetTransform() {
      $this->setTransform(1, 0, 0, 1, 0, 0);
      return $this;
    }

    public function translate($tx, $ty) {
      $this->transform(1, 0, 0, 1, (int)$tx, (int)$ty);
      return $this;
    }

    private function applyToPoint($x, $y) {
      return [
        \round(
          $x * $this->_matrix[self::SCALE_HORIZONTAL] +
          $y * $this->_matrix[self::SKEW_VERTICAL] +
          $this->_matrix[self::MOVE_HORIZONTAL]
        ),
        \round(
          $y * $this->_matrix[self::SCALE_VERTICAL] +
          $x * $this->_matrix[self::SKEW_HORIZONTAL] +
          $this->_matrix[self::MOVE_VERTICAL]
        )
      ];
    }

    private function applyToPoints(...$points) {
      return \array_map(
        function ($point) {
          return $this->applyToPoint(...$point);
        },
        $points
      );
    }

    /* Draw image */

    /**
     * @param GDImage|resource $image
     * @param null $x
     * @param null $y
     * @param null $width
     * @param null $height
     * @param null $dx
     * @param null $dy
     * @param null $dWidth
     * @param null $dHeight
     * @noinspection NestedTernaryOperatorInspection
     */
    public function drawImage(
      $image,
      $x = NULL, $y = NULL, $width = NULL, $height = NULL,
      $dx = NULL, $dy = NULL, $dWidth = NULL, $dHeight = NULL
    ) {
      $source = ($image instanceof GDImage) ? $image->getResource() : $image;
      if (\is_resource($source) && \get_resource_type($source) === 'gd') {
        $destinationX = Arrays::firstNotNull([$dx, $x, 0]);
        $destinationY =  Arrays::firstNotNull([$dy, $y, 0]);
        $sourceX = (NULL !== $dx) ? ($x ?: 0) : 0;
        $sourceY = (NULL !== $dy) ? ($y ?: 0) : 0;
        $destinationWidth = Arrays::firstNotNull([$dWidth, $width, imagesx($image)]);
        $destinationHeight = Arrays::firstNotNull([$dHeight, $height, imagesy($image)]);
        $sourceWidth = (NULL !== $dWidth) ? ($width ?: imagesx($image)) : imagesx($image);
        $sourceHeight = (NULL !== $dHeight) ? ($height ?: imagesy($image)) : imagesy($image);

        list($destinationX, $destinationY) = $this->applyToPoint($destinationX, $destinationY);

        \imagecopyresampled(
          $this->getResource(),
          $source,
          $destinationX,
          $destinationY,
          $sourceX,
          $sourceY,
          $destinationWidth,
          $destinationHeight,
          $sourceWidth,
          $sourceHeight
        );
        $this->_imageData = NULL;
      }
    }


    /* Rectangle */

    public function clearRect($x, $y, $width, $height) {
      $resource = $this->getResource();
      list($topLeft, $bottomRight) = $this->applyToPoints([$x, $y], [$x + $width, $y + $height]);
      \imagealphablending($resource, FALSE);
      \imagefilledrectangle(
        $resource,
        $topLeft[0],
        $topLeft[1],
        $bottomRight[0],
        $bottomRight[1],
        $this->getColorIndex(0, 0, 0, 0)
      );
      \imagealphablending($resource, TRUE);
      $this->_imageData = NULL;
    }

    public function fillRect($x, $y, $width, $height) {
      $resource = $this->getResource();
      list($topLeft, $bottomRight) = $this->applyToPoints([$x, $y], [$x + $width, $y + $height]);
      \imagefilledrectangle(
        $resource,
        $topLeft[0],
        $topLeft[1],
        $bottomRight[0],
        $bottomRight[1],
        $this->getColorIndex(...$this->_properties['fillcolor'])
      );
      $this->_imageData = NULL;
    }

    public function strokeRect($x, $y, $width, $height) {
      $resource = $this->getResource();
      list($topLeft, $bottomRight) = $this->applyToPoints([$x, $y], [$x + $width, $y + $height]);
      \imagerectangle(
        $resource,
        $topLeft[0],
        $topLeft[1],
        $bottomRight[0],
        $bottomRight[1],
        $this->getColorIndex(...$this->_properties['strokecolor'])
      );
      $this->_imageData = NULL;
    }

    /* Paths */

    public function stroke() {
      $resource = $this->getResource();
      if ($points = $this->getPolygonPoints($this->getCurrentPath())) {
        $list = array_reduce(
          $points,
          function ($carry, $point) {
            list($x, $y) = $this->applyToPoint(...$point);
            $carry[] = $x;
            $carry[] = $y;
            return $carry;
          },
          []
        );
        \imagepolygon(
          $resource, $list, \count($points), $this->getColorIndex(...$this->_properties['strokecolor'])
        );
      } else {
        $colorIndex = $this->getColorIndex(...$this->_properties['strokecolor']);
        $position = [0, 0];
        foreach ($this->getCurrentPath() as $segment) {
          if ($segment instanceof Path2D\Move) {
            $position = $this->applyToPoint(...$segment->getTargetPoint());
          } elseif ($segment instanceof Path2D\Line) {
            $targetPosition = $this->applyToPoint(...$segment->getTargetPoint());
            \imageline(
              $resource, $position[0], $position[1], $targetPosition[0], $targetPosition[1], $colorIndex
            );
            $position = $targetPosition;
          }
        }
      }
      $this->_imageData = NULL;
    }

    public function fill() {
      $resource = $this->getResource();
      $path = $this->getCurrentPath();
      if (\count($path) < 1) {
        return;
      }
      if (\count($path) === 1) {
        $segment = $path[0];
        if ($segment instanceof Path2D\Ellipse) {
          $center = $this->applyToPoint($segment->getCenterX(), $segment->getCenterY());
          \imagefilledellipse(
            $resource,
            $center[0],
            $center[1],
            $segment->getRadiusX() * 2,
            $segment->getRadiusY() * 2,
            $this->getColorIndex(...$this->_properties['fillcolor'])
          );
          $this->_imageData = NULL;
          return;
        }
        if ($segment instanceof Path2D\Rectangle) {
          $this->fillRect($segment->getX(), $segment->getY(), $segment->getWidth(), $segment->getHeight());
          return;
        }
      } elseif ($points = $this->getPolygonPoints($path)) {
        $list = array_reduce(
          $points,
          function ($carry, $point) {
            list($x, $y) = $this->applyToPoint(...$point);
            $carry[] = $x;
            $carry[] = $y;
            return $carry;
          },
          []
        );
        \imagefilledpolygon(
          $resource,
          $list,
          \count($points) - 1,
          $this->getColorIndex(...$this->_properties['fillcolor'])
        );
        $this->_imageData = NULL;
        return;
      }
      throw new \LogicException('Unsupported path.');
    }

    private function getPolygonPoints(Path2D $path) {
      $points = [];
      foreach ($path as $index => $segment) {
        if (
          ($index === 0 && $segment instanceof Path2D\Move) ||
          ($index > 0 && $segment instanceof Path2D\Line)
        ) {
          $points[] = $segment[0];
        } else {
          return FALSE;
        }
      }
      $count = \count($points);
      if ($count > 3 && $points[0] === $points[$count - 1]) {
        return $points;
      }
      return FALSE;
    }

    public function beginPath() {
      $this->_currentPath = new Path2D();
    }

    public function closePath() {
      if (NULL !== $this->_currentPath) {
        $this->_currentPath->closePath();
      }
    }

    private function getCurrentPath() {
      if (NULL === $this->_currentPath) {
        $this->_currentPath = new Path2D();
      }
      return $this->_currentPath;
    }

    public function moveTo($x, $y) {
      $this->getCurrentPath()->moveTo($x, $y);
    }

    public function lineTo($x, $y) {
      $this->getCurrentPath()->lineTo($x, $y);
    }

    public function ellipse($centerX, $centerY, $radiusX, $radiusY) {
      $this->getCurrentPath()->ellipse($centerX, $centerY, $radiusX, $radiusY);
    }

    public function rect($x, $y, $width, $height) {
      $this->getCurrentPath()->rect($x, $y, $width, $height);
    }
  }
}
