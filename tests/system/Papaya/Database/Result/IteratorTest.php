<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaDatabaseResultIteratorTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseResultIterator::__construct
  */
  public function testConstructor() {
    $iterator = new PapayaDatabaseResultIterator(
      $databaseResult = $this->createMock(PapayaDatabaseResult::class)
    );
    $this->assertAttributeSame(
      $databaseResult, '_databaseResult', $iterator
    );
  }

  /**
  * @covers PapayaDatabaseResultIterator::__construct
  */
  public function testConstructorWithAllParameters() {
    $iterator = new PapayaDatabaseResultIterator(
      $this->createMock(PapayaDatabaseResult::class),
      PapayaDatabaseResult::FETCH_ORDERED
    );
    $this->assertAttributeSame(
      PapayaDatabaseResult::FETCH_ORDERED, '_fetchMode', $iterator
    );
  }

  /**
  * @covers PapayaDatabaseResultIterator::rewind
  * @covers PapayaDatabaseResultIterator::key
  * @covers PapayaDatabaseResultIterator::current
  * @covers PapayaDatabaseResultIterator::next
  * @covers PapayaDatabaseResultIterator::valid
  */
  public function testIterate() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will(
        $this->onConsecutiveCalls(
          array('id' => 21),
          array('id' => 42),
          FALSE
        )
      );
    $databaseResult
      ->expects($this->any())
      ->method('seek')
      ->with(0);
    $iterator = new PapayaDatabaseResultIterator($databaseResult);
    $this->assertEquals(
      array(
        0 => array('id' => 21),
        1 => array('id' => 42)
      ),
      iterator_to_array($iterator)
    );
  }

  /**
  * @covers PapayaDatabaseResultIterator::current
  */
  public function testIterateWithMapping() {
    $mapping = $this->createMock(PapayaDatabaseInterfaceMapping::class);
    $mapping
      ->expects($this->any())
      ->method('mapFieldsToProperties')
      ->with($this->isType('array'))
      ->will($this->returnCallback(array($this, 'callbackMapFieldsToProperties')));
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will(
        $this->onConsecutiveCalls(
          array('id' => 21),
          FALSE
        )
      );
    $databaseResult
      ->expects($this->any())
      ->method('seek')
      ->with(0);
    $iterator = new PapayaDatabaseResultIterator($databaseResult);
    $iterator->setMapping($mapping);
    $this->assertEquals(
      array(
        0 => array('identifier' => 21)
      ),
      iterator_to_array($iterator)
    );
  }

  public function callbackMapFieldsToProperties($record) {
    return array('identifier' => $record['id']);
  }

  /**
  * @covers PapayaDatabaseResultIterator::rewind
  */
  public function testRewindAfterIteration() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will(
        $this->onConsecutiveCalls(
          array('id' => 21),
          array('id' => 42),
          FALSE,
          array('id' => 23),
          FALSE
        )
      );
    $databaseResult
      ->expects($this->any())
      ->method('seek')
      ->with(0);
    $iterator = new PapayaDatabaseResultIterator($databaseResult);
    iterator_to_array($iterator);
    $this->assertEquals(
      array(
        0 => array('id' => 23)
      ),
      iterator_to_array($iterator)
    );
  }

  /**
  * @covers PapayaDatabaseResultIterator::setMapping
  * @covers PapayaDatabaseResultIterator::getMapping
  */
  public function testSetMappingGetAfterSet() {
    $iterator = new PapayaDatabaseResultIterator($this->createMock(PapayaDatabaseResult::class));
    $iterator->setMapping($mapping = $this->createMock(PapayaDatabaseInterfaceMapping::class));
    $this->assertSame(
      $mapping, $iterator->getMapping()
    );
  }
}
