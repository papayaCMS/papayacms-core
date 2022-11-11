<?php

namespace Papaya\Template\Engine\XSLT {

  class XpathValue {

    /**
     * @var string
     */
    private $_value;

    public function __construct(string $value) {
      $this->_value = $value;
    }

    public function __toString() {
      $string = str_replace("\x00", '', $this->_value);
      $hasSingleQuote = FALSE !== strpos($string, "'");
      if ($hasSingleQuote) {
        $hasDoubleQuote = FALSE !== strpos($string, '"');
        if ($hasDoubleQuote) {
          $result = '';
          preg_match_all('("[^\']*|[^"]+)', $string, $matches);
          foreach ($matches[0] as $part) {
            $quoteChar = 0 === strpos($part, '"') ? "'" : '"';
            $result .= ', '.$quoteChar.$part.$quoteChar;
          }
          return 'concat('.substr($result, 2).')';
        }
        return '"'.$string.'"';
      }
      return "'".$string."'";
    }
  }
}
