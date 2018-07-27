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

namespace Papaya\Filter;

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
class Factory implements \IteratorAggregate {

  /**
   * @var array storage for field profiles, defined by constants in \Papaya\PapayaFilter
   */
  private static $_profiles;

  /**
   * Returns an ArrayIterator for the available profiles. The
   * profiles need to be defined in the \Papaya\PapayaFilter interface.
   *
   * The key contains a lowercase version of the profile name, the value
   * the "real version"
   *
   *
   * @return \Traversable|void
   * @throws \ReflectionException
   */
  public function getIterator() {
    return new \ArrayIterator(self::_getProfiles());
  }

  /**
   * Fetch all constants from \Papaya\PapayaFilter and store them in an internal array.
   *
   * @codeCoverageIgnore
   * @return array
   * @throws \ReflectionException
   */
  private static function _getProfiles() {
    if (NULL === self::$_profiles) {
      $reflection = new \ReflectionClass(\Papaya\Filter::class);
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
    $namespace = __CLASS__.'\\Profile\\';
    if (isset(self::$_profiles[$key])) {
      return $namespace.\PapayaUtilStringIdentifier::toCamelCase(self::$_profiles[$key], TRUE);
    }
    return $namespace.\PapayaUtilStringIdentifier::toCamelCase($name, TRUE);
  }

  /**
   * Get the filter factory profile by name
   *
   * @param string $name
   * @throws Factory\Exception\InvalidProfile
   * @return Factory\Profile
   */
  public function getProfile($name) {
    return self::_getProfile($name);
  }

  /**
   * Get the filter factory profile by name, internal static call
   *
   * @param string $name
   * @return Factory\Profile
   * @throws Factory\Exception\InvalidProfile
   * @throws Factory\Exception\InvalidProfile
   */
  private static function _getProfile($name) {
    $class = self::_getProfileClass($name);
    if (class_exists($class)) {
      return new $class();
    }
    throw new Factory\Exception\InvalidProfile($class);
  }

  /**
   * Get the filter using the specified profile.
   *
   * If mandatory is set to false, the actual filter will be prefixed with an \PapayaFilterEmpty
   * allowing empty values.
   *
   * @param Factory\Profile|string $profile
   * @param boolean $mandatory
   * @param mixed $options
   * @return \Papaya\Filter
   * @throws Factory\Exception\InvalidProfile
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
   * @return \Papaya\Filter|\Papaya\Filter\LogicalOr
   * @throws Factory\Exception\InvalidProfile
   */
  private static function _getFilter($profile, $mandatory = TRUE, $options = NULL) {
    if (!$profile instanceof Factory\Profile) {
      $profile = self::_getProfile($profile);
    }
    if (NULL !== $options) {
      $profile->options($options);
    }
    $filter = $profile->getFilter();
    if ($mandatory) {
      return $filter;
    }
    return new \Papaya\Filter\LogicalOr(
      $filter,
      new \PapayaFilterEmpty(FALSE, FALSE)
    );
  }

  /**
   * Validate a value using filter, filter profiles or a filter profile name.
   * Capture the exception from the filter and return a boolean.
   *
   * @param mixed $value
   * @param string|\Papaya\Filter|Factory\Profile $filter
   * @param bool $mandatory
   * @return bool
   * @throws \Papaya\Filter\Factory\Exception\InvalidProfile
   */
  public static function validate($value, $filter, $mandatory = TRUE) {
    if (!($filter instanceof \Papaya\Filter)) {
      $filter = self::_getFilter($filter, $mandatory);
    } elseif (!$mandatory) {
      $filter = new \Papaya\Filter\LogicalOr(
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
   * @param string|\Papaya\Filter|Factory\Profile $filter
   * @return mixed
   * @throws \Papaya\Filter\Factory\Exception\InvalidProfile
   */
  public static function filter($value, $filter) {
    if (!($filter instanceof \Papaya\Filter)) {
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
   * @throws \Papaya\Filter\Factory\Exception\InvalidProfile
   */
  public static function matches($value, $pattern, $mandatory = TRUE) {
    return self::validate($value, new \PapayaFilterPcre($pattern), $mandatory);
  }

  /**
   * @param string $name
   * @param array $arguments
   * @return bool
   * @throws \Papaya\Filter\Factory\Exception\InvalidProfile
   */
  public static function __callStatic($name, $arguments) {
    if (0 === strpos($name, 'is')) {
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
        'Unknown function %s::%s().',
        __CLASS__,
        $name
      )
    );
  }
}
