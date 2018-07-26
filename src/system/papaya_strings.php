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
* some string handling functions
*
* @package Papaya-Library
* @subpackage Strings
*/
class papaya_strings {

  /**
  * Escapes entities and some chars to be used in the xml-output.
  *
  * @param string $str
  * @param boolean $escapeTags Escape < and >, default value FALSE
  * @access public
  * @return string
  */
  public static function entityToXML($str, $escapeTags = FALSE) {
    $result = \PapayaUtilStringUtf8::ensure($str);
    $result = preg_replace(
      '/\&((amp)|(quot)|([gl]t)|(#((\d+)|(x[a-fA-F\d]{2,4}))))\;/iu',
      '#||\\1||#',
      $result
    );
    $result = \PapayaUtilStringHtml::decodeNamedEntities($result);
    $result = str_replace('&', '&amp;', $result);
    $result = preg_replace('/\#\|\|([a-z\d\#]+)\|\|\#/iu', '&\\1;', $result);
    $result = str_replace('&amp;amp;', '&amp;', $result);
    if ($escapeTags) {
      $result = str_replace(array('<', '>'), array('&lt;', '&gt;'), $result);
    }
    return $result;
  }

  /**
  * Escape HTML special chars
  *
  * @param string $str
  * @access public
  * @return string
  */
  public static function escapeHTMLChars($str) {
    return str_replace(
      array('&', '<', '>', '"'),
      array('&amp;', '&lt;', '&gt;', '&quot;'),
      $str
    );
  }

  /**
   * escape <> inside html tags, convert linbreaks to <br />
   *
   * @param string $str
   * @param boolean $nl2br convert linebreaks
   * @throws LogicException
   * @access public
   * @return string
   */
  public static function escapeHTMLTags($str, $nl2br = FALSE) {
    $replace = array('<', '>');
    $replaceWith = array('&lt;', '&gt;');
    $offset = 0;
    $status = 0;
    $buffer = '';
    $result = '';
    $tagsAmount = 0;
    $nextItemPos = 0;
    do {
      switch ($status) {
      case 0 : // in text
        $tagStart = strpos($str, '<', $offset);
        $commentStart = strpos($str, '<!--', $offset);
        $positions = array();
        if ($tagStart !== FALSE) {
          $positions[] = $tagStart;
        }
        if ($commentStart !== FALSE) {
          $positions[] = $commentStart;
        }
        if (count($positions) > 0) {
          $nextItemPos = min($positions);
        } else {
          $nextItemPos = FALSE;
        }
        break;
      case 1 : // in tag
        $doubleQuote = strpos($str, '"', $offset);
        $singleQuote = strpos($str, "'", $offset);
        $tagEnd = strpos($str, ">", $offset);
        $positions = array();
        if ($doubleQuote !== FALSE) {
          $positions[] = $doubleQuote;
        }
        if ($singleQuote !== FALSE) {
          $positions[] = $singleQuote;
        }
        if ($tagEnd !== FALSE) {
          $positions[] = $tagEnd;
        }
        if (count($positions) > 0) {
          $nextItemPos = min($positions);
        } else {
          $nextItemPos = FALSE;
        }
        break;
      case 2 : // in double quote attribute
        $nextItemPos = strpos($str, '"', $offset);
        break;
      case 3 : // in single quote attribute
        $nextItemPos = strpos($str, "'", $offset);
        break;
      case 4 : // in comment
        $nextItemPos = strpos($str, "-->", $offset);
        break;
      }
      if (FALSE !== $nextItemPos) {
        $buffer .= substr($str, $offset, $nextItemPos - $offset);
        $itemChar = substr($str, $nextItemPos, 1);
        switch ($itemChar) {
        case '<' : //tag or comment starts
          $tagsAmount++;
          if (!empty($buffer)) {
            if ($nl2br) {
              $result .= preg_replace("((?:\r\n)|(?:\n\r)|[\r\n])", "<br />\n", $buffer);
            } else {
              $result .= $buffer;
            }
          }
          $buffer = '';
          if (substr($str, $nextItemPos, 4) == '<!--') {
            $offset = $nextItemPos + 4;
            $status = 4;
          } else {
            $status = 1;
            $offset = $nextItemPos + 1;
          }
          break;
        case '>' : //tag ends
          if (!empty($buffer)) {
            $result .= '<'.str_replace($replace, $replaceWith, $buffer).'>';
          }
          $buffer = '';
          $status = 0;
          $offset = $nextItemPos + 1;
          break;
        case '"' : //double quote attribute starts or ends
          $buffer .= $itemChar;
          $status = ($status == 1) ? 2 : 1;
          $offset = $nextItemPos + 1;
          break;
        case "'" : //single quote attribute starts or ends
          $buffer .= $itemChar;
          $status = ($status == 1) ? 3 : 1;
          $offset = $nextItemPos + 1;
          break;
        case '-' : //comment ends
          if (!empty($buffer)) {
            $result .= '<!--'.$buffer.'-->';
          }
          $buffer = '';
          $status = 0;
          $offset = $nextItemPos + 3;
        }
      }
    } while (FALSE !== $nextItemPos);
    $buffer .= substr($str, $offset);
    if ((!empty($buffer)) && $status == 0) {
      $result .= $buffer;
    }
    if ($tagsAmount == 0 && $nl2br == TRUE) {
      $result = preg_replace("((?:\r\n)|(?:\n\r)|[\r\n])", "<br />\n", $buffer);
    }
    return $result;
  }

