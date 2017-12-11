<?php
class PapayaMediaImageFileProperties extends PapayaMediaFileInfo {

  private $_fetchers;

  protected function fetchProperties() {
    $properties = [];
    foreach ($this->getFetchers() as $fetcher) {
      if ($fetcher->isSupported($properties)) {
        /** @noinspection SlowArrayOperationsInLoopInspection */
        $properties = array_merge($properties, iterator_to_array($fetcher));
      }
    }
    return $properties;
  }

  public function getFetchers() {
    if (NULL === $this->_fetchers) {
      $this->_fetchers = [
        new PapayaMediaFileInfoImage($this->getFileName()),
        new PapayaMediaFileInfoSvg($this->getFileName()),
      ];
    }
    return $this->_fetchers;
  }
}