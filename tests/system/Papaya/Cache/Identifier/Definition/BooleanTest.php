<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaCacheIdentifierDefinitionBooleanTest extends PapayaTestCase {

  /**
   * @covers PapayaCacheIdentifierDefinitionBoolean
   */
  public function testGetStatusForBooleanReturningTrue() {
    $definition = new PapayaCacheIdentifierDefinitionBoolean(TRUE);
    $this->assertTrue($definition->getStatus());
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionBoolean
   */
  public function testGetStatusForBooleanReturningFalse() {
    $definition = new PapayaCacheIdentifierDefinitionBoolean(FALSE);
    $this->assertFalse($definition->getStatus());
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionBoolean
   */
  public function testGetStatusForCallableReturningTrue() {
    $definition = new PapayaCacheIdentifierDefinitionBoolean(function() { return TRUE; });
    $this->assertTrue($definition->getStatus());
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionBoolean
   */
  public function testGetStatusForCallableReturningFalse() {
    $definition = new PapayaCacheIdentifierDefinitionBoolean(function() { return FALSE; });
    $this->assertFalse($definition->getStatus());
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionBoolean
   */
  public function testGetSources() {
    $definition = new PapayaCacheIdentifierDefinitionBoolean(TRUE);
    $this->assertEquals(
      PapayaCacheIdentifierDefinition::SOURCE_VARIABLES,
      $definition->getSources()
    );
  }
}
