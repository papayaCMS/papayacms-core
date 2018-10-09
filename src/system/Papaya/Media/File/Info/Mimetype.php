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

/** @noinspection PhpComposerExtensionStubsInspection */
namespace Papaya\Media\File\Info;

use Papaya\Media;

class Mimetype extends Media\File\Info {
  private $_fallbackMimeType = 'application/octet-stream';

  protected function fetchProperties() {
    $mimeType = $this->getMimeType();
    return [
      'mimetype' => $mimeType
    ];
  }

  private function getMimeType() {
    $file = $this->getFile();
    if (!empty($file) && \is_file($file)) {
      if (
        \function_exists('mime_content_type') &&
        \is_callable('mime_content_type')
      ) {
        return \mime_content_type($file);
      }
      if (\extension_loaded('fileinfo')) {
        $fileInfo = \finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = \finfo_file($fileInfo, $file);
        \finfo_close($fileInfo);
        return $mimeType;
      }

      $fileCommand = $this->papaya()->options->get('PAPAYA_FILE_CMD_PATH', '/usr/bin/file');
      $disabledFunctions = \array_flip(
        \preg_split('/,\s*/', \ini_get('disable_functions'))
      );
      $neededFunctions = ['proc_open', 'escapeshellcmd', 'escapeshellarg'];
      foreach ($neededFunctions as $neededFunction) {
        if (isset($disabledFunctions[$neededFunction])) {
          return $this->_fallbackMimeType;
        }
      }

      $mimeType = NULL;
      $null = NULL;
      $this->execCommand(
        \escapeshellcmd($fileCommand).' -i -b '.\escapeshellarg($file), $mimeType, $null
      );
      if (
        NULL === $mimeType &&
        \preg_match('(^([\w-\d]+/[\w-\d]+))', $mimeType, $matches)
      ) {
        return $matches[1];
      }
    }
    return $this->_fallbackMimeType;
  }

  private function execCommand($cmd, &$stdout, &$stderr) {
    $result = FALSE;
    $cachePath = $this->papaya()->options['PAPAYA_PATH_CACHE'];
    $stdoutFile = \tempnam($cachePath, 'exec');
    $stderrFile = \tempnam($cachePath, 'exec');
    $descriptorSpec = [
      0 => ['pipe', 'r'],
      1 => ['file', $stdoutFile, 'w'],
      2 => ['file', $stderrFile, 'w']
    ];
    $procRes = \proc_open($cmd, $descriptorSpec, $pipes);

    if (\is_resource($procRes)) {
      \fclose($pipes[0]);

      $result = \proc_close($procRes);
      $stdout = \file_get_contents($stdoutFile);
      $stderr = \file_get_contents($stderrFile);
    }

    \unlink($stdoutFile);
    \unlink($stderrFile);
    return $result;
  }
}
