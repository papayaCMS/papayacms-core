<?php
/**
* This class provides functionality to load and export CSV data as well as other
* helpful methods around CSV.
*
* Interesting methods:
* <code>
* base_csv::getInstance()  - get an instance of this class
* $csvObj->readCSVFile()  - read a CSV file into an array
* $csvObj->readCSVData()  - read a CSV string into an array
* base_csv::outputCSV()    - output a string as a CSV file for download
* base_csv::escapeForCSV() - escape field content for CSV
* </code>
* See each methods documentation for more details.
*
* @copyright 2002-2007 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya
* @subpackage Core
* @version $Id: base_csv.php 39728 2014-04-07 19:51:21Z weinert $
*/

/**
* This class provides functionality to load and export CSV data as well as other
* helpful methods around CSV.
* @package Papaya
* @subpackage Core
*/
class base_csv extends base_object {

  /**
  * if greater than 0, csv read is denied for data larger than this value
  * @var integer $maxSize maximum size csv read data may be
  */
  var $maxSize = 0;

  /**
  * the last file offset position of a reading operation.
  * @var integer
  */
  var $lastFileOffset = 0;

  /**
   * @var string
   */
  public $lastError;

  /** THIS SECTION CONTAINS METHODS TO READ IN CSV FILES */

  /**
  * This method reads in a CSV file and returns a 2-dimensional array
  *
  * @param string $fileLocation full path to the file
  * @param boolean $firstRowTitles whether the first row contains the titles
  * @param boolean $associative whether the titles in 1st row should be keys
  * @param integer $rows number of lines to process
  * @param integer $offset offset to start processing lines
  * @return mixed $result array of data or FALSE
  */
  function readCSVFile(
    $fileLocation, $firstRowTitles = TRUE, $associative = TRUE, $rows = NULL, $offset = 0
  ) {
    $result = FALSE;
    if ($this->checkCSVFile($fileLocation)) {
      $csvStyle = $this->guessCSVStyle($fileLocation);
      if ($fp = fopen($fileLocation, 'r')) {
        $result = array();
        $c = 0;
        $titles = array();
        if ($firstRowTitles) {
          $row = papaya_strings::fgetcsv(
            $fp, 8192, $csvStyle['seperator'], $csvStyle['delimiter']
          );
          if ($row) {
            foreach ($row as $i => $fieldName) {
              $titles[$i] = $fieldName;
            }
          }
        }
        while (
          $row = papaya_strings::fgetcsv(
            $fp, 8192, $csvStyle['seperator'], $csvStyle['delimiter']
          )
        ) {
          if ($rows === 0 || ($rows > 0 && $c >= $offset + $rows)) {
            // offset + rows exceeded
            break;
          }
          $this->lastFileOffset = ftell($fp);
          if ($c >= $offset) {
            if ($firstRowTitles && $associative) {
              foreach ($row as $i => $content) {
                $result[$c][$titles[$i]] = $content;
              }
            } else {
              foreach ($row as $i => $content) {
                $result[$c][$i] = $content;
              }
            }
          }
          ++$c;
        }
      } else {
        $this->lastError = $this->_gt('Could not open CSV file.');
      }
    }
    return $result;
  }

  /**
   * This method reads in a CSV file and returns a 2-dimensional array
   *
   * @param $csvData
   * @param boolean $firstRowTitles whether the first row contains the titles
   * @param boolean $associative whether the titles in 1st row should be keys
   * @see base_csv::readCSVFile()
   * @return mixed $result array of data or FALSE
   */
  function readCSVData($csvData, $firstRowTitles = TRUE, $associative = TRUE) {
    $cacheId = md5($csvData);
    $directory = $this->papaya()->options['PAPAYA_PATH_CACHE'];
    $tempFileLocation = $directory.'.csvd_'.$cacheId.'.csv';
    if ($fp = fopen($tempFileLocation, 'w')) {
      fwrite($fp, $csvData);
      fclose($fp);
      $result = $this->readCSVFile($tempFileLocation, $firstRowTitles, $associative);
      unlink($tempFileLocation);
      return $result;
    } else {
      fclose($fp);
    }
    return FALSE;
  }

  /**
  * Check for valid csv file
  *
  * @access public
  * @param string $fileLocation complete path to the file
  * @param string $fileName the name of the file
  * @param boolean $isUploaded whether this is an uploaded file
  * @return boolean
  */
  function checkCSVFile($fileLocation, $fileName = '', $isUploaded = FALSE) {
    if (isset($fileLocation) && @file_exists($fileLocation)
        && (!$isUploaded || @is_uploaded_file($fileLocation))) {
      $fileName = ($fileName != '') ? $fileName : basename($fileLocation);
      $fileSize = @filesize($fileLocation);
      if ($fileSize > 0) {
        if (!isset($this->maxSize) ||
            $this->maxSize <= 0 ||
            $fileSize <= $this->maxSize) {
          $mediaDB = base_mediadb::getInstance();
          $mimeType = $mediaDB->guessMimeType($fileLocation);
          if ($mimeType == 'text/csv') {
            return TRUE;
          } elseif ($mimeType == $mediaDB->fallbackMimeType) {
            // mimetype could not be determined, it may be csv though
            return TRUE;
          } else {
            $this->lastError = sprintf(
              $this->_gt('Invalid file type "%s"!'),
              $mimeType
            );
          }
        } else {
          $this->lastError = sprintf(
            $this->_gt('File "%s" is too large.'),
            $fileName
          );
        }
      } else {
        $this->lastError = sprintf($this->_gt('File "%s" is empty.'), $fileName);
      }
    } else {
      $msg = 'File "%s" doesn\'t exist or is no uploaded file although it should be.';
      $this->lastError = sprintf($this->_gt($msg), $fileName);
    }
    return FALSE;
  }

