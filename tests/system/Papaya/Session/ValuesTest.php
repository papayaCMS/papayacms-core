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

namespace Papaya\Session {

  use Papaya\Session;

  require_once __DIR__.'/../../../bootstrap.php';

  /**
   * @covers \Papaya\Session\Values
   */
  class ValuesTest extends \Papaya\TestFramework\TestCase {

    public function testOffsetExistsIfSessionActiveExpectingFalse() {
      $session = $this->getSessionFixture(TRUE);
      $values = new Values($session);
      $this->assertFalse(isset($values['sample']));
    }

    public function testOffsetExistsIfSessionActiveExpectingTrue() {
      $session = $this->getSessionFixture(TRUE, ['sample' => 42]);
      $values = new Values($session);
      $this->assertTrue(isset($values['sample']));
    }

    public function testOffsetGetIfSessionActiveExpectingNull() {
      $session = $this->getSessionFixture(TRUE);
      $values = new Values($session);
      $this->assertNull($values['sample']);
    }

    public function testOffsetGetIfSessionActiveExpectingValue() {
      $session = $this->getSessionFixture(TRUE, ['sample' => 'success']);
      $values = new Values($session);
      $this->assertEquals('success', $values['sample']);
    }

    public function testOffsetSetIfSessionInactive() {
      $session = $this->getSessionFixture(FALSE);
      $session
        ->wrapper()
        ->expects($this->never())
        ->method('storeValue');
      $values = new Values($session);
      $values['sample'] = 'fallback';
      $this->assertSame('fallback', $values['sample']);
    }

    public function testOffsetSetIfSessionActive() {
      $session = $this->getSessionFixture(TRUE);
      $session
        ->wrapper()
        ->expects($this->once())
        ->method('storeValue')
        ->with('sample', 'success');
      $values = new Values($session);
      $values['sample'] = 'success';
    }

    public function testOffsetUnsetIfSessionActive() {
      $session = $this->getSessionFixture(TRUE, ['sample' => 'failed']);
      $session
        ->wrapper()
        ->expects($this->once())
        ->method('removeValue')
        ->with('sample');
      $values = new Values($session);
      unset($values['sample']);
    }

    public function testGetAfterSetWithInactiveSessionUsingFallback() {
      $session = $this->getSessionFixture(FALSE);
      $values = new Values($session);
      $values['sample'] = 'success';
      $this->assertEquals('success', $values['sample']);
    }

    public function testIssetAfterSetWithInactiveSessionUsingFallback() {
      $session = $this->getSessionFixture(FALSE);
      $values = new Values($session);
      $values['sample'] = 'success';
      $this->assertTrue(isset($values['sample']));
    }

    public function testGetAfterUnsetWithInactiveSessionUsingFallback() {
      $session = $this->getSessionFixture(FALSE);
      $values = new Values($session);
      $values['sample'] = 'fail';
      unset($values['sample']);
      $this->assertNull($values['sample']);
    }

    public function testSet() {
      $session = $this->getSessionFixture(TRUE);
      $session
        ->wrapper()
        ->expects($this->once())
        ->method('storeValue')
        ->with('sample', 'success');
      $values = new Values($session);
      $values->set('sample', 'success');
    }

    public function testGet() {
      $session = $this->getSessionFixture(TRUE, ['sample' => 'success']);
      $values = new Values($session);
      $this->assertEquals('success', $values->get('sample'));
    }

    public function testGetWithCastToDefaultValueType() {
      $session = $this->getSessionFixture(TRUE, ['sample' => 'fail']);
      $values = new Values($session);
      $this->assertSame(0, $values->get('sample', 21));
    }

    public function testGetWithArrayDefaultValueToIgnoringScalarValue() {
      $session = $this->getSessionFixture(TRUE, ['sample' => 'fail']);
      $values = new Values($session);
      $this->assertSame(['sample' => 'success'], $values->get('sample', ['sample' => 'success']));
    }

    public function testGetWithValueObjectCastableToString() {
      $valueObject = $this->createMock(StringCastable_TestFixture::class);
      $valueObject
        ->expects($this->once())
        ->method('__toString')
        ->willReturn('success');

      $session = $this->getSessionFixture(TRUE, ['sample' => []]);
      $values = new Values($session);
      $this->assertSame('success', $values->get('sample', $valueObject));
    }

    public function testGetWithNonCastableObjectAsDefaultValue() {
      $valueObject = new \stdClass();
      $session = $this->getSessionFixture(TRUE, ['sample' => 'fail']);
      $values = new Values($session);
      $this->assertSame($valueObject, $values->get('sample', $valueObject));
    }

    public function testGetWithFilterReturnDefaultValue() {
      $session = $this->getSessionFixture(TRUE, ['sample' => 'fail']);
      $values = new Values($session);
      $this->assertSame(
        21,
        $values->get('sample', 21, new \Papaya\Filter\IntegerValue(3))
      );
    }

    /**
     * @dataProvider provideIdentifierData
     * @param mixed $expected
     * @param mixed $identifierData
     */
    public function testGetKey($expected, $identifierData) {
      $values = new Values($this->getSessionFixture(TRUE));
      $this->assertSame($expected, $values->getKey($identifierData));
    }

    /************************
     * Fixtures
     ************************/

    /**
     * @param bool $isActive
     * @param array $data
     * @return \PHPUnit_Framework_MockObject_MockObject|Session
     */
    public function getSessionFixture($isActive = FALSE, $data = []) {
      $wrapper = $this->createMock(Wrapper::class);
      $wrapper
        ->method('hasValue')
        ->willReturnCallback(
          static function ($key) use ($data) {
            return array_key_exists($key, $data);
          }
        );
      $wrapper
        ->method('readValue')
        ->willReturnCallback(
          static function ($key) use ($data) {
            return $data[$key];
          }
        );
      $session = $this->createMock(Session::class);
      $session
        ->method('isActive')
        ->willReturn($isActive);
      $session
        ->method('wrapper')
        ->willReturn($wrapper);
      return $session;
    }

    /************************
     * Data Provider
     *************************/

    public static function provideIdentifierData() {
      return [
        'string' => ['sample', 'sample'],
        'number' => ['123', 123],
        'object' => [\stdClass::class, new \stdClass()],
        'array of strings' => ['foo_bar', ['foo', 'bar']],
        'array with object' => ['stdClass_bar', [new \stdClass(), 'bar']],
        'array with array' => ['5b448a7bdbeea0be7d7f758f5f8ee90b_bar', [[''], 'bar']]
      ];
    }

  }

  class StringCastable_TestFixture {
    public function __toString() {
      return 'success';
    }
  }
}
