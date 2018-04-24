<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaCacheIdentifierDefinitionSessionParametersTest extends PapayaTestCase {

  /**
   * @covers PapayaCacheIdentifierDefinitionSessionParameters
   */
  public function testGetStatus() {
    $values = $this
      ->getMockBuilder('PapayaSessionValues')
      ->disableOriginalConstructor()
      ->getMock();
    $values
      ->expects($this->once())
      ->method('getKey')
      ->with('foo')
      ->will($this->returnValue('bar'));
    $values
      ->expects($this->once())
      ->method('offsetGet')
      ->with('bar')
      ->will($this->returnValue('session_value'));
    $session = $this->getMock('PapayaSession');
    $session
      ->expects($this->once())
      ->method('isActive')
      ->will($this->returnValue(TRUE));
    $session
      ->expects($this->once())
      ->method('values')
      ->will($this->returnValue($values));

    $definition = new PapayaCacheIdentifierDefinitionSessionParameters('foo');
    $definition->papaya(
      $this->mockPapaya()->application(
        array(
          'session' => $session
        )
      )
    );
    $this->assertEquals(
      array('PapayaCacheIdentifierDefinitionSessionParameters' => array('bar' => 'session_value')),
      $definition->getStatus()
    );
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionSessionParameters
   */
  public function testGetStatusValueReturnsNull() {
    $values = $this
      ->getMockBuilder('PapayaSessionValues')
      ->disableOriginalConstructor()
      ->getMock();
    $values
      ->expects($this->any())
      ->method('getKey')
      ->withAnyParameters()
      ->will($this->returnArgument(0));
    $values
      ->expects($this->once())
      ->method('offsetGet')
      ->with('foo')
      ->will($this->returnValue(NULL));
    $session = $this->getMock('PapayaSession');
    $session
      ->expects($this->once())
      ->method('isActive')
      ->will($this->returnValue(TRUE));
    $session
      ->expects($this->once())
      ->method('values')
      ->will($this->returnValue($values));

    $definition = new PapayaCacheIdentifierDefinitionSessionParameters('foo');
    $definition->papaya(
      $this->mockPapaya()->application(
        array(
          'session' => $session
        )
      )
    );
    $this->assertTrue(
      $definition->getStatus()
    );
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionSessionParameters
   */
  public function testGetStatusNoSessionActive() {
    $session = $this->getMock('PapayaSession');
    $session
      ->expects($this->once())
      ->method('isActive')
      ->will($this->returnValue(FALSE));

    $definition = new PapayaCacheIdentifierDefinitionSessionParameters('foo');
    $definition->papaya(
      $this->mockPapaya()->application(
        array(
          'session' => $session
        )
      )
    );
    $this->assertTrue(
      $definition->getStatus()
    );
  }
  /**
   * @covers PapayaCacheIdentifierDefinitionSessionParameters
   */
  public function testGetStatusMultipleParameters() {
    $values = $this
      ->getMockBuilder('PapayaSessionValues')
      ->disableOriginalConstructor()
      ->getMock();
    $values
      ->expects($this->any())
      ->method('getKey')
      ->withAnyParameters()
      ->will($this->returnArgument(0));
    $values
      ->expects($this->any())
      ->method('offsetGet')
      ->withAnyParameters()
      ->will(
        $this->returnValueMap(
          array(
            array('foo', 21),
            array('bar', 42),
            array('foobar', NULL)
          )
        )
      );
    $session = $this->getMock('PapayaSession');
    $session
      ->expects($this->once())
      ->method('isActive')
      ->will($this->returnValue(TRUE));
    $session
      ->expects($this->once())
      ->method('values')
      ->will($this->returnValue($values));

    $definition = new PapayaCacheIdentifierDefinitionSessionParameters('foo', 'bar', 'foobar');
    $definition->papaya(
      $this->mockPapaya()->application(
        array(
          'session' => $session
        )
      )
    );
    $this->assertEquals(
      array(
        'PapayaCacheIdentifierDefinitionSessionParameters' => array(
          'foo' => 21,
          'bar' => 42
        )
      ),
      $definition->getStatus()
    );
  }

  /**
   * @covers PapayaCacheIdentifierDefinitionSessionParameters
   */
  public function testGetSources() {
    $definition = new PapayaCacheIdentifierDefinitionSessionParameters('foo');
    $this->assertEquals(
      PapayaCacheIdentifierDefinition::SOURCE_SESSION,
      $definition->getSources()
    );
  }
}
