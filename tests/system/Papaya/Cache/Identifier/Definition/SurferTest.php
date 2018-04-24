<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaCacheIdentifierDefinitionSurferTest extends PapayaTestCase {

  /**
   * @covers PapayaCacheIdentifierDefinitionSurfer
   */
  public function testGetStatus() {
    $surfer = new stdClass();
    $surfer->isValid = TRUE;
    $surfer->id = '012345678901234567890123456789ab';
    $definition = new PapayaCacheIdentifierDefinitionSurfer();
    $definition->papaya(
      $this->mockPapaya()->application(
        array(
          'surfer' => $surfer
        )
      )
    );
    $this->assertEquals(
      array(PapayaCacheIdentifierDefinitionSurfer::class => '012345678901234567890123456789ab'),
      $definition->getStatus()
    );
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionSurfer
   */
  public function testGetStatusForPreviewExpectingFalse() {
    $surfer = new stdClass();
    $surfer->isValid = FALSE;
    $definition = new PapayaCacheIdentifierDefinitionSurfer();
    $definition->papaya(
      $this->mockPapaya()->application(
        array(
          'surfer' => $surfer
        )
      )
    );
    $this->assertTrue($definition->getStatus());
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionSurfer
   */
  public function testGetSources() {
    $definition = new PapayaCacheIdentifierDefinitionSurfer();
    $this->assertEquals(
      PapayaCacheIdentifierDefinition::SOURCE_REQUEST,
      $definition->getSources()
    );
  }
}