  /**
  * split lines at CRLF, LFCR, CR and LF
  *
  * @param string $string
  * @access public
  * @return array Lines
  */
  public static function splitLines($string) {
    return preg_split("((?:\r\n)|(?:\n\r)|[\r\n])", $string);
  }

  /**
   * Escape String for use in javascript.
   *
   * @param  string String to Escape
   * @return string Escaped Strign
   */
  public static function escapeJavaScriptString($str) {
    return str_replace(array("'", '"'), array("\'", '\"'), $str);
  }


  /**
  * Checks a UTF-8 string for invalid bytes and converts it to UTF-8.
  *
  * It assumes that the invalid bytes are ISO-8859-1. Valid UTF-8 chars stay unchanged.
  *
  * @param string $str
  * @access public
  * @return string
  */
  public static function ensureUTF8($str) {
    return \PapayaUtilStringUtf8::ensure($str);
  }

  /**
  * Check the string to be valid UTF-8
  *
  * @param string $str
  * @access public
  * @return boolean String is valid UTF-8
  */
  public static function isUTF8($str) {
    $pattern = '~^([\\x00-\\x7F]|
                   [\\xC2-\\xDF][\\x80-\\xBF]|
                   \\xE0[\\xA0-\\xBF][\\x80-\\xBF]|[\\xE1-\\xEC][\\x80-\\xBF]{2}|
                   \\xED[\\x80-\\x9F][\\x80-\\xBF]|[\\xEE-\\xEF][\\x80-\\xBF]{2}|
                   \\xF0[\\x90-\\xBF][\\x80-\\xBF]{2}|[\\xF1-\\xF3][\\x80-\\xBF]{3}|
                   \\xF4[\\x80-\\x8F][\\x80-\\xBF]{2})*$~x';
    $result = (bool)preg_match($pattern, $str);
    return $result;
  }

  /**
  * Check the string to contain only ASCII chars
  *
  * @param string $str
  * @access public
  * @return boolean String contains only ASCII chars
  */
  public static function isAscii($str) {
    $pattern = '~^([\\x00-\\x7F]+)$~';
    return (bool)preg_match($pattern, $str);
  }

  /**
   * Get a part of a string
   *
   * @param string $str
   * @param integer $start
   * @param null $length
   * @internal param int $mixed $length optional, default value NULL
   * @access public
   * @return string
   */
  public static function substr($str, $start, $length = NULL) {
    return \PapayaUtilStringUtf8::copy($str, $start, $length);
  }

  /**
  * String length
  *
  * @param string $str
  * @access public
  * @return integer lenght of string
  */
  public static function strlen($str) {
    return \PapayaUtilStringUtf8::length($str);
  }

  /**
   * first position of $needle in $haystack
   *
   * @param string $haystack
   * @param string $needle
   * @param int|string $offset
   * @access public
   * @return integer
   */
  public static function strpos($haystack, $needle, $offset = 0) {
    return \PapayaUtilStringUtf8::position($haystack, $needle, $offset);
  }

  /**
  * last position of $needle in $haystack
  *
  * @param string $haystack
  * @param string $needle
  * @access public
  * @return integer
  */
  public static function strrpos($haystack, $needle) {
    if (function_exists('mb_strrpos')) {
      return mb_strrpos($haystack, $needle, 'UTF-8');
    } elseif (function_exists('iconv_strpos')) {
      return iconv_strrpos($haystack, $needle, 'UTF-8');
    } else {
      return strrpos($haystack, $needle);
    }
  }

  /**
  * Convert string to lowercase
  * @param string $string
  * @return string
  */
  public static function strtolower($string) {
    return \PapayaUtilStringUtf8::toLowerCase($string);
  }

  /**
  * Convert string to uppercase
  * @param string $string
  * @return string
  */
  public static function strtoupper($string) {
    return \PapayaUtilStringUtf8::toUpperCase($string);
  }

  /**
  * Normalize string
  *
  * @param string $utf8String input string
  * @param integer $maxLength optional, default value 0
  * @param string $language transliteration language optional, default value 0
  * @access public
  * @return string $str normalized string
  */
  public static function normalizeString($utf8String, $maxLength = 0, $language = NULL) {
    return \PapayaUtilFile::normalizeName($utf8String, $maxLength, $language);
  }

  /**
  * Clean input string
  *
  * @see papaya_strings::ensureUTF8
  * @param string $value input string
  * @access public
  * @return string
  */
  public static function cleanInputString($value) {
    $special = array(
      '&nbsp;' => ' ',
      '&ndash;' => '-',
      '&bdquo;' => '"',
      '&ldquo;' => '"',
      '&hellip;' => '...',
      '&lsquo;' => "'",
      '&rsquo;' => "'",
      '&euro;' => "\x80");
    $translation = array_flip(
      get_html_translation_table(HTML_ENTITIES, ENT_NOQUOTES)
    );
    unset($translation['&gt;']);
    unset($translation['&lt;']);
    unset($translation['&quot;']);
    $result = strtr($value, array_merge($translation, $special));
    $repair = array(
      '~(<embed[^>]+>)(</embed>)~' => '$1 $2',
      '~(<param[^>]+)>(</param>)~' => '$1 />',
    );
    $result = preg_replace(array_keys($repair), array_values($repair), $result);
    $remove = array(
      // word default class
      '~class="MsoNormal"~i',
      // remove all unknown namespaces (not papaya)
      '~</?([a-oq-z\\d]|p[b-z\\d]|pa[a-oq-z\\d]|pap[b-z\\d]|papa[a-xz\\d]|papay[b-z\\d])[a-z\\d]*'.
        ':([a-z\\d-])[^>]*>~i',
      //all empty long tags
      '~<(\\w+)[^<>]*?[^/]></\\1>~i',
      //comments
      '~<!--(.*?)-->~',
      //several tags without content tags
      '~<(strong|em|p)>\s*</\\1>~iU',
      '~style="margin-bottom:(\\s*)0(cm|em|pt);"~i',
      // remove OpenOffice meta stuff
      '~<meta[^>]*/>~i',
      // remove OpenOffice title
      '~<title>[^<]*</title>~i',
      // aggressive removal of all font tags
      '~</?font[^>]*>~i',
      // mozilla css
      '~-moz(-\\w+)+:[^;]+;~i'
    );
    $result = preg_replace($remove, '', $result);
    return papaya_strings::ensureUTF8($result);
  }

  /**
  * utf-8 to charset
  *
  * @param string $utf8String
  * @param string $charset
  * @access public
  * @return string
  */
  public static function utf8ToCharset($utf8String, $charset) {
    if (function_exists('iconv')) {
      return iconv('UTF-8', strtoupper($charset).'//TRANSLIT', $utf8String);
    } elseif (function_exists('mb_convert_encoding')) {
      return mb_convert_encoding($utf8String, strtoupper($charset), 'UTF-8');
    }
    return utf8_decode($utf8String);
  }

  /**
  * truncates string after specified length
  *
  * @author David Rekowski <info@papaya-cms.com>
  * @param string $str input string
  * @param integer $length desired length of output string
  * @param string $sep optional go back to last word ' ', '' if not
  * @param string $continue append this to the string if it was truncated
  * @return string $result truncated string or input string if shorter than $length
  */
  public static function truncate($str, $length = 80, $sep = ' ', $continue = '...') {
    if ($length < papaya_strings::strlen($str) - papaya_strings::strlen($continue)) {
      $cut = papaya_strings::substr($str, 0, $length);
      if ($sep != '' && ($pos = papaya_strings::strrpos($cut, $sep))) {
        $result = papaya_strings::substr($cut, 0, $pos);
      } else {
        return $cut.$continue;
      }
      return $result.$continue;
    }
    return $str;
  }


  /**
   * truncates filename after specified length (but saves extension)
   *
   * @author Thomas Weinert <info@papaya-cms.com>
   * @param string $str input string
   * @param integer $length desired length of output string
   * @param string $continue append this to the string if it was truncated
   * @return string $result truncated string or input string if shorter than $length
   */
  public static function truncateFileName($str, $length = 80, $continue = '..') {
    if ($length < papaya_strings::strlen($str)) {
      $pos = papaya_strings::strrpos($str, '.');
      if ($pos > 0) {
        $ext = papaya_strings::substr($str, $pos);
        $cut = papaya_strings::substr($str, 0, $length - papaya_strings::strlen($ext));
      } else {
        $ext = '';
        $cut = papaya_strings::substr($str, 0, $length);
      }
      return $cut.$continue.$ext;
    }
    return $str;
  }

  /**
  * takes a string of utf-8 encoded characters and converts it to a string of unicode
  * entities each unicode entitiy has the either the form &#nnnnn; or &#nnn; n={0..9}
  * and can be displayed by utf-8 supporting  browsers.
  * If the character passed maps as lower ascii it stays as such (a single char)
  * instead of being presented as a unicode entity
  *
  * This function is from the Zend code library
  *
  * @author Ronen Botzer
  * @param string $source string encoded using utf-8
  * @return string with unicode entities
  * @access public
  */
  public static function utf8ToUnicodeEntities($source) {
    // array used to figure what number to decrement from character order value
    // according to number of characters used to map unicode to ascii by utf-8
    $decrement[4] = 240;
    $decrement[3] = 224;
    $decrement[2] = 192;
    $decrement[1] = 0;

    // the number of bits to shift each charNum by
    $shift[1][0] = 0;
    $shift[2][0] = 6;
    $shift[2][1] = 0;
    $shift[3][0] = 12;
    $shift[3][1] = 6;
    $shift[3][2] = 0;
    $shift[4][0] = 18;
    $shift[4][1] = 12;
    $shift[4][2] = 6;
    $shift[4][3] = 0;

    $pos = 0;
    $len = strlen($source);
    $encodedString = '';
    while ($pos < $len) {
      $asciiPos = ord(substr($source, $pos, 1));
      if (($asciiPos >= 240) && ($asciiPos <= 255)) {
        // 4 chars representing one unicode character
        $thisLetter = substr($source, $pos, 4);
        $pos += 4;
      } elseif (($asciiPos >= 224) && ($asciiPos <= 239)) {
        // 3 chars representing one unicode character
        $thisLetter = substr($source, $pos, 3);
        $pos += 3;
      } elseif (($asciiPos >= 192) && ($asciiPos <= 223)) {
        // 2 chars representing one unicode character
        $thisLetter = substr($source, $pos, 2);
        $pos += 2;
      } else {
        // 1 char (lower ascii)
        $thisLetter = substr($source, $pos, 1);
        $pos += 1;
      }

      $thisLen = strlen($thisLetter);
      if ($thisLen > 1) {
        // process the string representing the letter to a unicode entity
        $thisPos = 0;
        $decimalCode = 0;
        while ($thisPos < $thisLen) {
          $thisCharOrd = ord(substr($thisLetter, $thisPos, 1));
          if ($thisPos == 0) {
            $charNum = intval($thisCharOrd - $decrement[$thisLen]);
            $decimalCode += ($charNum << $shift[$thisLen][$thisPos]);
          } else {
            $charNum = intval($thisCharOrd - 128);
            $decimalCode += ($charNum << $shift[$thisLen][$thisPos]);
          }
          $thisPos++;
        }
        if ($thisLen == 1) {
          //this should never happen
          $encodedLetter = "&#".str_pad($decimalCode, 3, "0", STR_PAD_LEFT).';';
        } else {
          $encodedLetter = "&#".str_pad($decimalCode, 5, "0", STR_PAD_LEFT).';';
        }
        $encodedString .= $encodedLetter;
      } else {
        $encodedString .= $thisLetter;
      }
    }
    return $encodedString;
  }

  /**
  * decode xml character references and entities to utf-8
  *
  * @param string $str
  * @access public
  * @return string
  */
  public static function unicodeEntitiesToUTF8($str) {
    return html_entity_decode($str, ENT_QUOTES, 'UTF-8');
  }

  /**
  * preg_replace_callback function - converts a numeric, decimal unicode to utf8 mbcs
  * because of php bug #25670 we need to emulate html_entity_decode for old versions
  * this is the callback for this emulation
  *
  * Thanks to the php manual commentors
  *
  * @param array $match;
  * @access public
  * @return string
  */
  public static function unicodeEntitiesToUTF8_PHP4Callback($match) {
    if (isset($match[1]) && $match[1] != '') {
      $num = hexdec($match[2]);
    } else {
      $num = (int)$match[2];
    }
    if ($num <= 0) {
      return '';
    }
    if ($num < 128) {
      return chr($num);
    }
    if ($num < 160) {
      $charMap = array(
        128 => 8364,
        129 => 160,
        130 => 8218,
        131 => 402,
        132 => 8222,
        133 => 8230,
        134 => 8224,
        135 => 8225,
        136 => 710,
        137 => 8240,
        138 => 352,
        139 => 8249,
        140 => 338,
        141 => 160,
        142 => 381,
        143 => 160,
        144 => 160,
        145 => 8216,
        146 => 8217,
        147 => 8220,
        148 => 8221,
        149 => 8226,
        150 => 8211,
        151 => 8212,
        152 => 732,
        153 => 8482,
        154 => 353,
        155 => 8250,
        156 => 339,
        157 => 160,
        158 => 382,
        159 => 376
      );
      if (isset($charMap[$num])) {
        $num = $charMap[$num];
      }
    }
    if ($num < 2048) {
      return chr(($num >> 6) + 192).chr(($num & 63) + 128);
    }
    if ($num < 65536) {
      return chr(($num >> 12) + 224).chr((($num >> 6) & 63) + 128).chr(($num & 63) + 128);
    }
    if ($num < 2097152) {
      return chr(($num >> 18) + 240).
        chr((($num >> 12) & 63) + 128).
        chr((($num >> 6) & 63) + 128).
        chr(($num & 63) + 128);
    }
    return '';
  }

  /**
  * This method converts an iso date (with time) to a unix timestamp.
  *
  * @param string $date iso date as YYYY-MM-DD HH:MM
  * @param string $timezone whether to use local time or GMT
  * @return integer $result unix timestamp representation of the given iso date
  *
  * @author David Rekowski <info@papaya-cms.com>
  * @since 2008-07-09
  */
  public static function isoDateTimeToUTS($date, $timezone = 'local') {
    $patternIsoDate = '(([\d]{4})-([\d]{2})-([\d]{2}) ?([\d]{2})?:?([\d]{2})?)';
    $result = FALSE;
    if (preg_match($patternIsoDate, $date, $matches)) {
      switch (strtolower($timezone)) {
      case 'gmt':
        $result = gmmktime(
          $matches[4], $matches[5], 0, $matches[2], $matches[3], $matches[1]
        );
        break;
      default:
      case 'local':
        $result = mktime(
          $matches[4], $matches[5], 0, $matches[2], $matches[3], $matches[1]
        );
      }
    }
    return $result;
  }

  /**
  * This method converts a unix timestamp to its iso date time representation.
  *
  * @param integer $uts unix timestamp
  * @param string $timezone whether to use local time or GMT
  * @return string iso date as YYYY-MM-DD HH:MM
  *
  * @author David Rekowski <info@papaya-cms.com>
  * @since 2008-07-09
  */
  public static function utsToIsoDateTime($uts, $timezone = 'local') {
    switch (strtolower($timezone)) {
    case 'gmt':
      $result = gmdate('Y-m-d H:i', $uts);
      break;
    default:
    case 'local':
      $result = date('Y-m-d H:i', $uts);
      break;
    }
    return $result;
  }

  /**
  * encode a binary string using base32 encoding
  *
  * @param string $bytes
  * @access public
  * @return string
  */
  public static function base32_encode($bytes) {
    return \PapayaUtilStringBase32::encode($bytes);
  }

  /**
  * decode a base32 encoded binary string
  *
  * @param string $encodedString
  * @access public
  * @return string
  */
  public static function base32_decode($encodedString) {
    return \PapayaUtilStringBase32::decode($encodedString);
  }

  /**
  * Read a line from a file handle and parse it for CSV fields.
  * Reimplementation of fgetcsv from PHP except the default for $escape.
  * Because the $escape argument is only available with PHP >= 5.3 .
  *
  * No checking for arguments that make no sense is done.
  * This is probably locale and encoding sensitive.
  * There is some very limited error handling that ensures
  *   that the whole line is matched.
  *
  * @todo add a fallback to the PHP 5.3 builtin if it is faster
  * @link http://php.net/manual/en/function.fgetcsv.php
  * @param resource $handle file to read
  * @param int $length maximum line length
  * @param string $delimiter between values, default is ','
  * @param string $enclosure around values, default is '"'
  * @param string $escape for inside values, default is $enclosure
  * @access public
  * @return array|FALSE
  */
  public static function fgetcsv(
    $handle, $length, $delimiter = ',', $enclosure = '"', $escape = NULL
  ) {
    if (is_null($escape)) {
      $escape = $enclosure;
    }
    $delimiter = preg_quote($delimiter);
    $enclosure = preg_quote($enclosure);
    $escape = preg_quote($escape);
    $prefix = "(?:^)";
    $postfix = "(?:$delimiter|$)";
    $quotedValue = "(?:$enclosure((?:[^$enclosure]|$escape$enclosure)*)$enclosure)";
    $unquotedValue = "([^$delimiter$enclosure]*)";
    $pattern = "($prefix(?:$quotedValue|$unquotedValue)$postfix)";

    $buffer = '';
    $ret = array();
    $offset = 0;
    do {
      $tmpBuffer = fgets($handle, $length);
      if ($tmpBuffer === FALSE) {
        /* most likely EOF, but may be any error
           e.g. the csv may be invalid */
        return FALSE;
      }

      // strip a newline at the end that is not part of any data
      $bufferLength = strlen($tmpBuffer);
      if ("\n" === $tmpBuffer[$bufferLength - 1]) {
        if ("\r" === $tmpBuffer[$bufferLength - 2]) {
          $stripNum = 2;
        } else {
          $stripNum = 1;
        }
        $buffer .= substr(
          $tmpBuffer,
          0,
          $bufferLength - $stripNum
        );
        $stripped = substr(
          $tmpBuffer,
          $bufferLength - $stripNum,
          $bufferLength
        );
      } else {
        $buffer .= $tmpBuffer;
        $stripped = '';
      }
      $bufferLength = strlen($buffer);

      /* no error checking for an invalid pattern,
         that should already result in a notice */
      while (
        1 === preg_match($pattern, substr($buffer, $offset), $matches, PREG_OFFSET_CAPTURE) &&
        $offset < $bufferLength
      ) {
        if (empty($matches[1][0]) && isset($matches[2][0])) {
          $ret[] = $matches[2][0];
        } else {
          $ret[] = preg_replace("($escape(.))", '$1', $matches[1][0]);
        }
        $offset += strlen($matches[0][0]);
      }
      // put the newline back we earlier removed
      $buffer .= $stripped;
      // also get the next line if this line can not be fully consumed
    } while ($offset < $bufferLength);
    return $ret;
  }
}

