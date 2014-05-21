<?php

class PapayaTemplateSimpleExceptionUnexpectedToken extends PapayaTemplateSimpleExceptionParser {


  /**
  * The token encountered during the scan.
  *
  * This is the token object which was not expected to be found at the given
  * position.
  *
  * @var PapayaTemplateSimpleScannerToken
  */
  public $encounteredToken;

  public function __construct($encounteredToken, array $expectedTokens) {
    $this->encounteredToken = $encounteredToken;
    $this->expectedTokens = $expectedTokens;

    $expectedTokenStrings = array();
    foreach ($expectedTokens as $expectedToken) {
      $expectedTokenStrings[] = PapayaTemplateSimpleScannerToken::getTypeString($expectedToken);
    }

    parent::__construct(
      'Parse error: Found '.(string)$encounteredToken.
      ' while one of '.implode(", ", $expectedTokenStrings).' was expected.'
    );
  }
}