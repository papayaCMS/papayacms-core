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
  * @covers \Papaya\Session\Parameters::__construct
  * @covers \Papaya\Session\Parameters::parameters
  */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Request\Parameters $parameters */
    $parameters = $this->createMock(\Papaya\Request\Parameters::class);
    $sessionParameters = new \Papaya\Session\Parameters(
      $group = new \stdClass, $parameters
    );
    $this->assertSame(
      $parameters, $sessionParameters->parameters()
    );
    $this->assertAttributeSame(
      $group, '_group', $sessionParameters
    );
  }

  /**
  * @covers \Papaya\Session\Parameters::values
  */
  public function testValuesGetAfterSet() {
    $sessionValues = $this->getSessionValuesFixture();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Request\Parameters $parameters */
    $parameters = $this->createMock(\Papaya\Request\Parameters::class);
    $sessionParameters = new \Papaya\Session\Parameters(
      $group = new \stdClass, $parameters
    );
    $this->assertSame(
      $sessionValues, $sessionParameters->values($sessionValues)
    );
  }

  /**
  * @covers \Papaya\Session\Parameters::values
  */
  public function testValuesGetFromApplication() {
    $sessionValues = $this->getSessionValuesFixture();
    $session = $this->createMock(\Papaya\Session::class);
    $session
      ->expects($this->any())
      ->method('__get')
      ->with('values')
      ->will($this->returnValue($sessionValues));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Request\Parameters $parameters */
    $parameters = $this->createMock(\Papaya\Request\Parameters::class);
    $sessionParameters = new \Papaya\Session\Parameters(
      $group = new \stdClass, $parameters
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
  * @covers \Papaya\Session\Parameters::load
  * @covers \Papaya\Session\Parameters::getIdentifier
  */
  public function testLoadReadFromParametersChangeSessionValue() {
    $sessionValues = $this->getSessionValuesFixture(array('foo' => '21'));
    $sessionValues
      ->expects($this->once())
      ->method('set')
      ->with(array(new \stdClass, 'foo'), 42);
    $sessionParameters = new \Papaya\Session\Parameters(
      new \stdClass,
      new \Papaya\Request\Parameters(array('foo' => '42'))
    );
    $sessionParameters->values($sessionValues);
    $this->assertSame(42, $sessionParameters->load('foo', 23));
  }

  /**
  * @covers \Papaya\Session\Parameters::load
  */
  public function testLoadReadFromSessionChangeParameters() {
    $sessionValues = $this->getSessionValuesFixture(array('foo' => '21'));
    $sessionParameters = new \Papaya\Session\Parameters(
      new \stdClass,
      $parameters = new \Papaya\Request\Parameters()
    );
    $sessionParameters->values($sessionValues);
    $this->assertSame(21, $sessionParameters->load('foo', 23));
    $this->assertEquals(21, $parameters->get('foo'));
  }

  /**
  * @covers \Papaya\Session\Parameters::load
  */
  public function testLoadReadFromParametersRemovesOtherParameters() {
    $sessionValues = $this->getSessionValuesFixture(array('foo' => '21'));
    $sessionValues
      ->expects($this->at(1))
      ->method('set')
      ->with(array(new \stdClass, 'foo'), 42);
    $sessionValues
      ->expects($this->at(2))
      ->method('set')
      ->with(array(new \stdClass, 'bar'), NULL);
    $sessionParameters = new \Papaya\Session\Parameters(
      new \stdClass,
      $parameters = new \Papaya\Request\Parameters(array('foo' => '42', 'bar' => 'failed'))
    );
    $sessionParameters->values($sessionValues);
    $sessionParameters->load('foo', 23, NULL, 'bar');
    $this->assertFalse($parameters->has('bar'));
  }

  /**
  * @covers \Papaya\Session\Parameters::load
  */
  public function testLoadReturningDefault() {
    $sessionValues = $this->getSessionValuesFixture();
    $sessionParameters = new \Papaya\Session\Parameters(
      new \stdClass,
      new \Papaya\Request\Parameters()
    );
    $sessionParameters->values($sessionValues);
    $this->assertSame(23, $sessionParameters->load('foo', 23));
  }

  /**
  * @covers \Papaya\Session\Parameters::store
  */
  public function testStore() {
    $sessionValues = $this->getSessionValuesFixture();
    $sessionValues
      ->expects($this->once())
      ->method('set')
      ->with(array(new \stdClass, 'foo'), 42);
    $sessionParameters = new \Papaya\Session\Parameters(
      new \stdClass,
      $parameters = new \Papaya\Request\Parameters()
    );
    $sessionParameters->values($sessionValues);
    $sessionParameters->store('foo', 42);
    $this->assertEquals(42, $parameters->get('foo'));
  }

  /**
  * @covers \Papaya\Session\Parameters::remove
  */
  public function testRemove() {
    $sessionValues = $this->getSessionValuesFixture();
    $sessionValues
      ->expects($this->once())
      ->method('set')
      ->with(array(new \stdClass, 'foo'), NULL);
    $sessionParameters = new \Papaya\Session\Parameters(
      new \stdClass,
      $parameters = new \Papaya\Request\Parameters(array('foo' => 42))
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
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\Session\Values
   */
  private function getSessionValuesFixture(array $data = array()) {
    $this->_sessionData = $data;
    $sessionValues = $this
      ->getMockBuilder(\Papaya\Session\Values::class)
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
