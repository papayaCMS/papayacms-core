<?php

namespace Papaya\TestFramework {

  class DOMFixtures {

    private $_testCase = NULL;

    public function __construct(\Papaya\TestFramework\TestCase $testCase) {
      $this->_testCase = $testCase;
    }

    public function createXpathFromFile($filename, array $namespaces = []) {
      $dom = new \Papaya\XML\Document();
      $dom->load($filename);
      $xpath = $dom->xpath();
      foreach ($namespaces as $prefix => $namespace) {
        $xpath->registerNamespace($prefix, $namespace);
      }
      return $dom->xpath();
    }

    public function createXpathFromString($xmlString, array $namespaces = []) {
      $dom = new \Papaya\XML\Document();
      $dom->loadXml($xmlString);
      $xpath = $dom->xpath();
      foreach ($namespaces as $prefix => $namespace) {
        $xpath->registerNamespace($prefix, $namespace);
      }
      return $dom->xpath();
    }
  }
}
