<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

require_once __DIR__.'/../../../bootstrap.php';

class PapayaSessionParametersTest extends \PapayaTestCase {

  private $_sessionData = array();

  /**
  * @covers \PapayaSessionParameters::__construct
  * @covers \PapayaSessionParameters::parameters
  */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaRequestParameters $parameters */
    $parameters = $this->createMock(\PapayaRequestParameters::class);
    $sessionParameters = new \PapayaSessionParameters(
      $group = new stdClass, $parameters
    );
    $this->assertSame(
      $parameters, $sessionParameters->parameters()
    );
    $this->assertAttributeSame(
      $group, '_group', $sessionParameters
    );
  }

  /**
  * @covers \PapayaSessionParameters::values
  */
  public function testValuesGetAfterSet() {
    $sessionValues = $this->getSessionValuesFixture();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaRequestParameters $parameters */
    $parameters = $this->createMock(\PapayaRequestParameters::class);
    $sessionParameters = new \PapayaSessionParameters(
      $group = new stdClass, $parameters
    );
    $this->assertSame(
      $sessionValues, $sessionParameters->values($sessionValues)
    );
  }

  /**
  * @covers \PapayaSessionParameters::values
  */
  public function testValuesGetFromApplication() {
    $sessionValues = $this->getSessionValuesFixture();
    $session = $this->createMock(Papaya\Session::class);
    $session
      ->expects($this->any())
      ->method('__get')
      ->with('values')
      ->will($this->returnValue($sessionValues));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaRequestParameters $parameters */
    $parameters = $this->createMock(\PapayaRequestParameters::class);
    $sessionParameters = new \PapayaSessionParameters(
      $group = new stdClass, $parameters
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
  * @covers \PapayaSessionParameters::load
  * @covers \PapayaSessionParameters::getIdentifier
  */
  public function testLoadReadFromParametersChangeSessionValue() {
    $sessionValues = $this->getSessionValuesFixture(array('foo' => '21'));
    $sessionValues
      ->expects($this->once())
      ->method('set')
      ->with(array(new stdClass, 'foo'), 42);
    $sessionParameters = new \PapayaSessionParameters(
      new stdClass,
      new \PapayaRequestParameters(array('foo' => '42'))
    );
    $sessionParameters->values($sessionValues);
    $this->assertSame(42, $sessionParameters->load('foo', 23));
  }

  /**
  * @covers \PapayaSessionParameters::load
  */
  public function testLoadReadFromSessionChangeParameters() {
    $sessionValues = $this->getSessionValuesFixture(array('foo' => '21'));
    $sessionParameters = new \PapayaSessionParameters(
      new stdClass,
      $parameters = new \PapayaRequestParameters()
    );
    $sessionParameters->values($sessionValues);
    $this->assertSame(21, $sessionParameters->load('foo', 23));
    $this->assertEquals(21, $parameters->get('foo'));
  }

  /**
  * @covers \PapayaSessionParameters::load
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
    $sessionParameters = new \PapayaSessionParameters(
      new stdClass,
      $parameters = new \PapayaRequestParameters(array('foo' => '42', 'bar' => 'failed'))
    );
    $sessionParameters->values($sessionValues);
    $sessionParameters->load('foo', 23, NULL, 'bar');
    $this->assertFalse($parameters->has('bar'));
  }

  /**
  * @covers \PapayaSessionParameters::load
  */
  public function testLoadReturningDefault() {
    $sessionValues = $this->getSessionValuesFixture();
    $sessionParameters = new \PapayaSessionParameters(
      new stdClass,
      new \PapayaRequestParameters()
    );
    $sessionParameters->values($sessionValues);
    $this->assertSame(23, $sessionParameters->load('foo', 23));
  }

  /**
  * @covers \PapayaSessionParameters::store
  */
  public function testStore() {
    $sessionValues = $this->getSessionValuesFixture();
    $sessionValues
      ->expects($this->once())
      ->method('set')
      ->with(array(new stdClass, 'foo'), 42);
    $sessionParameters = new \PapayaSessionParameters(
      new stdClass,
      $parameters = new \PapayaRequestParameters()
    );
    $sessionParameters->values($sessionValues);
    $sessionParameters->store('foo', 42);
    $this->assertEquals(42, $parameters->get('foo'));
  }

  /**
  * @covers \PapayaSessionParameters::remove
  */
  public function testRemove() {
    $sessionValues = $this->getSessionValuesFixture();
    $sessionValues
      ->expects($this->once())
      ->method('set')
      ->with(array(new stdClass, 'foo'), NULL);
    $sessionParameters = new \PapayaSessionParameters(
      new stdClass,
      $parameters = new \PapayaRequestParameters(array('foo' => 42))
    );
    $sessionParameters->values($sessionValues);
    $sessionParameters->remove('foo');
    $this->assertFalse($parameters->has('foo'));
  }

  /****************
   * Fixtures
   ***************/

  /**
   * @param array $data
   * @return \PHPUnit_Framework_MockObject_MockObject|\PapayaSessionValues
   */
  private function getSessionValuesFixture(array $data = array()) {
    $this->_sessionData = $data;
    $sessionValues = $this
      ->getMockBuilder(\PapayaSessionValues::class)
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
