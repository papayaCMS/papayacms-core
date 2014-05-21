<?php

abstract class PapayaTemplateSimpleExceptionParser extends PapayaTemplateSimpleException {

  /**
  * An array of tokens which would have been expected to be found.
  *
  * @var array(PhpCssScannerToken)
  */
  public $expectedTokens = array();
}
