<?php

class PapayaFilterLines implements PapayaFilter {

  /**
   * @var PapayaFilter
   */
  private $_filter;

  public function __construct(PapayaFilter $filter) {
    $this->_filter = $filter;
  }

  public function filter($value) {
    $lines = [];
    foreach ($this->getLines((string)$value) as $line) {
      $line = $this->_filter->filter($line);
      if ($line !== NULL && $line !== '') {
        $lines[] = $line;
      }
    }
    return implode("\n", $lines);
  }

  public function validate($value) {
    foreach ($this->getLines((string)$value) as $line) {
      $this->_filter->validate($line);
    }
  }

  private function getLines($string) {
    if (preg_match_all('(^.+$)m', $string, $matches, PREG_PATTERN_ORDER)) {
      return $matches[0];
    }
    return [];
  }
}