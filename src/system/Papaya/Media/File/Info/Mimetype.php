<?php
class PapayaMediaFileInfoMimetype extends PapayaMediaFileInfo {

  private $_fallbackMimeType = 'application/octet-stream';

  protected function fetchProperties() {
    $mimeType = $this->getMimeType();
    return [
      'mimetype' => $mimeType
    ];
  }

  private function getMimeType() {
    $file = $this->getFile();
    if (!empty($file) && is_file($file)) {
      if (
        function_exists('mime_content_type') &&
        is_callable('mime_content_type')
      ) {
        return mime_content_type($file);
      }
      if (extension_loaded('fileinfo')) {
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($fileInfo, $file);
        finfo_close($fileInfo);
        return $mimeType;
      }

      $fileCommand = $this->papaya()->options->get('PAPAYA_FILE_CMD_PATH', '/usr/bin/file');
      $disabledFunctions = array_flip(
        preg_split('/,\s*/', ini_get('disable_functions'))
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
        escapeshellcmd($fileCommand).' -i -b '.escapeshellarg($file), $mimeType, $null
      );
      if (
        NULL === $mimeType &&
        preg_match('(^([\w-\d]+/[\w-\d]+))', $mimeType, $matches)
      ) {
        return $matches[1];
      }
    }
    return $this->_fallbackMimeType;
  }


  private function execCommand($cmd, &$stdout, &$stderr) {
    $result = FALSE;
    $cachePath = $this->papaya()->options['PAPAYA_PATH_CACHE'];
    $stdoutFile = tempnam($cachePath, "exec");
    $stderrFile = tempnam($cachePath, "exec");
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