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

use Papaya\Application\Cms;

/**
* Deliver binary content
*
* @package Papaya
* @subpackage Core
*/
class papaya_file_delivery {

  /**
  * output a file and force the download
  *
  * @param $localFileName
  * @param $data
  * @access public
  * @return boolean
  */
  public static function outputDownload($localFileName, $data) {
    return papaya_file_delivery::_outputFileData($localFileName, $data, TRUE);
  }

  /**
  * output a file
  *
  * @param string $localFileName
  * @param array $data
  * @access public
  * @return boolean
  */
  public static function outputFile($localFileName, $data) {
    return papaya_file_delivery::_outputFileData($localFileName, $data, FALSE);
  }


  /**
  * Get user agent
  *
  * @access public
  * @return string
  */
  public static function _getUserAgent() {
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
      $agentString = '';
    } else {
      $agentString = strtolower($_SERVER["HTTP_USER_AGENT"]);
    }
    if (strpos($agentString, 'opera') !== FALSE) {
      return 'OPERA';
    } elseif (strpos($agentString, 'msie') !== FALSE) {
      return 'IE';
    } else {
      return 'STD';
    }
  }

  /**
  * get microtime as float number
  *
  * @access private
  * @return float
  */
  public static function _microtimeFloat() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
  }

  /**
  * Output last modified and cache time headers
  * @param string $localFileName
  * @param string $data
  * @return void
  */
  public static function _outputTimeHeaders($localFileName, $data) {
    if (isset($data['file_date'])) {
      $lastModified = $data['file_date'];
    } else {
      $lastModified = filemtime($localFileName);
    }
    //send modified and expires
    header('Last-modified: '.gmdate('D, d M Y H:i:s', $lastModified).' GMT');
    if (defined('PAPAYA_CACHE_TIME_OUTPUT') && PAPAYA_CACHE_TIME_OUTPUT) {
      $outputCacheTime = (int)PAPAYA_CACHE_TIME_OUTPUT;
    } else {
      $outputCacheTime = 0;
    }
    if (defined('PAPAYA_CACHE_TIME_FILES') && PAPAYA_CACHE_TIME_FILES > $outputCacheTime) {
      $expires = (int)PAPAYA_CACHE_TIME_FILES;
    } else {
      $expires = $outputCacheTime;
    }
    header('Expires: '.gmdate('D, d M Y H:i:s', time() + $expires).' GMT');
    header(
      sprintf(
        'Cache-Control: private, max-age=%d, pre-check=%d, no-transform',
        $expires,
        $expires
      )
    );
    header('Pragma: ');
  }

  /**
  * Escape bad chars in file name for content-disposition header
  *
  * @param $fileName
  * @access public
  * @return string
  */
  public static function _escapeFileName($fileName) {
    $result = str_replace(array('\\', '"'), array('\\\\', '\\"'), $fileName);
    return $result;
  }

  /**
  * validate if file is available and readable
  *
  * @param string $fileName
  * @return boolean
  */
  public static function validateFile($fileName) {
    if (file_exists($fileName) && is_file($fileName) && is_readable($fileName)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Send the binary data to the client
   *
   * @param string $localFileName
   * @param array $data
   * @param boolean $forceDownload optional
   * @return bool
   */
  public static function _outputFileData($localFileName, $data, $forceDownload = FALSE) {
    if (self::validateFile($localFileName)) {
      //close session if it is still open
      papaya_file_delivery::_closeSession();

      //set some default
      $fileOffset = 0; //current byte offset
      $shapeRequest = FALSE; // do not limit bandwidth
      $shapeLimitRate = 81920; //default bandwidth limit
      $shapeLimitStart = 1048576;  //default bandwidth limit offset
      $shapeRateMinimum = 1024; // 1 KByte/s minimum

      //check for get paremeters that define offsets for flv videos
      $startOffset = 0;
      if (isset($_GET['start']) && $_GET['start'] > 0) {
        $startOffset = (int)$_GET['start'];
      }
      if (isset($_GET['position']) && $_GET['position'] > 0) {
        $startOffset = (int)$_GET['position'];
      }
      if (isset($_GET['pos']) && $_GET['pos'] > 0) {
        $startOffset = (int)$_GET['pos'];
      }
      $handleFlvOffset = FALSE;
      if ($data['mimetype'] == 'video/x-flv' && $startOffset > 0) {
        $handleFlvOffset = TRUE;
      }

      //send modified and expires
      papaya_file_delivery::_outputTimeHeaders($localFileName, $data);

      if ($forceDownload) {
        // get user agent
        $agent = papaya_file_delivery::_getUserAgent();
        // set download mime type header
        $mimeType = ($agent == 'IE' || $agent == 'OPERA') ?
          'application/octetstream' : 'application/octet-stream';
        // send a nice filename to the client
        header(
          'Content-Disposition: attachment; filename="'.
          papaya_file_delivery::_escapeFileName($data['file_name']).'"'
        );
      } elseif (TRUE != $handleFlvOffset && !empty($data['url'])) {
        header('Location: '.$data['url']);
        exit;
      } else {
        // use file mime type
        $mimeType = $data['mimetype'];
        // use inline if you don't want to force FF to download the file
        header(
          'Content-Disposition: inline; filename="'.
          papaya_file_delivery::_escapeFileName($data['file_name']).'"'
        );
      }

      //check shaping options for mime type or all
      if (isset($data['shaping']) && $data['shaping']) {
        $shapeRequest = (bool)$data['shaping'];
        if (isset($data['shaping_limit']) && $data['shaping_limit'] >= $shapeRateMinimum) {
          $shapeLimitRate = (int)$data['shaping_limit'];
        } elseif (defined('PAPAYA_BANDWIDTH_SHAPING_LIMIT') &&
                  PAPAYA_BANDWIDTH_SHAPING_LIMIT >= $shapeRateMinimum) {
          $shapeLimitRate = (int)PAPAYA_BANDWIDTH_SHAPING_LIMIT;
        }
        if (isset($data['shaping_offset']) && $data['shaping_offset'] >= 0) {
          $shapeLimitStart = (int)$data['shaping_offset'];
        } elseif (defined('PAPAYA_BANDWIDTH_SHAPING_OFFSET') &&
                  PAPAYA_BANDWIDTH_SHAPING_OFFSET >= 0) {
          $shapeLimitStart = (int)PAPAYA_BANDWIDTH_SHAPING_OFFSET;
        }
      } elseif (defined('PAPAYA_BANDWIDTH_SHAPING')) {
        $shapeRequest = (bool)PAPAYA_BANDWIDTH_SHAPING;
        if (defined('PAPAYA_BANDWIDTH_SHAPING_LIMIT') &&
            PAPAYA_BANDWIDTH_SHAPING_LIMIT >= $shapeRateMinimum) {
          $shapeLimitRate = (int)PAPAYA_BANDWIDTH_SHAPING_LIMIT;
        }
        if (defined('PAPAYA_BANDWIDTH_SHAPING_OFFSET') &&
            PAPAYA_BANDWIDTH_SHAPING_OFFSET >= 0) {
          $shapeLimitStart = (int)PAPAYA_BANDWIDTH_SHAPING_OFFSET;
        }
      }

      $useSendFile = FALSE;
      //send mime type
      header('Content-type: '.$mimeType);
      if (TRUE == $handleFlvOffset) {
        // special handling for flv streams that with byte offsets
        // send magic flash video header "FLV01050000000900000000"
        $flashHeader = 'FLV'.pack('CCNN', 1, 5, 9, 0);
        $fileOffset = $startOffset;
        header('Content-length: '.($data['file_size'] - $startOffset + strlen($flashHeader)));
        print($flashHeader);
      } elseif (isset($data['range_support']) && $data['range_support'] == 1) {
        // check, whether the mimetype supports range header
        header('Accept-Ranges: bytes');
        if (defined('PAPAYA_SENDFILE_HEADER') &&
            PAPAYA_SENDFILE_HEADER &&
            (!$shapeRequest)) {
          // we use X-Sendfile so the webserver will do all this
          $useSendFile = TRUE;
        } elseif (isset($_SERVER['HTTP_RANGE'])) {
          // check whether the client requested a range (e.g. 'Range: bytes=2999-')
          list(, $range) = explode('=', $_SERVER['HTTP_RANGE']);
          $fileOffset = (int)str_replace('-', '', $range);
          header('HTTP/1.1 206 Partial Content');
          // tell the client, how large the current chunk is
          header('Content-length: '.($data['file_size'] - $fileOffset));
          // tell the client, which chunk we will give him
          header(
            sprintf(
              'Content-Range: bytes %d-%d/%d',
              $fileOffset,
              $data['file_size'] - 1,
              $data['file_size']
            )
          );
        } else {
          header('Content-length: '.$data['file_size']);
          header(
            sprintf('Content-Range: bytes 0-%d/%d', $data['file_size'] - 1, $data['file_size'])
          );
        }
      } else {
        //the file does not accept range headers
        header('Accept-Ranges: none');
        header('Content-length: '.$data['file_size']);
      }

      if ($shapeRequest) {
        //initialize start time
        $timeStart = papaya_file_delivery::_microtimeFloat();
        //send bytes in each loop
        $bytesPerStep = 256;

        //check better sleep function
        if (function_exists('usleep')) {
          $sleepFunction = 'usleep';
          $sleepTime = 500;
        } else {
          $sleepFunction = 'sleep';
          $sleepTime = 1;
        }
      } else {
        $timeStart = 0;
        $bytesPerStep = 1024;
        $sleepFunction = 'sleep';
        $sleepTime = 1;
      }

      if ($fileOffset == 0 && $useSendFile &&
          ((!$shapeRequest) || filesize($localFileName) <= $shapeLimitStart)) {
        //use X-Sendfile HTTP Header
        header('X-Sendfile: '.$localFileName);
        exit;
      } else {
        //own file handling
        if ($fh = @fopen($localFileName, 'r')) {
          if ($fileOffset > 0) {
            //seek file to start position
            fseek($fh, $fileOffset);
          }
          $bytesSend = 0;
          while (!feof($fh) && (connection_status() == 0)) {
            print fread($fh, $bytesPerStep);
            flush();

            //is we shape the request
            if ($shapeRequest) {
              //add the send byte count
              $bytesSend += $bytesPerStep;
              //is it above the offset (bytes send without any limit)
              if ($bytesSend > $shapeLimitStart) {
                //get time difference to starting time
                $timeDiff = papaya_file_delivery::_microtimeFloat() - $timeStart;
                //calculate bytes per second
                $rate = ($bytesSend - $shapeLimitStart) / $timeDiff;
                // to fast?
                if ($rate > $shapeLimitRate) {
                  //sleep a moment
                  $sleepFunction($sleepTime);
                }
              }
            }
          }
          fclose($fh);
          exit;
        }
      }
    }
    return FALSE;
  }

  /**
  * close the current session
  *
  * @access private
  */
  public static function _closeSession() {
    /** @var Cms $application */
    $application = PapayaApplication::getInstance();
    $application->session->close();
  }
}

