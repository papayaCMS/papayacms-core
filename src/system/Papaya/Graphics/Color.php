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

  use Papaya\Utility\Arrays as ArraysUtility;
  use Papaya\Utility\Random;

  /**
   * @property int $red
   * @property int $green
   * @property int $blue
   * @property float $alpha
   * @property-read float $hue
   * @property-read float $saturation
   * @property-read float $lightness
   */
  class Color implements \ArrayAccess {

    const FLOAT_DELTA = 0.000001;

    private $_rgba = [
      'red' => 0,
      'green' => 0,
      'blue' => 0,
      'alpha' => 1.0
    ];

    private $_hsl;

    /**
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param float $alpha
     * @throws \LogicException
     */
    public function __construct($red, $green, $blue, $alpha = 1.0) {
      $this->setValue('red', (int)$red);
      $this->setValue('green', (int)$green);
      $this->setValue('blue', (int)$blue);
      $this->setValue('alpha', (float)$alpha);
    }

    /**
     * @return string
     */
    public function __toString() {
      $hsl = $this->toHSL();
      return sprintf(
        'rgba(%d, %d, %d, %d), hsl(%01.2f, %01.2f, %01.2f)',
        $this->_rgba['red'],
        $this->_rgba['green'],
        $this->_rgba['blue'],
        number_format($this->_rgba['alpha'], 2),
        $hsl['hue'],
        $hsl['saturation'],
        $hsl['lightness']
      );
    }

    /**
     * @param bool $withAlpha
     * @return string
     */
    public function toHexString($withAlpha = FALSE) {
      if ($withAlpha) {
        return sprintf(
          '#%02x%02x%02x%02x',
          $this->_rgba['red'],
          $this->_rgba['green'],
          $this->_rgba['blue'],
          round($this->_rgba['alpha'] * 255)
        );
      }
      $result = sprintf(
        '#%02x%02x%02x',
        $this->_rgba['red'],
        $this->_rgba['green'],
        $this->_rgba['blue']
      );
      if (preg_match('(^#(([A-Fa-f\\d])\\g{-1}){3}$)', $result)) {
        return $result[0].$result[1].$result[3].$result[5];
      }
      return $result;
    }

    /**
     * @return int
     */
    public function toInt() {
      return
        ($this->_rgba['red'] << 24) +
        ($this->_rgba['green'] << 16) +
        ($this->_rgba['blue'] << 8) +
        round($this->_rgba['alpha'] * 255);
    }

    /**
     * @return array
     */
    public function toHSL() {
      if (NULL === $this->_hsl) {
        $this->_hsl = self::convertRGBToHSL(
          $this->_rgba['red'],
          $this->_rgba['green'],
          $this->_rgba['blue']
        );
      }
      return $this->_hsl;
    }

    public function removeAlpha(self $backgroundColor = NULL) {
      $this->_rgba = self::removeAlphaFromColor($this, $backgroundColor);
      return $this;
    }

    /**
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param int $alpha
     * @return self
     * @throws \LogicException
     */
    public static function create($red, $green, $blue, $alpha = 1.0) {
      return new self($red, $green, $blue, $alpha);
    }

    /**
     * All color parts of the color (rgb) will get the same value
     *
     * @param int $value
     * @param int $alpha
     * @return self
     * @throws \LogicException
     */
    public static function createGray($value, $alpha = 1.0) {
      return new self($value, $value, $value, $alpha);
    }

    /**
     * @param array $values
     * @return self
     * @throws \LogicException
     */
    public static function createFromArray(array $values) {
      return new self(
        ArraysUtility::get($values, ['red', 'r', 0], 0),
        ArraysUtility::get($values, ['green', 'g', 1], 0),
        ArraysUtility::get($values, ['blue', 'b', 2], 0),
        ArraysUtility::get($values, ['alpha', 'a', 3], 1.0)
      );
    }

    /**
     * Create a random color - the transparency can be specified
     *
     * @param int|NULL $alpha
     * @return self
     * @throws \Exception
     */
    public static function createRandom($alpha = NULL) {
      return new self(
        Random::randomInt(0, 255),
        Random::randomInt(0, 255),
        Random::randomInt(0, 255),
        isset($alpha) ? $alpha : Random::randomFloat()
      );
    }

    /**
     * Create color from (hex) string
     *
     * @param string $string
     * @return self
     */
    public static function createFromString($string) {
      $pattern = '(#(?:[a-fA-F\\d]{3,4}|(?:[a-fA-F\\d]{1,2}){3,4}))';
      if (preg_match($pattern, $string, $matches)) {
        $c = strlen($string);
        $step = $c < 5 ? 1 : 2;
        $parts = [];
        for ($i = 1; $i < $c; $i += $step) {
          $part = substr($string, $i, $step);
          if (strlen($part) === '') {
            $part = 'ff';
          } elseif (strlen($part) < 2) {
            $part .= $part;
          }
          if (count($parts) < 3) {
            $parts[] = hexdec($part);
          } else {
            $parts[] = hexdec($part) / 255;
          }
        }
        return new self(...$parts);
      }
      throw new \InvalidArgumentException(
        sprintf('Invalid color string: "%s"', $string)
      );
    }

    /**
     * Create a rbg color from HSL
     *
     * @param float $hue
     * @param float $saturation
     * @param float $lightness
     * @return self
     * @throws \LogicException
     */
    public static function createFromHSL($hue, $saturation, $lightness) {
      return self::createFromArray(self::convertHSLToRGB($hue, $saturation, $lightness));
    }

    /**
     * @param string $name
     * @return bool
     */
    private function hasValue($name) {
      switch ($name) {
      case '0':
      case 'r':
      case '1':
      case 'red':
      case 'g':
      case 'green':
      case '2':
      case 'b':
      case 'blue':
      case '3':
      case 'a':
      case 'alpha':
        return TRUE;
      }
      return FALSE;
    }

    /**
     * @param string $name
     * @param int|float $value
     * @throws \LogicException
     */
    private function setValue($name, $value) {
      switch ($name) {
      case '0':
      case 'r':
      case 'red':
        $this->validateValue($value, 0, 255);
        $this->_rgba['red'] = $value;
        $this->_hsl = NULL;
        return;
      case '1':
      case 'g':
      case 'green':
        $this->validateValue($value, 0, 255);
        $this->_rgba['green'] = $value;
        $this->_hsl = NULL;
        return;
      case '2':
      case 'b':
      case 'blue':
        $this->validateValue($value, 0, 255);
        $this->_rgba['blue'] = $value;
        $this->_hsl = NULL;
        return;
      case '3':
      case 'a':
      case 'alpha':
        $this->validateValue($value, 0, 1);
        $this->_rgba['alpha'] = $value;
        return;
      }
      throw new \LogicException('Invalid property name: '.$name);
    }

    /**
     * @param int|float $value
     * @param int|float $minimum
     * @param int|float $maximum
     * @throws \OutOfRangeException
     */
    private function validateValue($value, $minimum, $maximum) {
      if ($value < $minimum || $value > $maximum) {
        throw new \OutOfRangeException("Value needs to be between $minimum and $maximum.");
      }
    }

    /**
     * @param string $name
     * @return int|float
     * @throws \LogicException
     */
    private function getValue($name) {
      switch ($name) {
      case '0':
      case 'r':
      case 'red':
        return $this->_rgba['red'];
      case '1':
      case 'g':
      case 'green':
        return $this->_rgba['green'];
      case '2':
      case 'b':
      case 'blue':
        return $this->_rgba['blue'];
      case '3':
      case 'a':
      case 'alpha':
        return $this->_rgba['alpha'];
      case 'h':
      case 'hue':
        return $this->toHSL()['hue'];
      case 's':
      case 'saturation':
        return $this->toHSL()['saturation'];
      case 'l':
      case 'lightness':
        return $this->toHSL()['lightness'];
      }
      throw new \LogicException('Invalid property name: '.$name);
    }

    /**
     * @param string|int $offset
     * @return bool
     */
    public function offsetExists($offset): bool {
      return $this->hasValue((string)$offset);
    }

    /**
     * @param string|int $offset
     * @return int|float
     */
    public function offsetGet($offset): mixed {
      return $this->getValue((string)$offset);
    }

    /**
     * @param string|int $offset
     * @param int|float $value
     * @throws \LogicException
     */
    public function offsetSet($offset, $value): void {
      $this->setValue((string)$offset, $value);
    }

    /**
     * @param string|int $offset
     * @throws \LogicException
     */
    public function offsetUnset($offset): void {
      throw new \LogicException('Can not unset color parts.');
    }

    /**
     * @param string $offset
     * @return bool
     */
    public function __isset($offset) {
      return $this->hasValue($offset);
    }

    /**
     * @param string $offset
     * @return int|float
     * @throws \LogicException
     */
    public function __get($offset) {
      return $this->getValue($offset);
    }

    /**
     * @param string $offset
     * @param int|float $value
     * @throws \LogicException
     */
    public function __set($offset, $value) {
      $this->setValue($offset, $value);
    }

    /**
     * @param $offset
     * @throws \LogicException
     */
    public function __unset($offset) {
      throw new \LogicException('Can not unset color parts.');
    }

    /**
     * @param int $red
     * @param int $green
     * @param int $blue
     * @return array
     */
    public static function convertRGBToHSL($red, $green, $blue) {
      $red /= 255;
      $green /= 255;
      $blue /= 255;
      $minimum = \min([$red, $green, $blue]);
      $maximum = \max([$red, $green, $blue]);
      $lightness = ($maximum + $minimum) / 2;
      $hue = 0;
      $saturation = 0;
      if ($maximum - $minimum > self::FLOAT_DELTA) {
        $d = $maximum + $minimum;
        $saturation = ($lightness > 0.5) ? $d / (2 - $maximum - $minimum) : $d / ($maximum + $minimum);
        if ($maximum - $red < 0.000001) {
          $hue = ($green - $blue) / $d + ($green < $blue ? 6 : 0);
        } elseif ($maximum - $green < 0.000001) {
          $hue = ($blue - $red) / $d + 2;
        } else {
          $hue = ($red - $green) / $d + 4;
        }
        $hue /= 6;
      }
      return [
        'hue' => $hue, 'saturation' => $saturation, 'lightness' => $lightness
      ];
    }

    /**
     * @param float $hue
     * @param float $saturation
     * @param float $lightness
     * @return array
     */
    public static function convertHSLToRGB($hue, $saturation, $lightness) {
      if ($saturation < self::FLOAT_DELTA) {
        // achromatic
        $value = \round($lightness * 255);
        return [
          'red' => $value, 'green' => $value, 'blue' => $value
        ];
      }
      $m2 = ($lightness < 0.5)
        ? $lightness * (1 + $saturation)
        : $lightness + $saturation - ($lightness * $saturation);
      $m1 = 2 * $lightness - $m2;
      return [
        'red' => \round(self::convertHueToRGB($m1, $m2, $hue + 1 / 3)),
        'green' => \round(self::convertHueToRGB($m1, $m2, $hue)),
        'blue' => \round(self::convertHueToRGB($m1, $m2, $hue - (1 / 3)))
      ];
    }

    /**
     * @param float $m1
     * @param float $m2
     * @param float $hue
     * @return float
     */
    private static function convertHueToRGB($m1, $m2, $hue) {
      if ($hue < 0) {
        ++$hue;
      }
      if ($hue > 1) {
        --$hue;
      }
      if ($hue < 1 / 6) {
        return $m1 + ($m2 - $m1) * 6 * $hue;
      }
      if ($hue < 1 / 2) {
        return $m2;
      }
      if ($hue < 2 / 3) {
        return $m1 + ($m2 - $m1) * (2 / 3 - $hue) * 6;
      }
      return $m1;
    }

    /**
     * @param array|self $colorOne
     * @param array|self $colorTwo
     * @param NULL|array|self $backgroundColor
     * @return float
     */
    public static function computeDistance($colorOne, $colorTwo, $backgroundColor = NULL) {
      $colorOne = self::removeAlphaFromColor($colorOne, $backgroundColor);
      $colorTwo = self::removeAlphaFromColor($colorTwo, $backgroundColor);
      $difference = 0;
      $difference += ($colorOne['red'] - $colorTwo['red']) ** 2 * 2;
      $difference += ($colorOne['green'] - $colorTwo['green']) ** 2 * 4;
      $difference += ($colorOne['blue'] - $colorTwo['blue']) ** 2 * 3;
      return sqrt($difference) / (255 * 3);
    }

    /**
     * @param array|self $color
     * @param NULL|array|self $backgroundColor
     * @return array|self
     */
    public static function removeAlphaFromColor($color, $backgroundColor = NULL) {
      $backgroundColor = $backgroundColor ?: ['red' => 255, 'green' => 255, 'blue' => 255];
      $alpha = ArraysUtility::get($color, ['alpha', 4], 1.0);
      if ($alpha < 1.0) {
        $backgroundRed = ArraysUtility::get($backgroundColor, ['red', 0], 0);
        $backgroundGreen = ArraysUtility::get($backgroundColor, ['green', 1], 0);
        $backgroundBlue = ArraysUtility::get($backgroundColor, ['blue', 2], 0);
        $factor = (float)$alpha;
        $red = $backgroundRed * (1 - $factor) + $color['red'] * $factor;
        $green = $backgroundGreen * (1 - $factor) + $color['green'] * $factor;
        $blue = $backgroundBlue * (1 - $factor) + $color['blue'] * $factor;
        if ($color instanceof self) {
          return self::create($red, $green, $blue);
        }
        if (isset($color['red'])) {
          return ['red' => $red, 'green' => $green, 'blue' => $blue, 'alpha' => 1.0];
        }
        return [$red, $green, $blue, 1.0];
      }
      return $color;
    }
  }
}
