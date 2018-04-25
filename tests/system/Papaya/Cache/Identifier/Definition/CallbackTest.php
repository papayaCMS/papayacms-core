<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaCacheIdentifierDefinitionCallbackTest extends PapayaTestCase {

  /**
   * @covers PapayaCacheIdentifierDefinitionCallback
   */
  public function testGetStatus() {
    $definition = new PapayaCacheIdentifierDefinitionCallback(function() { return 'success'; });
    $this->assertEquals(
      array(
        PapayaCacheIdentifierDefinitionCallback::class => 'success'
      ),
      $definition->getStatus()
    );
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionCallback
   */
  public function testGetStatusExpectingFalse() {
    $definition = new PapayaCacheIdentifierDefinitionCallback(function() { return FALSE; });
    $this->assertFalse($definition->getStatus());
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionCallback
   */
  public function testGetSources() {
    $definition = new PapayaCacheIdentifierDefinitionCallback(function() { return FALSE; });
    $this->assertEquals(
      PapayaCacheIdentifierDefinition::SOURCE_VARIABLES,
      $definition->getSources()
    );
  }
}
