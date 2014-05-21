<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaCacheIdentifierDefinitionGroupTest extends PapayaTestCase {

  /**
   * @covers PapayaCacheIdentifierDefinitionGroup
   */
  public function testGetStatusWithOneDefinitionReturingTrue() {
    $mockDefinition = $this->getMock('PapayaCacheIdentifierDefinition');
    $mockDefinition
      ->expects($this->once())
      ->method('getStatus')
      ->will($this->returnValue(TRUE));
    $definition = new PapayaCacheIdentifierDefinitionGroup($mockDefinition);
    $this->assertTrue($definition->getStatus());
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionGroup
   */
  public function testGetStatusWithTwoDefinitionsReturingFalseSecondNeverCalled() {
    $one = $this->getMock('PapayaCacheIdentifierDefinition');
    $one
      ->expects($this->once())
      ->method('getStatus')
      ->will($this->returnValue(FALSE));
    $two = $this->getMock('PapayaCacheIdentifierDefinition');
    $two
      ->expects($this->never())
      ->method('getStatus');
    $definition = new PapayaCacheIdentifierDefinitionGroup($one, $two);
    $this->assertFalse($definition->getStatus());
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionGroup
   */
  public function testGetStatusWithTwoDefinitionsMergingReturns() {
    $one = $this->getMock('PapayaCacheIdentifierDefinition');
    $one
      ->expects($this->once())
      ->method('getStatus')
      ->will($this->returnValue(array('foo' => '21')));
    $two = $this->getMock('PapayaCacheIdentifierDefinition');
    $two
      ->expects($this->once())
      ->method('getStatus')
      ->will($this->returnValue(array('bar' => '48')));
    $definition = new PapayaCacheIdentifierDefinitionGroup($one, $two);
    $this->assertEquals(
      array(
        'PapayaCacheIdentifierDefinitionGroup' => array(
           array('foo' => '21'), array('bar' => '48')
        )
      ),
      $definition->getStatus()
    );
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionGroup
   * @dataProvider provideSourceExamples
   */
  public function testGetSourcesFromTwoDefinitions($expected, $sourceOne, $sourceTwo) {
    $one = $this->getMock('PapayaCacheIdentifierDefinition');
    $one
      ->expects($this->once())
      ->method('getSources')
      ->will($this->returnValue($sourceOne));
    $two = $this->getMock('PapayaCacheIdentifierDefinition');
    $two
      ->expects($this->once())
      ->method('getSources')
      ->will($this->returnValue($sourceTwo));
    $definition = new PapayaCacheIdentifierDefinitionGroup($one, $two);
    $this->assertEquals(
      $expected,
      $definition->getSources()
    );
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionGroup
   */
  public function testAdd() {
    $one = $this->getMock('PapayaCacheIdentifierDefinition');
    $one
      ->expects($this->once())
      ->method('getStatus')
      ->will($this->returnValue(array('foo' => '21')));
    $two = $this->getMock('PapayaCacheIdentifierDefinition');
    $two
      ->expects($this->once())
      ->method('getStatus')
      ->will($this->returnValue(array('bar' => '48')));
    $definition = new PapayaCacheIdentifierDefinitionGroup();
    $definition->add($one);
    $definition->add($two);
    $this->assertEquals(
      array(
        'PapayaCacheIdentifierDefinitionGroup' => array(
           array('foo' => '21'), array('bar' => '48')
        )
      ),
      $definition->getStatus()
    );
  }

  public static function provideSourceExamples() {
    return array(
      array(
        PapayaCacheIdentifierDefinition::SOURCE_URL,
        PapayaCacheIdentifierDefinition::SOURCE_URL,
        PapayaCacheIdentifierDefinition::SOURCE_URL
      ),
      array(
        PapayaCacheIdentifierDefinition::SOURCE_URL |
          PapayaCacheIdentifierDefinition::SOURCE_SESSION,
        PapayaCacheIdentifierDefinition::SOURCE_URL,
        PapayaCacheIdentifierDefinition::SOURCE_SESSION
      ),
      array(
        PapayaCacheIdentifierDefinition::SOURCE_URL |
          PapayaCacheIdentifierDefinition::SOURCE_SESSION,
        PapayaCacheIdentifierDefinition::SOURCE_URL |
          PapayaCacheIdentifierDefinition::SOURCE_SESSION,
        PapayaCacheIdentifierDefinition::SOURCE_SESSION
      ),
      array(
        PapayaCacheIdentifierDefinition::SOURCE_URL |
          PapayaCacheIdentifierDefinition::SOURCE_SESSION |
          PapayaCacheIdentifierDefinition::SOURCE_VARIABLES,
        PapayaCacheIdentifierDefinition::SOURCE_URL |
          PapayaCacheIdentifierDefinition::SOURCE_SESSION,
        PapayaCacheIdentifierDefinition::SOURCE_VARIABLES
      ),
    );
  }
}