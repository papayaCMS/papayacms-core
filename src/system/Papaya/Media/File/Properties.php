<?php
class PapayaMediaFileProperties extends PapayaMediaFileInfo {

  private $_fetchers;

  protected function fetchProperties() {
    $file = $this->getFile();
    if (\file_exists($file) && \is_file($file) && \is_readable($file)) {
      $properties = [];
      foreach ($this->fetchers() as $fetcher) {
        if ($fetcher->isSupported($properties)) {
          /** @noinspection SlowArrayOperationsInLoopInspection */
          $properties = \array_merge($properties, \iterator_to_array($fetcher));
        }
      }
      return $properties;
    }
    return [
      'is_valid' => FALSE
    ];
  }

  public function fetchers(PapayaMediaFileInfo ...$fetchers) {
    if (\count($fetchers) > 0) {
      $this->_fetchers = $fetchers;
    } elseif (NULL === $this->_fetchers) {
      $file = $this->getFile();
      $originalName = $this->getOriginalFileName();
      $this->_fetchers = [
        new PapayaMediaFileInfoBasic($file, $originalName),
        new PapayaMediaFileInfoMimetype($file, $originalName),
        new PapayaMediaFileInfoImage($file, $originalName),
        new PapayaMediaFileInfoSvg($file, $originalName),
      ];
    }
    return $this->_fetchers;
  }
}