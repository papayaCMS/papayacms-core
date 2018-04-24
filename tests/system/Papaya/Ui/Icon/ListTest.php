<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiIconListTest extends PapayaTestCase {

  /**
  * @covers PapayaUiIconList::offsetExists
  */
  public function testOffsetExists() {
    $list = new PapayaUiIconList();
    $list['sample'] = new PapayaUiIcon('sample.png');
    $this->assertTrue(isset($list['sample']));
  }

  /**
  * @covers PapayaUiIconList::offsetGet
  */
  public function testOffsetGet() {
    $list = new PapayaUiIconList();
    $list['sample'] = $icon = new PapayaUiIcon('sample.png');
    $this->assertSame($icon, $list['sample']);
  }

  /**
  * @covers PapayaUiIconList::offsetSet
  */
  public function testOffsetSet() {
    $list = new PapayaUiIconList();
    $list['sample'] = $icon = new PapayaUiIcon('sample.png');
    $this->assertAttributeSame(array('sample' => $icon), '_icons', $list);
  }

  /**
  * @covers PapayaUiIconList::offsetSet
  */
  public function testOffsetSetWithoutIndexExpectingException() {
    $list = new PapayaUiIconList();
    $this->setExpectedException(
      'InvalidArgumentException',
      'InvalidArgumentException: Please provide a valid offset for the icon.'
    );
    $list[] = new PapayaUiIcon('sample.png');
  }

  /**
  * @covers PapayaUiIconList::offsetSet
  */
  public function testOffsetSetWithInvalidIconExpectingException() {
    $list = new PapayaUiIconList();
    $this->setExpectedException(
      'InvalidArgumentException',
      'InvalidArgumentException: Please provide an instance of PapayaUiIcon.'
    );
    $list['sample'] = 'X';
  }

  /**
  * @covers PapayaUiIconList::offsetUnset
  */
  public function testOffsetUnset() {
    $list = new PapayaUiIconList();
    $list['sample'] = new PapayaUiIcon('sample.png');
    unset($list['sample']);
    $this->assertFalse(isset($list['sample']));
  }

  /**
  * @covers PapayaUiIconList::count
  */
  public function testCountExpectingZero() {
    $list = new PapayaUiIconList();
    $this->assertEquals(0, count($list));
  }

  /**
  * @covers PapayaUiIconList::count
  */
  public function testCountExpectingTwo() {
    $list = new PapayaUiIconList();
    $list['one'] = $icon = new PapayaUiIcon('one.png');
    $list['two'] = $icon = new PapayaUiIcon('two.png');
    $this->assertEquals(2, count($list));
  }

  /**
  * @covers PapayaUiIconList::getIterator
  */
  public function testGetIterator() {
    $list = new PapayaUiIconList();
    $list['sample'] = $icon = new PapayaUiIcon('sample.png');
    $this->assertSame(array('sample' => $icon), $list->getIterator()->getArrayCopy());
  }
}
