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
* Examine a string to given properties like email, numbers etc.
* This class will be used directly. You call the include functions with self::xyz.
*
* @package Papaya
* @subpackage Core
*/
class checkit {

  /**
   * @return array get A list of all defined checkit function (all functions of this class)
   */
  public static function getList() {
    static $result = NULL;
    if (NULL == $result) {
      $result = array();
      $checkFunctions = get_class_methods(__CLASS__);
      foreach ($checkFunctions as $functionName) {
        if (0 === strpos($functionName, 'is')) {
          $result[strtolower($functionName)] = $functionName;
        }
      }
    }
    return $result;
  }

  /**
   * Validate that a validation function exists
   *
   * @param $functionName
   * @return bool
   */
  public static function has($functionName) {
    $functions = self::getList();
    return isset($functions[strtolower($functionName)]);
  }

  /**
   * Validate a value using one of the function defined in this class.
   *
   * @param mixed $value
   * @param string $functionName
   * @param bool $mustContainValue
   * @param mixed ...$argument
   * @return mixed
   */
  public static function validate($value, $functionName, $mustContainValue = FALSE) {
    $arguments = array($value, $mustContainValue);
    return call_user_func_array(__CLASS__.'::'.$functionName, $arguments);
  }

  /**
   * Check if string is NOT empty
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isNotEmpty()
   *
   * @param string $str string to check
   * @return boolean
   */
  public static function filled($str) {
    return \Papaya\Filter\Factory::isNotEmpty($str);
  }

  /**
   * Check string against the pattern (PCRE)
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::matches()
   *
   * @param string $str string to check
   * @param string $pattern filter
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function check($str, $pattern, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::matches($str, $pattern, $mustContainValue);
  }

  /**
   * Check string consists of letter or alfanumeric characters.
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isText()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isAlpha($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::isText($str, $mustContainValue);
  }

  /**
   * Check string consists of letter or numbers.
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isText()
   *
   * @param string $str string
   * @param boolean $mustContainValue string may be empty?
  * @return boolean
   */
  public static function isAlphaChar($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::isText($str, $mustContainValue);
  }


  /**
   * Check string consists of numbers
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isInteger()
   * @see \Papaya\Filter\IntegerValue
   *
   * @param string $str string
   * @param boolean $mustContainValue string may be empty?
   * @param integer $digitsMin minimum number of numbers
   * @param integer $digitsMax maximum number of numbers
   * @return boolean
   */
  public static function isNum(
    $str, $mustContainValue = FALSE, $digitsMin = NULL, $digitsMax = NULL
  ) {
    if (isset($digitsMin)) {
      $filter = new \Papaya\Filter\LogicalAnd(
        new \Papaya\Filter\IntegerValue,
        new \Papaya\Filter\Length($digitsMin, $digitsMax)
      );
      return self::validate($str, $filter, $mustContainValue);
    } else {
      return \Papaya\Filter\Factory::isInteger($str, $mustContainValue);
    }
  }

  /**
   * check string is float number (with .)
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isInteger()
   *
   * @param string $str
   * @param boolean $mustContainValue optional, default value FALSE
   * @return boolean
   */
  public static function isFloat($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::isFloat($str, $mustContainValue);
  }

  /**
   * Check string consists of alphanumeric characters
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isTextWithNumbers()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isAlphaNum($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::isTextWithNumbers($str, $mustContainValue);
  }

  /**
   * Check string consists of alphanumeric characters with numbers
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isTextWithNumbers()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isAlphaNumChar($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::isTextWithNumbers($str, $mustContainValue);
  }

  /**
   * Check string consists of measure and numbers
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isCssSize()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isNumUnit($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::isCssSize($str, $mustContainValue);
  }

  /**
  * Check string is safe to use in an url
  *
  * @param string $str string to check
  * @param boolean $mustContainValue string may be empty?
  * @return boolean
  */
  public static function internalSafeURL($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::matches(
      $str, '(^[a-zA-Z0-9\.\(\)\[\]\/ ,_-]+$)u', $mustContainValue
    );
  }

