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

/**
 * A filter factory to create filter objects for from data structures using profiles
 *
 * @package Papaya-Library
 * @subpackage Filter
 *
 * @method static bool isCssColor($value, $mandatory = TRUE)
 * @method static bool isCssSize($value, $mandatory = TRUE)
 * @method static bool isEmail($value, $mandatory = TRUE)
 * @method static bool isFileName($value, $mandatory = TRUE)
 * @method static bool isFilePath($value, $mandatory = TRUE)
 * @method static bool isFloat($value, $mandatory = TRUE)
 * @method static bool isGeoPosition($value, $mandatory = TRUE)
 * @method static bool isGermanDate($value, $mandatory = TRUE)
 * @method static bool isGermanZip($value, $mandatory = TRUE)
 * @method static bool isGuid($value, $mandatory = TRUE)
 * @method static bool isInteger($value, $mandatory = TRUE)
 * @method static bool isIpAddress($value, $mandatory = TRUE)
 * @method static bool isIpAddressV4($value, $mandatory = TRUE)
 * @method static bool isIpAddressV6($value, $mandatory = TRUE)
 * @method static bool isIsoDate($value, $mandatory = TRUE)
 * @method static bool isIsoDateTime($value, $mandatory = TRUE)
 * @method static bool isNotEmpty($value, $mandatory = TRUE)
 * @method static bool isNotXml($value, $mandatory = TRUE)
 * @method static bool isPassword($value, $mandatory = TRUE)
 * @method static bool isPhone($value, $mandatory = TRUE)
 * @method static bool isText($value, $mandatory = TRUE)
 * @method static bool isTime($value, $mandatory = TRUE)
 * @method static bool isTextWithNumbers($value, $mandatory = TRUE)
 * @method static bool isUrl($value, $mandatory = TRUE)
 * @method static bool isUrlHost($value, $mandatory = TRUE)
 * @method static bool isUrlHttp($value, $mandatory = TRUE)
 * @method static bool isXml($value, $mandatory = TRUE)
 */
class PapayaFilterFactory implements \IteratorAggregate {

  /**
   * @var array storage for field profiles, defined by constants in PapayaFilter
   */
  private static $_profiles = NULL;

  /**
   * Returns an ArrayIterator for the available profiles. The
   * profiles need to be defined in the PapayaFilter interface.
   *
   * The key contains a lowercase version of the profile name, the value
   * the "real version"
   *
   *
   * @return \Traversable|void
   */
  public function getIterator() {
    return new \ArrayIterator(self::_getProfiles());
  }

  /**
   * Fetch all constants from PapayaFilter and store them in an internal array.
   *
   * @codeCoverageIgnore
   * @return array
   * @throws ReflectionException
   */
  private static function _getProfiles() {
    if (NULL === self::$_profiles) {
      $reflection = new \ReflectionClass(\PapayaFilter::class);
      foreach ($reflection->getConstants() as $constant => $profile) {
        if (0 === strpos($constant, 'IS_')) {
          self::$_profiles[strtolower($profile)] = $profile;
        }
      }
    }
    return self::$_profiles;
  }

  /**
   * Check if a profile for an filter exists.
   *
   * @param $name
   * @return bool
   */
  public function hasProfile($name) {
    return class_exists(self::_getProfileClass($name));
  }

  /**
   * Get the class name for a given profile
   *
   * @param string $name
   * @return string
   */
  private static function _getProfileClass($name) {
    $key = strtolower($name);
    $class = __CLASS__.'Profile';
    if (isset(self::$_profiles[$key])) {
      return $class.\PapayaUtilStringIdentifier::toCamelCase(self::$_profiles[$key], TRUE);
    } else {
      return $class.\PapayaUtilStringIdentifier::toCamelCase($name, TRUE);
    }
  }

  /**
   * Get the filter factory profile by name
   * @param string $name
   * @throws \PapayaFilterFactoryExceptionInvalidProfile
   * @return \PapayaFilter
   */
  public function getProfile($name) {
    return self::_getProfile($name);
  }

