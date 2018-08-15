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

class PapayaObjectItemTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\BaseObject\Item::__construct
  */
  public function testConstructorSetsFields() {
    $item = new \PapayaObjectItem_TestProxy(array('sample_one', 'sampleTwo'));
    $this->assertAttributeEquals(
      array(
        'sample_one' => NULL,
        'sample_two' => NULL
      ),
      '_values',
      $item
    );
  }

  /**
  * @covers \Papaya\BaseObject\Item::assign
  */
  public function testAssign() {
    $item = new \PapayaObjectItem_TestProxy(array('sample_one', 'sampleTwo'));
    $item->assign(
      array(
        'sampleOne' => 21,
        'SAMPLE_TWO' => 42,
        'unknown' => 23
      )
    );
    $this->assertAttributeEquals(
      array(
        'sample_one' => 21,
        'sample_two' => 42
      ),
      '_values',
      $item
    );
  }

  /**
  * @covers \Papaya\BaseObject\Item::clear
  */
  public function testClear() {
    $item = new \PapayaObjectItem_TestProxy(array('sample_one', 'sampleTwo'));
    $item->assign(
      array(
        'sampleOne' => 21,
        'SAMPLE_TWO' => 42
      )
    );
    $item->clear();
    $this->assertEquals(
      array(
        'sample_one' => NULL,
        'sample_two' => NULL
      ),
      $item->toArray()
    );
  }

  /**
  * @covers \Papaya\BaseObject\Item::assign
  */
  public function testAssignExpectingInvalid() {
    $item = new \PapayaObjectItem_TestProxy(array('sample_one', 'sampleTwo'));
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('Argument $data must be an array or instance of Traversable.');
    /** @noinspection PhpParamsInspection */
    $item->assign('INVALID');
  }

  /**
  * @covers \Papaya\BaseObject\Item::toArray
  */
  public function testToArray() {
    $item = new \PapayaObjectItem_TestProxy(array('sample_one', 'sample_two'));
    $item->assign(
      array(
        'sample_one' => 21,
        'sample_two' => 42
      )
    );
    $this->assertEquals(
      array(
        'sample_one' => 21,
        'sample_two' => 42
      ),
      $item->toArray()
    );
  }

  /**
  * @covers \Papaya\BaseObject\Item::getIterator
  */
  public function testGetIterator() {
    $item = new \PapayaObjectItem_TestProxy(array('sample_one', 'sample_two'));
    $iterator = $item->getIterator();
    $this->assertEquals(
      array(
        'sample_one' => NULL,
        'sample_two' => NULL
      ),
      $iterator->getArrayCopy()
    );
  }

  /**
  * @covers \Papaya\BaseObject\Item::__isset
  */
  public function testMagicMethodIssetExpectingTrue() {
    $item = new \PapayaObjectItem_TestProxy(array('sample_one', 'sample_two'));
    $item->sampleOne = 'success';
    $this->assertTrue(isset($item->sampleOne));
  }

  /**
  * @covers \Papaya\BaseObject\Item::__isset
  */
  public function testMagicMethodIssetExpectingFalse() {
    $item = new \PapayaObjectItem_TestProxy(array('sample_one', 'sample_two'));
    $this->assertFalse(isset($item->sampleOne));
  }

  /**
  * @covers \Papaya\BaseObject\Item::__get
  */
  public function testMagicMethodGetWithoutSetExpectingNull() {
    $item = new \PapayaObjectItem_TestProxy(array('sample_one', 'sample_two'));
    $this->assertNull($item->sampleOne);
  }

  /**
  * @covers \Papaya\BaseObject\Item::__get
  * @covers \Papaya\BaseObject\Item::__set
  */
  public function testMagicMethodGetAfterSet() {
    $item = new \PapayaObjectItem_TestProxy(array('sample_one', 'sample_two'));
    $item->sampleOne = 'success';
    $this->assertEquals('success', $item->sampleOne);
  }

  /**
  * @covers \Papaya\BaseObject\Item::__unset
  */
  public function testMagicMethodUnsetAfterSet() {
    $item = new \PapayaObjectItem_TestProxy(array('sample_one', 'sample_two'));
    $item->sampleOne = 'failed';
    unset($item->sampleOne);
    $this->assertNull($item->sampleOne);
  }

  /**
  * @covers \Papaya\BaseObject\Item::offsetExists
  */
  public function testOffsetExistsExpectingTrue() {
    $item = new \PapayaObjectItem_TestProxy(array('sample_one', 'sample_two'));
    $item->sampleOne = 'success';
    $this->assertTrue(isset($item['sample_one']));
  }

  /**
  * @covers \Papaya\BaseObject\Item::offsetExists
  */
  public function testOffsetExistsExpectingFalse() {
    $item = new \PapayaObjectItem_TestProxy(array('sample_one', 'sample_two'));
    $this->assertFalse(isset($item['unknown_property']));
  }

  /**
  * @covers \Papaya\BaseObject\Item::offsetGet
  */
  public function testOffsetGetWithoutSet() {
    $item = new \PapayaObjectItem_TestProxy(array('sample_one', 'sample_two'));
    $this->assertNull($item['sample_one']);
  }

  /**
  * @covers \Papaya\BaseObject\Item::offsetGet
  * @covers \Papaya\BaseObject\Item::offsetSet
  */
  public function testOffsetGetAfterSet() {
    $item = new \PapayaObjectItem_TestProxy(array('sample_one', 'sample_two'));
    $item['sample_one'] = 'success';
    $this->assertEquals('success', $item['sample_one']);
  }

  /**
  * @covers \Papaya\BaseObject\Item::offsetUnset
  */
  public function testOffsetUnset() {
    $item = new \PapayaObjectItem_TestProxy(array('sample_one', 'sample_two'));
    $item->sampleOne = 'success';
    unset($item['sample_one']);
    $this->assertNull($item['sample_one']);
  }

  /**
   * @covers \Papaya\BaseObject\Item::_prepareName
   * @dataProvider provideNameVariants
   * @param string $name
   * @param string $normalizedName
   */
  public function testPropertyConvert($name, $normalizedName) {
    $item = new \PapayaObjectItem_TestProxy(array($name));
    $item[$name] = 'success';
    $this->assertEquals(
      array($normalizedName => 'success'),
      $item->toArray()
    );
  }

  public static function provideNameVariants() {
    return array(
      'lowercase' => array('sample_one', 'sample_one'),
      'uppercase' => array('SAMPLE_TWO', 'sample_two'),
      'camel case' => array('sampleTwo', 'sample_two'),
      'camel case first upper' => array('SampleTwo', 'sample_two')
    );
  }

  /**
  * @covers \Papaya\BaseObject\Item::_prepareName
  */
  public function testPropertyValidationExpectingExceptionForUnknown() {
    $item = new \PapayaObjectItem_TestProxy(array());
    $this->expectException(\OutOfBoundsException::class);
    $this->expectExceptionMessage('Property/Index "test" is not defined for item class "PapayaObjectItem_TestProxy".');
    /** @noinspection PhpUndefinedFieldInspection */
    $item->test = 'fail';
  }
}

/**
 * @property string sampleOne
 */
class PapayaObjectItem_TestProxy extends \Papaya\BaseObject\Item {
}
