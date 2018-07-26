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
* status code return in xml if status is unknown (not set)
*/
define('PAPAYA_MEDIADB_UPLOADSTATUS_UNKNOWN', 'UNKNOWN');
/**
* status code return in xml if extension is not here
*/
define('PAPAYA_MEDIADB_UPLOADSTATUS_NO_EXTENSION', 'NO_EXTENSION');
/**
* status code return in xml if given id is invalid
*/
define('PAPAYA_MEDIADB_UPLOADSTATUS_INVALID_ID', 'INVALID_ID');
/*
* status code return in xml for currently processed uploads
*/
define('PAPAYA_MEDIADB_UPLOADSTATUS_PROGRESS', 'PROGRESS');
/**
* status code return in xml for finished uploads
*/
define('PAPAYA_MEDIADB_UPLOADSTATUS_FINISHED', 'FINISHED');

/**
* Basic class for media db xmlrpc requests
*
* @package Papaya
* @subpackage Media-Database
*/
class base_mediadb_rpc extends base_mediadb {

  /**
   * @var array
   */
  public $info;

  /**
  * executeUploadProgressRPC
  *
  * If the upload progress function is loaded, then attempt to get information
  * from the function for a particular file ID, and then transform it into an
  * XML document which is sent back to the XMLrpc caller.
  *
  * @param string $id unique upload id send from browser
  * @access private
  * @return void
  */
  function executeUploadProgressRPC($id) {
    // check for the upload function
    if (function_exists('uploadprogress_get_info')) {
      //make sure that the id is only in hexadecimal
      if (\PapayaFilterFactory::isGuid($id, FALSE)) {
        $ulInfo = $this->getUploadProgressInfo($id);
         // get information from the file upload
        if ($ulInfo) {
          $this->info[$id] = $ulInfo;
        } else {
          //if it fails
          //output that we're waiting for the upload
          $this->info[$id] = array(
            'status' => PAPAYA_MEDIADB_UPLOADSTATUS_FINISHED,
            'message' => "Processing upload.",
            'percent' => 99,
            'eta' => '??',
            'speed' => $this->formatFileSize(0),
            'upl' => $this->formatFileSize(0),
            'total' => $this->formatFileSize(100),
            'speed_num' => 0,
            'upl_num' => 0,
            'total_num' => 0
          );
        }
      } else {
        // if not then output error message via the XML
        $this->info[0] = array(
          'status' => PAPAYA_MEDIADB_UPLOADSTATUS_INVALID_ID,
          'message' => 'Invalid upload identifier.',
          'percent' => 0,
          'eta' => '??',
          'speed' => $this->formatFileSize(0),
          'upl' => $this->formatFileSize(0),
          'total' => $this->formatFileSize(100),
          'speed_num' => 0,
          'upl_num' => 0,
          'total_num' => 100
        );
      }
    } else {
      $this->info[0] = array(
        'status' => PAPAYA_MEDIADB_UPLOADSTATUS_NO_EXTENSION,
        'error' => 'Upload progress extension is not available.'
      );
    }
    $this->outputXML();
  }

  /**
   * Graps the upload information for file with specified upload id and returns an array
   * with formatted informations
   *
   * @author Michael van Engelshoven <info@papaya-cms.com>
   * @param string $id of upload
   * @return mixed array with informations or FALSE if no upload exists
   */
  function getUploadProgressInfo($id) {
    if ($arrUploadInfo = uploadprogress_get_info($id)) {
      // calculate the percentage, time remaining, etc
      $percentComplete = round(
        $arrUploadInfo['bytes_uploaded'] * 100 / $arrUploadInfo['bytes_total']
      );

      return array(
        'id' => $id,
        'status' => PAPAYA_MEDIADB_UPLOADSTATUS_PROGRESS,
        'message' => 'Uploading, please wait.',
        'percent' => ($percentComplete == 0) ? 1 : $percentComplete,
        'eta' => sprintf(
          '%02d:%02d',
          $arrUploadInfo['est_sec'] / 60,
          $arrUploadInfo['est_sec'] % 60
        ),
        'speed' => $this->formatFileSize($arrUploadInfo['speed_average']),
        'upl' => $this->formatFileSize($arrUploadInfo['bytes_uploaded']),
        'total' => $this->formatFileSize($arrUploadInfo['bytes_total']),
        'speed_num' => (int)$arrUploadInfo['speed_average'],
        'upl_num' => (int)$arrUploadInfo['bytes_uploaded'],
        'total_num' => (int)$arrUploadInfo['bytes_total']
      );
    }
    return FALSE;
  }

  /**
   * output the array containing upload stats as a very simple XML document
   * and exit the script
   *
   * @access private
   * @return void
   */
  function outputXML() {
    // XML headers
    header('Content-Type: text/xml; charset=utf-8');
    // prevent caching by browser
    header('Cache-Control: no-store, no-cache, must-revalidate');
    header('Cache-Control: post-check=0, pre-check=0', FALSE);
    header('Pragma: no-cache');
    echo '<?xml version="1.0" encoding="UTF-8" ?>'.LF;
    echo '<status>'.LF;
    // this allows multiple upload blocks to be implemented in the future
    foreach ($this->info as $id => $infoBlock) {
      echo sprintf(
        '<id value="%s">'.LF,
        papaya_strings::escapeHTMLChars($id)
      );
      foreach ($infoBlock as $key => $value) {
        echo sprintf(
          '<%s>%s</%s>'.LF,
          papaya_strings::escapeHTMLChars($key),
          papaya_strings::escapeHTMLChars($value),
          papaya_strings::escapeHTMLChars($key)
        );
      }
      echo '</id>'.LF;
    }
    echo '</status>'.LF;
    exit;
  }

}
