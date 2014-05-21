<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaCacheIdentifierDefinitionCallbackTest extends PapayaTestCase {

  /**
   * @covers PapayaCacheIdentifierDefinitionCallback
   */
  public function testGetStatus() {
    $definition = new PapayaCacheIdentifierDefinitionCallback(array($this, 'callbackReturnString'));
    $this->assertEquals(
      array(
        'PapayaCacheIdentifierDefinitionCallback' => 'success'
      ),
      $definition->getStatus()
    );
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionCallback
   */
  public function testGetStatusExpectingFalse() {
    $definition = new PapayaCacheIdentifierDefinitionCallback(array($this, 'callbackReturnFalse'));
    $this->assertFalse($definition->getStatus());
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionCallback
   */
  public function testGetSources() {
    $definition = new PapayaCacheIdentifierDefinitionCallback(array($this, 'callbackReturnFalse'));
    $this->assertEquals(
      PapayaCacheIdentifierDefinition::SOURCE_VARIABLES,
      $definition->getSources()
    );
  }

  public function callbackReturnString() {
    return 'success';
  }
  public function callbackReturnFalse() {
    return FALSE;
  }
}