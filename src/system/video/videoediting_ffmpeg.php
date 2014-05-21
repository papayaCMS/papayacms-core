<?php
/**
* ffmpeg class for video editing and information retrieving
*
* note: There are some hardcoded command locations, which may have to be overridden
*       in the instantiated object (encoder, ruby, cat, flvtool2).
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
* @version $Id: videoediting_ffmpeg.php 39635 2014-03-19 17:54:43Z weinert $
*/

/**
* ffmpeg class for video editing
*
* @package Papaya-Library
* @subpackage Video
*/
class videoediting_ffmpeg extends videoediting_common {

  /**
  * Location of ffmpeg binary
  * @var string
  */
  var $encoder = '/usr/bin/ffmpeg';

  /**
  * Location of ruby binary (used with flvtool for indexing)
  * @var string
  */
  var $ruby = '/usr/bin/ruby';

  /**
  * Location of cat binary
  * @var string
  */
  var $cat = '/bin/cat';

  /**
  * Location of flvtool ruby script
  * @var string
  */
  var $flvtool2 = '/var/www/lib/flvtool2/lib/flvtool2.rb';

  /**
  * Video bitrate to use
  * @var integer
  */
  var $videoBitrate = 200;

  /**
  * Audio bitrate to use
  * @var integer
  */
  var $audioBitrate = 44100;

  /**
  * regular expressions to parse ffmpeg output to get information flv file
  * @var array
  */
  var $infoRegex = array(
    // ffmpeg version string
    'version' => '~FFmpeg version (.*), Copyright .*~',
    // compile configuration
    'config' => '~configuration: (.*)~',
    // libavutil version string
    'avutil' => '~libavutil version: (.*)~',
    // libavcodec version string
    'avcodec' => '~libavcodec version: (.*)~',
    // libavformat version string
    'avformat' => '~libavformat version: (.*)~',
    // build date and compiler (gcc) version
    'build' => '~built on (.*), gcc: (.*)~',
    // input stream information
    'input' => "~Input #(.*), (.*), .* '(.*)'~",
    // input stream duration
    'time' => '~Duration: (.*), start: (.*), bitrate: (.*)~',
    // output stream information
    'output' => "~Output #(.*), (.*), .* '(.*)'~",
    // stream elements either input or output (audio, video, etc)
    'stream' => '~Stream #(.*): (.*): (.*)~',
    // stream mapping input to output
    'mapping' => '~Stream #(.*) -> #(.*)~',
    // progress of transcoding
    'progress' => '~frame=(.*) q=(.*) Lsize=(.*) time=(.*) bitrate=(.*)~',
    // result of transcoding
    'result' => '~video:(.*) audio:(.*) global headers:(.*) muxing overhead (.*)~',
   );

  /**
  * holds result of parseCmdResult()
  * @var array
  */
  var $info = array();
  /**
   * This method cuts a section from an input stream and saves it to a new file.
   *
   * @param string $inFile location of input file
   * @param string $outFile location of output file
   * @param string $start start time in seconds (e.g. 12.125)
   * @param string $duration duration of clipping
   * @return bool|void
   */
  function clip($inFile, $outFile, $start, $duration) {
    if ($inFile == $outFile) {
      // cannot write to source file
      return FALSE;
    }
    // ffmpeg -i $inFile -ss $start -t $duration $outFile
    $cmd = sprintf(
      '%s -i %s -sameq -ss %s -t %s %s',
      escapeshellcmd($this->encoder),
      escapeshellarg($inFile),
      escapeshellarg($start),
      escapeshellarg($duration),
      escapeshellarg($outFile)
    );
    $this->execCmd($cmd, $null, $info);
    // base_object::debug($cmd, $null, $info);
    return TRUE;
  }

  /**
  * not yet implemented: This method combines two clips into one
  *
  * @param array $inFiles list of input files
  * @param string $outFile location of output file
  */
  function combine($inFiles, $outFile) {
  }

