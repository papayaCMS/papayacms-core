<?php

class PapayaTemplateSimpleExceptionUnexpectedEof extends PapayaTemplateSimpleExceptionParser {

  public function __construct(array $expectedTokens) {
    $this->expectedTokens = $expectedTokens;

    $expectedTokenStrings = array();
    foreach ($expectedTokens as $expectedToken) {
      $expectedTokenStrings[] = PapayaTemplateSimpleScannerToken::getTypeString($expectedToken);
    }

    parent::__construct(
      'Parse error: Unexpected end of file was found while one of '.
      implode(", ", $expectedTokenStrings).' was expected.'
    );
  }
}