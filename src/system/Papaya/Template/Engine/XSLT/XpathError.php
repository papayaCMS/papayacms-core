<?php

namespace Papaya\Template\Engine\XSLT {

  class XpathError extends \Exception {

    private $_uri;
    private $_context;

    public function __construct(string $uri, string $description = '', $context = NULL) {
      parent::__construct($uri.(trim($description) !== '' ? ', '.$description : ''));
      $this->_uri = $uri;
      $this->_context = $context;
    }

    public function getURI(): string {
      return $this->_uri;
    }

    public function getContext() {
      return $this->_context;
    }
  }
}