  /**
   * Get the filter factory profile by name, internal static call
   *
   * @param string $name
   * @throws \PapayaFilterFactoryExceptionInvalidProfile
   * @throws \PapayaFilterFactoryExceptionInvalidProfile
   */
  private static function _getProfile($name) {
    $class = self::_getProfileClass($name);
    if (class_exists($class)) {
      return new $class();
    }
    throw new \PapayaFilterFactoryExceptionInvalidProfile($class);
  }

  /**
   * Get the filter using the specified profile.
   *
   * If mandatory is set to false, the actual filter will be prefixed with an PapayaFilterEmpty
   * allowing empty values.
   *
   * @param \PapayaFilterFactory|string $profile
   * @param boolean $mandatory
   * @param mixed $options
   * @return \PapayaFilter
   */
  public function getFilter($profile, $mandatory = TRUE, $options = NULL) {
    return self::_getFilter($profile, $mandatory, $options);
  }

  /**
   * Get the filter using the specified profile, internal static call
   *
   * @param $profile
   * @param bool $mandatory
   * @param mixed $options
   * @return \PapayaFilter|\PapayaFilterLogicalOr
   */
  private static function _getFilter($profile, $mandatory = TRUE, $options = NULL) {
    if (!$profile instanceof \PapayaFilterFactoryProfile) {
      $profile = self::_getProfile($profile);
    }
    if (isset($options)) {
      $profile->options($options);
    }
    $filter = $profile->getFilter();
    if ($mandatory) {
      return $filter;
    } else {
      return new \PapayaFilterLogicalOr(
        $filter,
        new \PapayaFilterEmpty(FALSE, FALSE)
      );
    }
  }

  /**
   * Validate a value using filter, filter profiles or a filter profile name.
   * Capture the exception from the filter and return a boolean.
   *
   * @param mixed $value
   * @param string|\PapayaFilter|\PapayaFilterFactoryProfile $filter
   * @param bool $mandatory
   * @return bool
   */
  public static function validate($value, $filter, $mandatory = TRUE) {
    if (!($filter instanceof \PapayaFilter)) {
      $filter = self::_getFilter($filter, $mandatory);
    } elseif (!$mandatory) {
      $filter = new \PapayaFilterLogicalOr(
        $filter,
        new \PapayaFilterEmpty(FALSE, FALSE)
      );
    }
    try {
      $filter->validate($value);
    } catch (\PapayaFilterException $e) {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Validate a value using filter, filter profiles or a filter profile name.
   * Capture the exception from the filter and return a boolean.
   *
   * @param mixed $value
   * @param string|\PapayaFilter|\PapayaFilterFactoryProfile $filter
   * @return mixed
   */
  public static function filter($value, $filter) {
    if (!($filter instanceof \PapayaFilter)) {
      $filter = self::_getFilter($filter);
    }
    return $filter->filter($value);
  }

  /**
   * Validate value using a PCRE pattern
   *
   * @param string $value
   * @param string $pattern
   * @param boolean $mandatory
   * @return bool
   */
  public static function matches($value, $pattern, $mandatory = TRUE) {
    return self::validate($value, new \PapayaFilterPcre($pattern), $mandatory);
  }

  /**
   * @param string $name
   * @param array $arguments
   * @throws \LogicException
   * @throws \InvalidArgumentException
   * @return bool
   */
  public static function __callStatic($name, $arguments) {
    if (substr($name, 0, 2) == 'is') {
      if (count($arguments) > 0) {
        $value = $arguments[0];
      } else {
        throw new \InvalidArgumentException(
          sprintf(
            'Missing argument #0 for %s::%s().',
            __CLASS__,
            $name
          )
        );
      }
      return self::validate(
        $value,
        $name,
        (count($arguments) > 1) ? (bool)$arguments[1] : TRUE
      );
    }
    throw new \LogicException(
      sprintf(
        'Unkown function %s::%s().',
        __CLASS__,
        $name
      )
    );
  }
}
