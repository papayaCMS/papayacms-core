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

class PapayaSessionValuesTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Session\Values::__construct
  */
  public function testConstructor() {
    $session = $this->getSessionFixture();
    $values = new \Papaya\Session\Values($session);
    $this->assertAttributeSame(
      $session, '_session', $values
    );
  }

  /**
  * @covers \Papaya\Session\Values::offsetExists
  */
  public function testOffsetExistsIfSessionActiveExpectingFalse() {
    $session = $this->getSessionFixture(TRUE);
    $values = new \Papaya\Session\Values($session);
    $this->assertFalse(isset($values['sample']));
  }

  /**
  * @backupGlobals enabled
  * @covers \Papaya\Session\Values::offsetExists
  */
  public function testOffsetExistsIfSessionActiveExpectingTrue() {
    $_SESSION = array('sample' => 'TRUE');
    $session = $this->getSessionFixture(TRUE);
    $values = new \Papaya\Session\Values($session);
    $this->assertTrue(isset($values['sample']));
  }

  /**
  * @covers \Papaya\Session\Values::offsetGet
  */
  public function testOffsetGetIfSessionActiveExpectingNull() {
    $session = $this->getSessionFixture(TRUE);
    $values = new \Papaya\Session\Values($session);
    $this->assertNull($values['sample']);
  }

  /**
  * @backupGlobals enabled
  * @covers \Papaya\Session\Values::offsetGet
  */
  public function testOffsetGetIfSessionActiveExpectingValue() {
    $_SESSION = array('sample' => 'success');
    $session = $this->getSessionFixture(TRUE);
    $values = new \Papaya\Session\Values($session);
    $this->assertEquals('success', $values['sample']);
  }

  /**
  * @backupGlobals
  * @covers \Papaya\Session\Values::offsetSet
  */
  public function testOffsetSetIfSessionInactive() {
    $_SESSION = array();
    $session = $this->getSessionFixture(FALSE);
    $values = new \Papaya\Session\Values($session);
    $values['sample'] = 'failed';
    $this->assertEquals(array(), $_SESSION);
  }

  /**
  * @backupGlobals
  * @covers \Papaya\Session\Values::offsetSet
  */
  public function testOffsetSetIfSessionActive() {
    $_SESSION = array();
    $session = $this->getSessionFixture(TRUE);
    $values = new \Papaya\Session\Values($session);
    $values['sample'] = 'success';
    $this->assertEquals(array('sample' => 'success'), $_SESSION);
  }

  /**
  * @backupGlobals
  * @covers \Papaya\Session\Values::offsetUnset
  */
  public function testOffsetUnsetIfSessionActive() {
    $_SESSION = array('sample' => 'failed');
    $session = $this->getSessionFixture(TRUE);
    $values = new \Papaya\Session\Values($session);
    unset($values['sample']);
    $this->assertEquals(array(), $_SESSION);
  }

  /**
  * @covers \Papaya\Session\Values
  */
  public function testGetAfterSetWithInactiveSessionUsingFallback() {
    $session = $this->getSessionFixture(FALSE);
    $values = new \Papaya\Session\Values($session);
    $values['sample'] = 'success';
    $this->assertEquals('success', $values['sample']);
  }

  /**
  * @covers \Papaya\Session\Values
  */
  public function testIssetAfterSetWithInactiveSessionUsingFallback() {
    $session = $this->getSessionFixture(FALSE);
    $values = new \Papaya\Session\Values($session);
    $values['sample'] = 'success';
    $this->assertTrue(isset($values['sample']));
  }

  /**
  * @covers \Papaya\Session\Values
  */
  public function testGetAfterUnsetWithInactiveSessionUsingFallback() {
    $session = $this->getSessionFixture(FALSE);
    $values = new \Papaya\Session\Values($session);
    $values['sample'] = 'fail';
    unset($values['sample']);
    $this->assertNull($values['sample']);
  }

  /**
   * @backupGlobals
   * @covers \Papaya\Session\Values::_compileKey
   * @dataProvider provideIdentifierData
   * @param $expected
   * @param $identifierData
   */
  public function testIdentifierHandlingBySettingValues($expected, $identifierData) {
    $_SESSION = array();
    $session = $this->getSessionFixture(TRUE);
    $values = new \Papaya\Session\Values($session);
    $values[$identifierData] = TRUE;
    $this->assertEquals(
      array($expected => TRUE),
      $_SESSION
    );
  }

  /**
  * @backupGlobals
  * @covers \Papaya\Session\Values::set
  */
  public function testSet() {
    $_SESSION = array();
    $session = $this->getSessionFixture(TRUE);
    $values = new \Papaya\Session\Values($session);
    $values->set('sample', 'success');
    $this->assertEquals(array('sample' => 'success'), $_SESSION);
  }

  /**
  * @backupGlobals
  * @covers \Papaya\Session\Values::get
  */
  public function testGet() {
    $_SESSION = array('sample' => 'success');
    $session = $this->getSessionFixture(TRUE);
    $values = new \Papaya\Session\Values($session);
    $this->assertEquals('success', $values->get('sample'));
  }

  /**
   * @covers \Papaya\Session\Values::getKey
   * @dataProvider provideIdentifierData
   * @param mixed $expected
   * @param mixed $identifierData
   */
  public function testGetKey($expected, $identifierData) {
    $values = new \Papaya\Session\Values($this->getSessionFixture(TRUE));
    $this->assertSame($expected, $values->getKey($identifierData));
  }

  /************************
   * Fixtures
   ************************/

  /**
   * @param bool $isActive
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\Session
   */
  public function getSessionFixture($isActive = FALSE) {
    $session = $this->createMock(\Papaya\Session::class);
    $session
      ->expects($this->any())
      ->method('isActive')
      ->will($this->returnValue($isActive));
    return $session;
  }

  /************************
  * Data Provider
  *************************/

  public static function provideIdentifierData() {
    return array(
      'string' => array('sample', 'sample'),
      'number' => array('123', 123),
      'object' => array(\stdClass::class, new \stdClass()),
      'array of strings' => array('foo_bar', array('foo', 'bar')),
      'array with object' => array('stdClass_bar', array(new \stdClass(), 'bar')),
      'array with array' => array('5b448a7bdbeea0be7d7f758f5f8ee90b_bar', array(array(''), 'bar'))
    );
  }
}