  /**
   * This method crops a media stream, e.g. to remove black borders or change ratio
   *
   * Restrictions apply, each dimension must be a mutiple of 8 (IIRC)
   *
   * @param string $inFile location of input file
   * @param string $outFile location of output file
   * @param integer $top number of pixels to remove from the top
   * @param integer $right number of pixels to remove from the right
   * @param integer $bottom number of pixels to remove from the bottom
   * @param integer $left number of pixels to remove from the left
   * @return bool|void
   */
  function crop($inFile, $outFile, $top, $right, $bottom, $left) {
    if ($inFile == $outFile) {
      // cannot write to source file
      return FALSE;
    }
    $cmd = sprintf(
      '%s -i %s -sameq -croptop %d -cropright %d -cropbottom %d -cropleft %d %s',
      escapeshellcmd($this->encoder),
      escapeshellarg($inFile),
      escapeshellcmd($top),
      escapeshellcmd($right),
      escapeshellcmd($bottom),
      escapeshellcmd($left),
      escapeshellcmd($outFile)
    );
    $this->execCmd($cmd, $null, $info);
    // base_object::debug($cmd, $null, $info);
    return TRUE;
  }

  /**
   * This method padds a media stream, e.g. to add black borders to change ratio
   *
   * Restrictions apply, each dimension must be a mutiple of 8 (IIRC)
   *
   * @param string $inFile location of input file
   * @param string $outFile location of output file
   * @param integer $top number of pixels to add to the top
   * @param integer $right number of pixels to add to the right
   * @param integer $bottom number of pixels to add to the bottom
   * @param integer $left number of pixels to add to the left
   * @return bool|void
   */
  function pad($inFile, $outFile, $top, $right, $bottom, $left) {
    if ($inFile == $outFile) {
      // cannot write to source file
      return FALSE;
    }
    $cmd = sprintf(
      '%s -i %s -sameq -padtop %d -padright %d -padbottom %d -padleft %d %s',
      escapeshellcmd($this->encoder),
      escapeshellarg($inFile),
      escapeshellcmd($top),
      escapeshellcmd($right),
      escapeshellcmd($bottom),
      escapeshellcmd($left),
      escapeshellcmd($outFile)
    );
    $this->execCmd($cmd, $null, $info);
    return TRUE;
  }

  /**
   * This method scales a media stream, e.g. to change dimensions from 1024x768 to 640x480
   *
   * Restrictions apply, each dimension must be a mutiple of 8 (IIRC)
   *
   * @param string $inFile location of input file
   * @param string $outFile location of output file
   * @param integer $width target width of video
   * @param integer $height target height of video
   * @return bool|void
   */
  function scale($inFile, $outFile, $width, $height) {
    if ($inFile == $outFile) {
      // cannot write to source file
      return FALSE;
    }
    $cmd = sprintf(
      '%s -i %s -sameq -s %dx%d %s',
      escapeshellcmd($this->encoder),
      escapeshellarg($inFile),
      escapeshellcmd($width),
      escapeshellcmd($height),
      escapeshellcmd($outFile)
    );
    $this->execCmd($cmd, $null, $info);
    return TRUE;
  }

  /**
   * This method creates an index for an flv file using flvtool2
   *
   * @param string $inFile location of input file
   * @param string $outFile location of output file (indexed)
   * @return bool|void
   */
  function reindexFlv($inFile, $outFile = NULL) {
    if ($inFile == $outFile) {
      // cannot write to source file
      return FALSE;
    }
    $overwrite = FALSE;
    if ($outFile == '') {
      $outFile = PAPAYA_PATH_CACHE.'/reindex_'.md5(rand(999999999)).'.flv';
      $overwrite = TRUE;
    }
    $cmd = sprintf(
      'cd %s && %s %s | %s %s -U stdin %s',
      escapeshellarg(dirname($this->flvtool2)),
      escapeshellcmd($this->cat),
      escapeshellarg($inFile),
      escapeshellcmd($this->ruby),
      escapeshellarg($this->flvtool2),
      escapeshellarg($outFile)
    );
    $this->execCmd($cmd, $null, $info);
    if ($overwrite) {
      copy($outFile, $inFile);
      unlink($outFile);
    }
    return TRUE;
  }

  /**
  * This method retrieves various information on a media file
  *
  * formerly known as loadVideoInformation
  *
  * @param string $file media file
  * @return array $info information on media file
  */
  function loadMediaInformation($file) {
    unset($this->info);
    // copy a splitsecond from the input file to /dev/null in order to get information
    $cmd = sprintf(
      "%s -i %s -t 0.1 -y -f flv /dev/null",
      escapeshellcmd($this->encoder),
      escapeshellarg($file)
    );
    $this->execCmd($cmd, $null, $stdout);
    $this->info = $this->parseCmdResult($stdout);
    return $this->info;
  }