  /**
  * Try to find out which csv seperator and delimiter are used for a file
  *
  * @access private
  * @param string $fileName file location
  * @return array $result contains 'seperator' and 'delimiter'
  */
  function guessCSVStyle($fileName) {
    $result = array(
      'seperator' => ',',
      'delimiter' => '"',
    );
    if ($fp = fopen($fileName, 'r')) {
      fgets($fp, 8096);
      // better take the second line
      $line2 = fgets($fp, 8096);
      $seperator = base_csv::getFirstChar($line2, array(',', ';', "\t"));
      if (isset($seperator) && $seperator != '') {
        $result['seperator'] = $seperator;
      }
      $delimiter = base_csv::getFirstChar($line2, array('"', "'"));
      if (isset($delimiter) && $delimiter != '') {
        $result['delimiter'] = $delimiter;
      }
      fclose($fp);
    }
    return $result;
  }

  /**
  * get that char of a list of character that occurs in a string first
  *
  * @access private
  * @param string $str string to check
  * @param array $chars array of characters
  * @return string $char character that occurs first, otherwise an empty string
  */
  function getFirstChar($str, $chars) {
    foreach ($chars as $char) {
      $position = strpos($str, $char);
      // if string doesn't contain char, 0 is returned -> check if str[0] is char
      if ($position > 0 || $str[0] == $char) {
        $charPos[$position] = $char;
      }
    }
    if (isset($charPos) && is_array($charPos) && count($charPos) > 0) {
      // order chars by position
      ksort($charPos);
      // result is char with lowest position
      $result = (string)array_shift($charPos);
      return $result;
    }
    return '';
  }


  /** THIS SECTION CONTAINS METHODS TO OUTPUT CSV FILES */

  /**
  * This method outputs a string as a CSV file
  *
  * @access public
  * @param string $csvData the CSV data as a string
  * @param string $fileName name of the file after download
  */
  public static function outputCSV($csvData, $fileName) {
    $csv = new base_csv();
    $csv->outputExportHeaders($fileName, strlen($csvData));
    print($csvData);
    exit;
  }

  /**
  * This method sends the content-type and content-disposition header for CSV
  *
  * @access private
  * @param string $fileName name of the file after download
  * @param integer $size size of the file
  */
  function outputExportHeaders($fileName, $size = NULL) {
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
      $agentStr = '';
    } else {
      $agentStr = strtolower($_SERVER['HTTP_USER_AGENT']);
    }
    if (strpos($agentStr, 'opera') !== FALSE) {
      $agent = 'OPERA';
    } elseif (strpos($agentStr, 'msie') !== FALSE) {
      $agent = 'IE';
    } else {
      $agent = 'STD';
    }
    $mimeType = ($agent == 'IE' || $agent == 'OPERA')
      ? 'application/octetstream'
      : 'application/octet-stream';
    $fileName = str_replace(array('"', '\\'), array('\\"', '\\\\'), $fileName);
    if ($agent == 'IE') {
      header('Content-Disposition: inline; filename="'.$fileName.'"');
    } else {
      header('Content-Disposition: attachment; filename="'.$fileName.'"');
    }
    header('Content-type: '.$mimeType);
    if ($size) {
      header('Content-Length: '.$size);
    }
  }

  /**
  * This method escapes a string for CSV to be used as a field value.
  *
  * It escapes the string if it contains the delimiter, seperator or whitespace
  * characters like TAB, CR, LF or space ' '. The delimiter is doubled as
  * RFC 4180 2.7 indicates. Technically this method allows random values as
  * seperator and delimiter, though '"' or "'" as delimiters and ',' or ';' or
  * TAB as seperators are strongly recommended.
  *
  * @param string $str string to escape for CSV (field value)
  * @param string $delimiter string to delimit csv fields, default is '"'
  * @param string $seperator string to separate csv fields, default is ','
  * @access public
  * @return string $str escaped string
  */
  function escapeForCSV($str, $delimiter = '"', $seperator = ',') {
    $escapeSymbols = array($delimiter, $seperator, "\n", "\t", "\r", ' ');
    $needsEscaping = FALSE;
    foreach ($escapeSymbols as $symbol) {
      if (papaya_strings::strpos($str, $symbol) !== FALSE) {
        $needsEscaping = TRUE;
        break;
      }
    }
    if ($needsEscaping) {
      $str = $delimiter.str_replace($delimiter, $delimiter.$delimiter, $str).$delimiter;
    }
    return $str;
  }

}

