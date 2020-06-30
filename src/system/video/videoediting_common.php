<?php
/**
* Basic interface class for video editing.
*
* note: escapeshellcmd, escapeshellarg and proc_open many not be disabled by
*       disable_functions for this to work.
*
* @copyright 2002-2009 by papaya Software GmbH - All rights reserved.
* @link http://www.papaya-cms.com/
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
*
* You can redistribute and/or modify this script under the terms of the GNU General Public
* License (GPL) version 2, provided that the copyright and license notes, including these
* lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
* FOR A PARTICULAR PURPOSE.
*
* @package Papaya-Library
* @subpackage Video
* @version $Id: videoediting_common.php 39635 2014-03-19 17:54:43Z weinert $
*/

/**
* Basic class for video editing
*
* @package Papaya-Library
* @subpackage Video
*/
class videoediting_common {

  /**
  * location of encoder binary
  * @var string
  */
  var $encoder = '';

  /**
   * @var array
   */
  public $formats = array();

  /**
   * @var array
   */
  public $codecs = array();


  /**
   * This method cuts a section from an input stream and saves it to a new file.
   *
   * @param string $inFile location of input file
   * @param string $outFile location of output file
   * @param int $start start time in seconds (e.g. 12.125)
   * @param int $end duration of clipping
   */
  function clip($inFile, $outFile, $start, $end) {
  }

  /**
  * This method combines two clips into one
  *
  * @param array $inFiles list of input files
  * @param string $outFile location of output file
  */
  function combine($inFiles, $outFile) {
  }

  /**
  * This method crops a media stream, e.g. to remove black borders or change ratio
  *
  * @param string $inFile location of input file
  * @param string $outFile location of output file
  * @param integer $top number of pixels to remove from the top
  * @param integer $right number of pixels to remove from the right
  * @param integer $bottom number of pixels to remove from the bottom
  * @param integer $left number of pixels to remove from the left
  */
  function crop($inFile, $outFile, $top, $right, $bottom, $left) {
  }

  /**
  * This method padds a media stream, e.g. to add black borders to change ratio
  *
  * @param string $inFile location of input file
  * @param string $outFile location of output file
  * @param integer $top number of pixels to add to the top
  * @param integer $right number of pixels to add to the right
  * @param integer $bottom number of pixels to add to the bottom
  * @param integer $left number of pixels to add to the left
  */
  function pad($inFile, $outFile, $top, $right, $bottom, $left) {
  }

  /**
  * This method scales a media stream, e.g. to change dimensions from 1024x768 to 640x480
  *
  * @param string $inFile location of input file
  * @param string $outFile location of output file
  * @param integer $width target width of video
  * @param integer $height target height of video
  */
  function scale($inFile, $outFile, $width, $height) {
  }

  /**
  * This method creates an index for an flv file
  *
  * @param string $inFile location of input file
  * @param string $outFile location of output file (indexed)
  */
  function reindexFlv($inFile, $outFile = NULL) {
  }

  /**
  * This method executes a shell command in a presumably secure way.
  *
  * Derived from php manual comments on shell_exec
  * Note! For some reason ffmpeg seems to write stuff to stderr instead of stdout
  *
  * @param string $cmd the full command to execute
  * @param string $stdout points to variable that will be filled with stdout
  * @param string $stderr points to variable that will be filled with stderr
  * @return integer $result presumably contains return value of command execution
  */
  function execCmd($cmd, &$stdout, &$stderr) {
    $result = FALSE;
    $stdoutFile = tempnam(\Papaya\Configuration\CMS::PATH_CACHE, "exec");
    $stderrFile = tempnam(\Papaya\Configuration\CMS::PATH_CACHE, "exec");
    $descriptorSpec = array(
      0 => array("pipe", "r"),
      1 => array("file", $stdoutFile, "w"),
      2 => array("file", $stderrFile, "w")
    );
    $procRes = proc_open($cmd, $descriptorSpec, $pipes);

    if (is_resource($procRes)) {
      fclose($pipes[0]);

      $result = proc_close($procRes);
      $stdout = file_get_contents($stdoutFile);
      $stderr = file_get_contents($stderrFile);
    }

    unlink($stdoutFile);
    unlink($stderrFile);
    return $result;
  }

}
