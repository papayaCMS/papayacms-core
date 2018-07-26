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
   * @see \PapayaFilterFactory::isNotEmpty()
   *
   * @param string $str string to check
   * @return boolean
   */
  public static function filled($str) {
    return \PapayaFilterFactory::isNotEmpty($str);
  }

  /**
   * Check string against the pattern (PCRE)
   *
   * @deprecated
   * @see \PapayaFilterFactory::matches()
   *
   * @param string $str string to check
   * @param string $pattern filter
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function check($str, $pattern, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::matches($str, $pattern, $mustContainValue);
  }

  /**
   * Check string consists of letter or alfanumeric characters.
   *
   * @deprecated
   * @see \PapayaFilterFactory::isText()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isAlpha($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::isText($str, $mustContainValue);
  }

  /**
   * Check string consists of letter or numbers.
   *
   * @deprecated
   * @see \PapayaFilterFactory::isText()
   *
   * @param string $str string
   * @param boolean $mustContainValue string may be empty?
  * @return boolean
   */
  public static function isAlphaChar($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::isText($str, $mustContainValue);
  }


  /**
   * Check string consists of numbers
   *
   * @deprecated
   * @see \PapayaFilterFactory::isInteger()
   * @see PapayaFilterInteger
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
      $filter = new \PapayaFilterLogicalAnd(
        new \PapayaFilterInteger,
        new \PapayaFilterLength($digitsMin, $digitsMax)
      );
      return self::validate($str, $filter, $mustContainValue);
    } else {
      return \PapayaFilterFactory::isInteger($str, $mustContainValue);
    }
  }

  /**
   * check string is float number (with .)
   *
   * @deprecated
   * @see \PapayaFilterFactory::isInteger()
   *
   * @param string $str
   * @param boolean $mustContainValue optional, default value FALSE
   * @return boolean
   */
  public static function isFloat($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::isFloat($str, $mustContainValue);
  }

  /**
   * Check string consists of alphanumeric characters
   *
   * @deprecated
   * @see \PapayaFilterFactory::isTextWithNumbers()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isAlphaNum($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::isTextWithNumbers($str, $mustContainValue);
  }

  /**
   * Check string consists of alphanumeric characters with numbers
   *
   * @deprecated
   * @see \PapayaFilterFactory::isTextWithNumbers()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isAlphaNumChar($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::isTextWithNumbers($str, $mustContainValue);
  }

  /**
   * Check string consists of measure and numbers
   *
   * @deprecated
   * @see \PapayaFilterFactory::isCssSize()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isNumUnit($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::isCssSize($str, $mustContainValue);
  }

  /**
  * Check string is safe to use in an url
  *
  * @param string $str string to check
  * @param boolean $mustContainValue string may be empty?
  * @return boolean
  */
  public static function internalSafeURL($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::matches(
      $str, '(^[a-zA-Z0-9\.\(\)\[\]\/ ,_-]+$)u', $mustContainValue
    );
  }

  /**
   * Check string is no HTML
   *
   * @deprecated
   * @see \PapayaFilterFactory::isNotXml()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isNoHTML($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::isNotXml($str, $mustContainValue);
  }

  /**
   * Check string is some text
   *
   * @deprecated
   * @see \PapayaFilterFactory::isNotEmpty()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isSomeText($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::isNotEmpty($str, $mustContainValue);
  }

  /**
   * Check string is a phone number
   *
   * @deprecated
   * @see \PapayaFilterFactory::isPhone()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isPhone($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::isPhone($str, $mustContainValue);
  }

  /**
   * Check string is filename
   *
   * @deprecated
   * @see \PapayaFilterFactory::isFileName()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isFile($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::isFileName($str, $mustContainValue);
  }

  /**
   * Check string is path
   *
   * @deprecated
   * @see \PapayaFilterFactory::isFilePath()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isPath($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::isFilePath($str, $mustContainValue);
  }

  /**
   * Check string is date in german format dd.mm.yyyy.
   *
   * @deprecated
   * @see \PapayaFilterFactory::isGermanDate()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isGermanDate($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::isGermanDate($str, $mustContainValue);
  }

  /**
   * Check string is german zip NNNNN or D-NNNNN
   *
   * @deprecated
   * @see \PapayaFilterFactory::isGermanZip()
   *
   * @param string $str String to check
   * @param bool $mustContainValue String may be empty?
   * @return boolean
   */
  public static function isGermanZip($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::isGermanDate($str, $mustContainValue);
  }

  /**
   * Extended HTTP check
   *
   * anchors             http://www.blah.de/index.html#top
   * parameters          http://www.blah.de/index.html?foo=bar
   * virtual directories http://www.blah.de/~user/index.html
   *
   * @deprecated
   * @see \PapayaFilterFactory::isUrl()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isHTTPX($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::isUrl($str, $mustContainValue);
  }

  /**
   * Check web adress (http://* or www.*)
   *
   * @deprecated
   * @see \PapayaFilterFactory::isHttp()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isHTTP($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::isUrlHttp($str, $mustContainValue);
  }

  /**
   * Check http host name
   *
   * @deprecated
   * @see \PapayaFilterFactory::isHttp()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isHTTPHost($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::isUrlHost($str, $mustContainValue);
  }

  /**
   * Check IPv4 address
   *
   * @deprecated
   * @see \PapayaFilterFactory::isIpAddressV4()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isIPv4Address($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::isIpAddressV4($str, $mustContainValue);
  }

  /**
   * Check IPv6 address
   *
   * @deprecated
   * @see \PapayaFilterFactory::isIpAddressV6()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isIPv6Address($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::isIpAddressV6($str, $mustContainValue);
  }

  /**
   * Check any IP address
   * @deprecated
   * @see \PapayaFilterFactory::isIpAddress()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isIPAddress($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::isIpAddress($str, $mustContainValue);
  }

  /**
   * Check host name or IP address
   *
   * @deprecated
   * @see \PapayaFilterFactory::isUrlHost()
   * @see \PapayaFilterFactory::isIpAddress()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isHTTPHostOrIPAddress($str, $mustContainValue = FALSE) {
    return (
      \PapayaFilterFactory::isUrlHost($str, $mustContainValue) ||
      \PapayaFilterFactory::isIpAddress($str, $mustContainValue)
    );
  }

  /**
   * Check string is email adress
   *
   * @deprecated
   * @see \PapayaFilterFactory::isEmail()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isEmail($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::isEmail($str, $mustContainValue);
  }

  /**
   * Check string is 32 byte hexcode
   *
   * @deprecated
   * @see \PapayaFilterFactory::isGuid()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @access public
   * @return boolean
   */
  public static function isGUID($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::isGuid($str, $mustContainValue);
  }

  /**
   * Check date is in ISO-format
   *
   * @deprecated
   * @see \PapayaFilterFactory::isIsoDate()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isISODate($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::isIsoDate($str, $mustContainValue);
  }

  /**
   * Check geo position
   *
   * @deprecated
   * @see \PapayaFilterFactory::isGeoPosition()
   *
   * This Method checks if a string consists of 2 comma separeted double values and if
   * they are between -180 and 180 degrees.
   *
   * @param string String to check
   * @param boolean String must contain any values
   * @return boolean True if string is correct
   */
  public static function isGeoPos($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::isGeoPosition($str, $mustContainValue);
  }

  /**
   * Check date and time is in ISO-format
   *
   * @deprecated
   * @see \PapayaFilterFactory::isIsoDateTime()
   *
   * @param string $str string
   * @param boolean $mustContainValue string may be empty ?
   * @return mixed FALSE or int
   */
  public static function isISODateTime($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::isIsoDateTime($str, $mustContainValue);
  }

  /**
   * Check string is a time
   *
   * @deprecated
   * @see \PapayaFilterFactory::isTime()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isTime($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::isTime($str, $mustContainValue);
  }

  /**
   * Check string is HTML color
   *
   * @deprecated
   * @see \PapayaFilterFactory::isCssColor()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isHTMLColor($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::isCssColor($str, $mustContainValue);
  }

  /**
   * Check string is Password
   *
   * @deprecated
   * @see \PapayaFilterFactory::isPassword()
   *
   * @param string $str string to check
   * @param boolean $mustContainValue string may be empty?
   * @return boolean
   */
  public static function isPassword($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::isPassword($str, $mustContainValue);
  }

  /**
   * convert ISO date/time to unix timestamp
   *
   * @deprecated
   * @see \PapayaUtilDate::stringToTimestamp()
   *
   * @param string $str string to convert
   * @access public
   * @return integer
   */
  public static function convertISODateTimeToUnix($str) {
    return (int)PapayaUtilDate::stringToTimestamp($str);
  }

  /**
   * Check string is xhtml
   *
   * @deprecated
   * @see \PapayaFilterFactory::isXml()
   *
   * @param $str
   * @param boolean $mustContainValue string may be empty?
   * @return bool $result
   */
  public static function isXhtml($str, $mustContainValue = FALSE) {
    return \PapayaFilterFactory::isXml($str, $mustContainValue);
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