  /**
  * This method parses the ffmpeg output of a transcoding process.
  *
  * @param string $data output of ffmpeg command execution
  * @return array $result detailed information of ffmpeg run
  */
  function parseCmdResult($data) {
    if (trim($data) == '') {
      return NULL;
    }
    $lines = explode("\n", $data);
    if (!is_array($lines) || count($lines) == 0) {
      return NULL;
    }
    $unknown = array();
    $result = array();
    foreach ($lines as $line) {
      $matched = FALSE;
      foreach ($this->infoRegex as $type => $currentRegex) {
        if (preg_match($currentRegex, $line, $matches)) {
          $matched = TRUE;
          switch ($type) {
          case 'version':
            $result['version'] = $matches[1];
            break;
          case 'config':
            $configOptions = explode('--', $matches[1]);
            foreach ($configOptions as $configOption) {
              if (trim($configOption) != '') {
                $result['config_options'][] = trim($configOption);
              }
            }
            break;
          case 'avutil':
            $result['avutil'] = $matches[1];
            break;
          case 'avcodec':
            $result['avcodec'] = $matches[1];
            break;
          case 'avformat':
            $result['avformat'] = $matches[1];
            break;
          case 'build':
            $result['build']['date'] = $matches[1];
            $result['build']['gcc'] = $matches[2];
            break;
          case 'input':
            $result['input']['id'] = $matches[1];
            $result['input']['format'] = $matches[2];
            $result['input']['file'] = $matches[3];
            break;
          case 'time':
            $result['time']['duration'] = $matches[1];
            $result['time']['start'] = $matches[2];
            $result['time']['bitrate'] = $matches[3];
            break;
          case 'output':
            $result['output'] = $matches[1];
            break;
          case 'stream':
            $streamInfo = explode(', ', $matches[3]);
            $matches[3] = $streamInfo;
            if (isset($result['output'])) {
              $streamType = 'output_streams';
            } else {
              $streamType = 'input_streams';
            }
            $result[$streamType][$matches[1]]['type'] = $matches[2];
            foreach ($streamInfo as $info) {
              if (isset($this->formats[$info])) {
                $result[$streamType][$matches[1]]['format'] = $info;
              } elseif (isset($this->codecs[$info])) {
                $result[$streamType][$matches[1]]['codec'] = $info;
              } elseif (preg_match('~([0-9]+)x([0-9]+)~', $info, $sizeMatches)) {
                $result[$streamType][$matches[1]]['width'] = $sizeMatches[1];
                $result[$streamType][$matches[1]]['height'] = $sizeMatches[2];
              } elseif (substr($info, -6) == 'fps(r)') {
                $result[$streamType][$matches[1]]['framerate'] = substr($info, 0, -7);
              } elseif (substr($info, -6) == 'fps(c)') {
                $result[$streamType][$matches[1]]['framerate'] = substr($info, 0, -7);
              } elseif (substr($info, -4) == 'kb/s') {
                $result[$streamType][$matches[1]]['bitrate'] = substr($info, 0, -5);
              } elseif (substr($info, 0, 2) == 'q=') {
                $result[$streamType][$matches[1]]['q'] = substr($info, 2);
              } elseif (substr($info, -2) == 'Hz') {
                $result[$streamType][$matches[1]]['audiorate'] = substr($info, 0, -3);
              }
            }
            break;
          case 'mapping':
            $result['mapping'][$matches[1]] = $matches[2];
            break;
          case 'progress':
            unset($progress);
            $progress['frame'] = trim($matches[1]);
            $progress['q'] = $matches[2];
            $progress['Lsize'] = trim($matches[3]);
            $progress['time'] = $matches[4];
            $progress['bitrate'] = substr($matches[5], 0, -7);
            $result['progress'][] = $progress;
            break;
          case 'result':
            $result['result']['video'] = $matches[1];
            $result['result']['audio'] = $matches[2];
            $result['result']['headers'] = $matches[3];
            $result['result']['muxing'] = $matches[4];
            break;
          }
          break;
        }
      }
      if (!$matched && trim($line) != '') {
        $unknown[] = $line;
      }
    }
    $result['unknown'] = $unknown;
    return $result;
  }

}
