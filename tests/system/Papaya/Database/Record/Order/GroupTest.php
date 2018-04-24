<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaDatabaseRecordOrderGroupTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseRecordOrderGroup
  */
  public function testConstructorWithTwoLists() {
    $group = new PapayaDatabaseRecordOrderGroup(
      $this->getListFixture(array('one', 'two')),
      $this->getListFixture(array('three', 'four'))
    );
    $this->assertEquals('one, two, three, four', (string)$group);
  }

  /**
  * @covers PapayaDatabaseRecordOrderGroup
  */
  public function testAdd() {
    $group = new PapayaDatabaseRecordOrderGroup();
    $group->add(
      $this->getListFixture(array('one', 'two'))
    );
    $this->assertEquals('one, two', (string)$group);
  }

  /**
  * @covers PapayaDatabaseRecordOrderGroup
  */
  public function testAddMovesExistingToEnd() {
    $group = new PapayaDatabaseRecordOrderGroup(
      $list = $this->getListFixture(array('one', 'two')),
      $this->getListFixture(array('three', 'four'))
    );
    $group->add($list);
    $this->assertEquals('three, four, one, two', (string)$group);
  }

  /**
  * @covers PapayaDatabaseRecordOrderGroup
  */
  public function testRemove() {
    $group = new PapayaDatabaseRecordOrderGroup(
      $this->getListFixture(array('one', 'two')),
      $list = $this->getListFixture(array('three', 'four'))
    );
    $group->remove($list);
    $this->assertEquals('one, two', (string)$group);
  }

  /*********************
   * Fixtures
   ********************/

  private function getListFixture(array $fieldNames = array()) {
    $fields = array();
    foreach ($fieldNames as $name) {
      $fields[] = $field = $this
        ->getMockBuilder('PapayaDatabaseRecordOrderField')
        ->disableOriginalConstructor()
        ->getMock();
      $field
        ->expects($this->any())
        ->method('__toString')
        ->will($this->returnValue($name));
    }
    $result = $this
      ->getMockBuilder('PapayaDatabaseRecordOrderByFields')
      ->disableOriginalConstructor()
      ->getMock();
    $result
      ->expects($this->any())
      ->method('getIterator')
      ->will($this->returnValue(new ArrayIterator($fields)));
    return $result;
  }
}
