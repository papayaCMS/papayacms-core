<?php
class PapayaMediaFileInfoBasic extends PapayaMediaFileInfo {

  protected function fetchProperties() {
    $fileName = $this->getFile();
    return array(
      'size' => filesize($fileName)
    );
  }


}