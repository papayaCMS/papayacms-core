<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaSessionParametersTest extends PapayaTestCase {

  private $_sessionData = array();

  /**
  * @covers PapayaSessionParameters::__construct
  * @covers PapayaSessionParameters::parameters
  */
  public function testConstructor() {
    $sessionParameters = new PapayaSessionParameters(
      $group = new stdClass,
      $parameters = $this->getMock('PapayaRequestParameters')
    );
    $this->assertSame(
      $parameters, $sessionParameters->parameters()
    );
    $this->assertAttributeSame(
      $group, '_group', $sessionParameters
    );
  }

  /**
  * @covers PapayaSessionParameters::values
  */
  public function testValuesGetAfterSet() {
    $sessionValues = $this->getSessionValuesFixture();
    $sessionParameters = new PapayaSessionParameters(
      new stdClass,
      $this->getMock('PapayaRequestParameters')
    );
    $this->assertSame(
      $sessionValues, $sessionParameters->values($sessionValues)
    );
  }

  /**
  * @covers PapayaSessionParameters::values
  */
  public function testValuesGetFromApplication() {
    $sessionValues = $this->getSessionValuesFixture();
    $session = $this->getMock('PapayaSession');
    $session
      ->expects($this->any())
      ->method('__get')
      ->with('values')
      ->will($this->returnValue($sessionValues));
    $sessionParameters = new PapayaSessionParameters(
      new stdClass,
      $this->getMock('PapayaRequestParameters')
    );
    $sessionParameters->papaya(
      $this->mockPapaya()->application(
        array(
          'Session' => $session
        )
      )
    );
    $this->assertEquals(
      $sessionValues, $sessionParameters->values()
    );
  }

  /**
  * @covers PapayaSessionParameters::load
  * @covers PapayaSessionParameters::getIdentifier
  */
  public function testLoadReadFromParametersChangeSessionValue() {
    $sessionValues = $this->getSessionValuesFixture(array('foo' => '21'));
    $sessionValues
      ->expects($this->once())
      ->method('set')
      ->with(array(new stdClass, 'foo'), 42);
    $sessionParameters = new PapayaSessionParameters(
      new stdClass,
      new PapayaRequestParameters(array('foo' => '42'))
    );
    $sessionParameters->values($sessionValues);
    $this->assertSame(42, $sessionParameters->load('foo', 23));
  }

  /**
  * @covers PapayaSessionParameters::load
  */
  public function testLoadReadFromSessionChangeParameters() {
    $sessionValues = $this->getSessionValuesFixture(array('foo' => '21'));
    $sessionParameters = new PapayaSessionParameters(
      new stdClass,
      $parameters = new PapayaRequestParameters()
    );
    $sessionParameters->values($sessionValues);
    $this->assertSame(21, $sessionParameters->load('foo', 23));
    $this->assertEquals(21, $parameters->get('foo'));
  }

  /**
  * @covers PapayaSessionParameters::load
  */
  public function testLoadReadFromParametersRemovesOtherParameters() {
    $sessionValues = $this->getSessionValuesFixture(array('foo' => '21'));
    $sessionValues
      ->expects($this->at(1))
      ->method('set')
      ->with(array(new stdClass, 'foo'), 42);
    $sessionValues
      ->expects($this->at(2))
      ->method('set')
      ->with(array(new stdClass, 'bar'), NULL);
    $sessionParameters = new PapayaSessionParameters(
      new stdClass,
      $parameters = new PapayaRequestParameters(array('foo' => '42', 'bar' => 'failed'))
    );
    $sessionParameters->values($sessionValues);
    $sessionParameters->load('foo', 23, NULL, 'bar');
    $this->assertFalse($parameters->has('bar'));
  }

  /**
  * @covers PapayaSessionParameters::load
  */
  public function testLoadReturningDefault() {
    $sessionValues = $this->getSessionValuesFixture();
    $sessionParameters = new PapayaSessionParameters(
      new stdClass,
      new PapayaRequestParameters()
    );
    $sessionParameters->values($sessionValues);
    $this->assertSame(23, $sessionParameters->load('foo', 23));
  }

  /**
  * @covers PapayaSessionParameters::store
  */
  public function testStore() {
    $sessionValues = $this->getSessionValuesFixture();
    $sessionValues
      ->expects($this->once())
      ->method('set')
      ->with(array(new stdClass, 'foo'), 42);
    $sessionParameters = new PapayaSessionParameters(
      new stdClass,
      $parameters = new PapayaRequestParameters()
    );
    $sessionParameters->values($sessionValues);
    $sessionParameters->store('foo', 42);
    $this->assertEquals(42, $parameters->get('foo'));
  }

  /**
  * @covers PapayaSessionParameters::remove
  */
  public function testRemove() {
    $sessionValues = $this->getSessionValuesFixture();
    $sessionValues
      ->expects($this->once())
      ->method('set')
      ->with(array(new stdClass, 'foo'), NULL);
    $sessionParameters = new PapayaSessionParameters(
      new stdClass,
      $parameters = new PapayaRequestParameters(array('foo' => 42))
    );
    $sessionParameters->values($sessionValues);
    $sessionParameters->remove('foo');
    $this->assertFalse($parameters->has('foo'));
  }

  /****************
  * Fixtures
  ****************/

  private function getSessionValuesFixture($data = array()) {
    $this->_sessionData = $data;
    $sessionValues = $this
      ->getMockBuilder('PapayaSessionValues')
      ->disableOriginalConstructor()
      ->getMock();
    $sessionValues
      ->expects($this->any())
      ->method('get')
      ->will($this->returnCallback(array($this, 'callbackSessionValue')));
    return $sessionValues;
  }

  public function callbackSessionValue(array $identifier) {
    return isset($this->_sessionData[$identifier[1]])
      ? $this->_sessionData[$identifier[1]] : NULL;
  }
}