  /**
   * Check string is no HTML
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isNotXML()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isNoHTML($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::isNotXML($str, $mustContainValue);
  }

  /**
   * Check string is some text
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isNotEmpty()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isSomeText($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::isNotEmpty($str, $mustContainValue);
  }

  /**
   * Check string is a phone number
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isPhone()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isPhone($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::isPhone($str, $mustContainValue);
  }

  /**
   * Check string is filename
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isFileName()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isFile($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::isFileName($str, $mustContainValue);
  }

  /**
   * Check string is path
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isFilePath()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isPath($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::isFilePath($str, $mustContainValue);
  }

  /**
   * Check string is date in german format dd.mm.yyyy.
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isGermanDate()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isGermanDate($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::isGermanDate($str, $mustContainValue);
  }

  /**
   * Check string is german zip NNNNN or D-NNNNN
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isGermanZip()
   *
   * @param string $str String to check
   * @param bool $mustContainValue String may be empty?
   * @return boolean
   */
  public static function isGermanZip($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::isGermanDate($str, $mustContainValue);
  }

  /**
   * Extended HTTP check
   *
   * anchors             http://www.blah.de/index.html#top
   * parameters          http://www.blah.de/index.html?foo=bar
   * virtual directories http://www.blah.de/~user/index.html
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isUrl()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isHTTPX($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::isUrl($str, $mustContainValue);
  }

  /**
   * Check web adress (http://* or www.*)
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isHttp()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isHTTP($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::isUrlHttp($str, $mustContainValue);
  }

  /**
   * Check http host name
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isHttp()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isHTTPHost($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::isUrlHost($str, $mustContainValue);
  }

  /**
   * Check IPv4 address
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isIpAddressV4()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isIPv4Address($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::isIpAddressV4($str, $mustContainValue);
  }

  /**
   * Check IPv6 address
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isIpAddressV6()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isIPv6Address($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::isIpAddressV6($str, $mustContainValue);
  }

  /**
   * Check any IP address
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isIpAddress()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isIPAddress($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::isIpAddress($str, $mustContainValue);
  }

  /**
   * Check host name or IP address
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isUrlHost()
   * @see \Papaya\Filter\Factory::isIpAddress()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isHTTPHostOrIPAddress($str, $mustContainValue = FALSE) {
    return (
      \Papaya\Filter\Factory::isUrlHost($str, $mustContainValue) ||
      \Papaya\Filter\Factory::isIpAddress($str, $mustContainValue)
    );
  }

  /**
   * Check string is email adress
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isEmail()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isEmail($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::isEmail($str, $mustContainValue);
  }

  /**
   * Check string is 32 byte hexcode
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isGuid()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @access public
   * @return boolean
   */
  public static function isGUID($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::isGuid($str, $mustContainValue);
  }

  /**
   * Check date is in ISO-format
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isIsoDate()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isISODate($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::isIsoDate($str, $mustContainValue);
  }

  /**
   * Check geo position
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isGeoPosition()
   *
   * This Method checks if a string consists of 2 comma separeted double values and if
   * they are between -180 and 180 degrees.
   *
   * @param string String to check
   * @param boolean String must contain any values
   * @return boolean True if string is correct
   */
  public static function isGeoPos($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::isGeoPosition($str, $mustContainValue);
  }

  /**
   * Check date and time is in ISO-format
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isIsoDateTime()
   *
   * @param string $str string
   * @param boolean $mustContainValue string may be empty ?
   * @return mixed FALSE or int
   */
  public static function isISODateTime($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::isIsoDateTime($str, $mustContainValue);
  }

  /**
   * Check string is a time
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isTime()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isTime($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::isTime($str, $mustContainValue);
  }

  /**
   * Check string is HTML color
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isCssColor()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isHTMLColor($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::isCssColor($str, $mustContainValue);
  }

  /**
   * Check string is Password
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isPassword()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isPassword($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::isPassword($str, $mustContainValue);
  }

  /**
   * convert ISO date/time to unix timestamp
   *
   * @deprecated
   * @see \Papaya\Utility\Date::stringToTimestamp()
   *
   * @param string $str string to convert
   * @access public
   * @return integer
   */
  public static function convertISODateTimeToUnix($str) {
    return (int)\Papaya\Utility\Date::stringToTimestamp($str);
  }

  /**
   * Check string is xhtml
   *
   * @deprecated
   * @see \Papaya\Filter\Factory::isXML()
   *
   * @param $str
   * @param boolean $mustContainValue string may be empty?
   * @return bool $result
   */
  public static function isXhtml($str, $mustContainValue = FALSE) {
    return \Papaya\Filter\Factory::isXML($str, $mustContainValue);
  }

  /**
  * check string for spam
  *
  * @param string $str
  * @return integer $result
  */
  public static function isSpam($str) {
    $result = 0;
    $matches = array();
    $result += floor(preg_match_all('~\bhttps?://~iu', $str, $matches) / 2);
    $result += preg_match_all('~<a[^>]+href~iu', $str, $matches);
    return $result;
  }
}
