<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaCacheIdentifierDefinitionEnvironmentTest extends PapayaTestCase {

  /**
   * @covers PapayaCacheIdentifierDefinitionEnvironment
   */
  public function testGetStatus() {
    $_SERVER['TEST_VARIABLE'] = 'success';
    $definition = new PapayaCacheIdentifierDefinitionEnvironment('TEST_VARIABLE');
    $this->assertEquals(
      array('PapayaCacheIdentifierDefinitionEnvironment' => array('TEST_VARIABLE' => 'success')),
      $definition->getStatus()
    );
    unset($_SERVER['TEST_VARIABLE']);
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionEnvironment
   */
  public function testGetStatusWithUnknownVariableExpectingTrue() {
    $definition = new PapayaCacheIdentifierDefinitionEnvironment('UNKNOWN_TEST_VARIABLE');
    $this->assertTrue(
      $definition->getStatus()
    );
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionEnvironment
   */
  public function testGetSources() {
    $definition = new PapayaCacheIdentifierDefinitionEnvironment('X');
    $this->assertEquals(
      PapayaCacheIdentifierDefinition::SOURCE_REQUEST,
      $definition->getSources()
    );
  }

}