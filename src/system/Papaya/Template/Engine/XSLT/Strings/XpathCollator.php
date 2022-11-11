<?php

namespace Papaya\Template\Engine\XSLT\Strings {

  interface XpathCollator {

    public function __construct(string $uri = NULL);

    public function compare(string $string1, string $string2): int;

    public function getSortKey(string $string): string;
  }
}
