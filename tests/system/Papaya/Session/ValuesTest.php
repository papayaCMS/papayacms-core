<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaSessionValuesTest extends PapayaTestCase {

  /**
  * @covers PapayaSessionValues::__construct
  */
  public function testConstructor() {
    $session = $this->getSessionFixture();
    $values = new PapayaSessionValues($session);
    $this->assertAttributeSame(
      $session, '_session', $values
    );
  }

  /**
  * @covers PapayaSessionValues::offsetExists
  */
  public function testOffsetExistsIfSessionActiveExpectingFalse() {
    $session = $this->getSessionFixture(TRUE);
    $values = new PapayaSessionValues($session);
    $this->assertFalse(isset($values['sample']));
  }

  /**
  * @backupGlobals enabled
  * @covers PapayaSessionValues::offsetExists
  */
  public function testOffsetExistsIfSessionActiveExpectingTrue() {
    $_SESSION = array('sample' => 'TRUE');
    $session = $this->getSessionFixture(TRUE);
    $values = new PapayaSessionValues($session);
    $this->assertTrue(isset($values['sample']));
  }

  /**
  * @covers PapayaSessionValues::offsetGet
  */
  public function testOffsetGetIfSessionActiveExpectingNull() {
    $session = $this->getSessionFixture(TRUE);
    $values = new PapayaSessionValues($session);
    $this->assertNull($values['sample']);
  }

  /**
  * @backupGlobals enabled
  * @covers PapayaSessionValues::offsetGet
  */
  public function testOffsetGetIfSessionActiveExpectingValue() {
    $_SESSION = array('sample' => 'success');
    $session = $this->getSessionFixture(TRUE);
    $values = new PapayaSessionValues($session);
    $this->assertEquals('success', $values['sample']);
  }

  /**
  * @backupGlobals
  * @covers PapayaSessionValues::offsetSet
  */
  public function testOffsetSetIfSessionInactive() {
    $_SESSION = array();
    $session = $this->getSessionFixture(FALSE);
    $values = new PapayaSessionValues($session);
    $values['sample'] = 'failed';
    $this->assertEquals(array(), $_SESSION);
  }

  /**
  * @backupGlobals
  * @covers PapayaSessionValues::offsetSet
  */
  public function testOffsetSetIfSessionActive() {
    $_SESSION = array();
    $session = $this->getSessionFixture(TRUE);
    $values = new PapayaSessionValues($session);
    $values['sample'] = 'success';
    $this->assertEquals(array('sample' => 'success'), $_SESSION);
  }

  /**
  * @backupGlobals
  * @covers PapayaSessionValues::offsetUnset
  */
  public function testOffsetUnsetIfSessionActive() {
    $_SESSION = array('sample' => 'failed');
    $session = $this->getSessionFixture(TRUE);
    $values = new PapayaSessionValues($session);
    unset($values['sample']);
    $this->assertEquals(array(), $_SESSION);
  }

  /**
  * @covers PapayaSessionValues
  */
  public function testGetAfterSetWithInactiveSessionUsingFallback() {
    $session = $this->getSessionFixture(FALSE);
    $values = new PapayaSessionValues($session);
    $values['sample'] = 'success';
    $this->assertEquals('success', $values['sample']);
  }

  /**
  * @covers PapayaSessionValues
  */
  public function testIssetAfterSetWithInactiveSessionUsingFallback() {
    $session = $this->getSessionFixture(FALSE);
    $values = new PapayaSessionValues($session);
    $values['sample'] = 'success';
    $this->assertTrue(isset($values['sample']));
  }

  /**
  * @covers PapayaSessionValues
  */
  public function testGetAfterUnsetWithInactiveSessionUsingFallback() {
    $session = $this->getSessionFixture(FALSE);
    $values = new PapayaSessionValues($session);
    $values['sample'] = 'fail';
    unset($values['sample']);
    $this->assertNull($values['sample']);
  }

  /**
  * @backupGlobals
  * @covers PapayaSessionValues::_compileKey
  * @dataProvider provideIdentfierData
  */
  public function testIdentifierHandlingBySettingValues($expected, $identifierData) {
    $_SESSION = array();
    $session = $this->getSessionFixture(TRUE);
    $values = new PapayaSessionValues($session);
    $values[$identifierData] = TRUE;
    $this->assertEquals(
      array($expected => TRUE),
      $_SESSION
    );
  }

  /**
  * @backupGlobals
  * @covers PapayaSessionValues::set
  */
  public function testSet() {
    $_SESSION = array();
    $session = $this->getSessionFixture(TRUE);
    $values = new PapayaSessionValues($session);
    $values->set('sample', 'success');
    $this->assertEquals(array('sample' => 'success'), $_SESSION);
  }

  /**
  * @backupGlobals
  * @covers PapayaSessionValues::get
  */
  public function testGet() {
    $_SESSION = array('sample' => 'success');
    $session = $this->getSessionFixture(TRUE);
    $values = new PapayaSessionValues($session);
    $this->assertEquals('success', $values->get('sample'));
  }

  /**
  * @covers PapayaSessionValues::getKey
  * @dataProvider provideIdentfierData
  */
  public function testGetKey($expected, $identifierData) {
    $values = new PapayaSessionValues($this->createMock(PapayaSession::class));
    $this->assertSame($expected, $values->getKey($identifierData));
  }

  /************************
  * Fixtures
  *************************/

  public function getSessionFixture($isActive = FALSE) {
    $session = $this->getMock('PapayaSession', array('isActive'));
    $session
      ->expects($this->any())
      ->method('isActive')
      ->will($this->returnValue($isActive));
    return $session;
  }

  /************************
  * Data Provider
  *************************/

  public static function provideIdentfierData() {
    return array(
      'string' => array('sample', 'sample'),
      'number' => array('123', 123),
      'object' => array('stdClass', new stdClass()),
      'array of strings' => array('foo_bar', array('foo', 'bar')),
      'array with object' => array('stdClass_bar', array(new stdClass(), 'bar')),
      'array with array' => array('5b448a7bdbeea0be7d7f758f5f8ee90b_bar', array(array(''), 'bar'))
    );
  }
}
