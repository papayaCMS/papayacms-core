<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaObjectItemTest extends PapayaTestCase {

  /**
  * @covers PapayaObjectItem::__construct
  */
  public function testConstructorSetsFields() {
    $item = new PapayaObjectItem_TestProxy(array('sample_one', 'sampleTwo'));
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
  * @covers PapayaObjectItem::assign
  */
  public function testAssign() {
    $item = new PapayaObjectItem_TestProxy(array('sample_one', 'sampleTwo'));
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
  * @covers PapayaObjectItem::clear
  */
  public function testClear() {
    $item = new PapayaObjectItem_TestProxy(array('sample_one', 'sampleTwo'));
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
  * @covers PapayaObjectItem::assign
  */
  public function testAssignExpectingInvalid() {
    $item = new PapayaObjectItem_TestProxy(array('sample_one', 'sampleTwo'));
    $this->setExpectedException(
      'InvalidArgumentException',
      'Argument $data must be an array or instance of Taversable.'
    );
    $item->assign('INVALID');
  }

  /**
  * @covers PapayaObjectItem::toArray
  */
  public function testToArray() {
    $item = new PapayaObjectItem_TestProxy(array('sample_one', 'sample_two'));
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
  * @covers PapayaObjectItem::getIterator
  */
  public function testGetIterator() {
    $item = new PapayaObjectItem_TestProxy(array('sample_one', 'sample_two'));
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
  * @covers PapayaObjectItem::__isset
  */
  public function testMagicMethodIssetExpectingTrue() {
    $item = new PapayaObjectItem_TestProxy(array('sample_one', 'sample_two'));
    $item->sampleOne = 'success';
    $this->assertTrue(isset($item->sampleOne));
  }

  /**
  * @covers PapayaObjectItem::__isset
  */
  public function testMagicMethodIssetExpectingFalse() {
    $item = new PapayaObjectItem_TestProxy(array('sample_one', 'sample_two'));
    $this->assertFalse(isset($item->sampleOne));
  }

  /**
  * @covers PapayaObjectItem::__get
  */
  public function testMagicMethodGetWithoutSetExpectingNull() {
    $item = new PapayaObjectItem_TestProxy(array('sample_one', 'sample_two'));
    $this->assertNull($item->sampleOne);
  }

  /**
  * @covers PapayaObjectItem::__get
  * @covers PapayaObjectItem::__set
  */
  public function testMagicMethodGetAfterSet() {
    $item = new PapayaObjectItem_TestProxy(array('sample_one', 'sample_two'));
    $item->sampleOne = 'success';
    $this->assertEquals('success', $item->sampleOne);
  }

  /**
  * @covers PapayaObjectItem::__unset
  */
  public function testMagicMethodUnsetAfterSet() {
    $item = new PapayaObjectItem_TestProxy(array('sample_one', 'sample_two'));
    $item->sampleOne = 'failed';
    unset($item->sampleOne);
    $this->assertNull($item->sampleOne);
  }

  /**
  * @covers PapayaObjectItem::offsetExists
  */
  public function testOffsetExistsExpectingTrue() {
    $item = new PapayaObjectItem_TestProxy(array('sample_one', 'sample_two'));
    $item->sampleOne = 'success';
    $this->assertTrue(isset($item['sample_one']));
  }

  /**
  * @covers PapayaObjectItem::offsetExists
  */
  public function testOffsetExistsExpectingFalse() {
    $item = new PapayaObjectItem_TestProxy(array('sample_one', 'sample_two'));
    $this->assertFalse(isset($item['unknown_property']));
  }

  /**
  * @covers PapayaObjectItem::offsetGet
  */
  public function testOffsetGetWithoutSet() {
    $item = new PapayaObjectItem_TestProxy(array('sample_one', 'sample_two'));
    $this->assertNull($item['sample_one']);
  }

  /**
  * @covers PapayaObjectItem::offsetGet
  * @covers PapayaObjectItem::offsetSet
  */
  public function testOffsetGetAfterSet() {
    $item = new PapayaObjectItem_TestProxy(array('sample_one', 'sample_two'));
    $item['sample_one'] = 'success';
    $this->assertEquals('success', $item['sample_one']);
  }

  /**
  * @covers PapayaObjectItem::offsetUnset
  */
  public function testOffsetUnset() {
    $item = new PapayaObjectItem_TestProxy(array('sample_one', 'sample_two'));
    $item->sampleOne = 'success';
    unset($item['sample_one']);
    $this->assertNull($item['sample_one']);
  }

  /**
  * @covers PapayaObjectItem::_prepareName
  * @dataProvider provideNameVariants
  */
  public function testPropertyConvert($name) {
    $item = new PapayaObjectItem_TestProxy(array('sample_one'));
    $item['sample_one'] = 'success';
    $this->assertEquals(
      array('sample_one' => 'success'),
      $item->toArray()
    );
  }

  public static function provideNameVariants() {
    return array(
      'lowercase' => array('sample_one'),
      'uppercase' => array('SAMPLE_TWO'),
      'camel case' => array('sampleTwo'),
      'camel case first upper' => array('SampleTwo')
    );
  }

  /**
  * @covers PapayaObjectItem::_prepareName
  */
  public function testPropertyValidationExpectingExceptionForUnknown() {
    $item = new PapayaObjectItem_TestProxy(array());
    try {
      $item->test = 'fail';
    } catch (OutOfBoundsException $e) {
      $this->assertEquals(
        'Property/Index "test" is not defined for item class "PapayaObjectItem_TestProxy".',
        $e->getMessage()
      );
    }
  }
}

class PapayaObjectItem_TestProxy extends PapayaObjectItem {
}