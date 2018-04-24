<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaDatabaseRecordKeySequenceTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseRecordKeySequence::__construct
  */
  public function testConstructor() {
    $sequence = $this->getSequenceFixture();
    $key = new PapayaDatabaseRecordKeySequence($sequence);
    $this->assertAttributeSame(
      $sequence, '_sequence', $key
    );
    $this->assertEquals(
      array('id'), $key->getProperties()
    );
  }

  /**
  * @covers PapayaDatabaseRecordKeySequence::__construct
  */
  public function testConstructorWithPropertyName() {
    $key = new PapayaDatabaseRecordKeySequence($this->getSequenceFixture(), 'ident');
    $this->assertEquals(
      array('ident'), $key->getProperties()
    );
  }


  /**
  * @covers PapayaDatabaseRecordKeySequence::assign
  * @covers PapayaDatabaseRecordKeySequence::getFilter
  */
  public function testAssignAndGetFilter() {
    $key = new PapayaDatabaseRecordKeySequence($this->getSequenceFixture());
    $this->assertTrue($key->assign(array('id' => 'PROVIDED_SEQUENCE_ID')));
    $this->assertEquals(
      array('id' => 'PROVIDED_SEQUENCE_ID'), $key->getFilter()
    );
  }

  /**
  * @covers PapayaDatabaseRecordKeySequence::assign
  * @covers PapayaDatabaseRecordKeySequence::getFilter
  */
  public function testAssignWithInvalidData() {
    $key = new PapayaDatabaseRecordKeySequence($this->getSequenceFixture());
    $this->assertFalse($key->assign(array('other' => 'PROVIDED_SEQUENCE_ID')));
    $this->assertEquals(
      array('id' => NULL), $key->getFilter()
    );
  }

  /**
  * @covers PapayaDatabaseRecordKeySequence::getFilter
  */
  public function testGetFilterWithoutAssign() {
    $key = new PapayaDatabaseRecordKeySequence($this->getSequenceFixture());
    $this->assertEquals(
      array('id' => NULL), $key->getFilter()
    );
  }

  /**
  * @covers PapayaDatabaseRecordKeySequence::getFilter
  */
  public function testGetFilterWithoutAssignCreatingId() {
    $sequence =$this->getSequenceFixture();
    $sequence
      ->expects($this->once())
      ->method('next')
      ->will($this->returnValue('CREATED_SEQUENCE_ID'));
    $key = new PapayaDatabaseRecordKeySequence($sequence);
    $this->assertEquals(
      array('id' => 'CREATED_SEQUENCE_ID'),
      $key->getFilter(PapayaDatabaseInterfaceKey::ACTION_CREATE)
    );
  }

  /**
  * @covers PapayaDatabaseRecordKeySequence::exists
  */
  public function testExistsExpectingTrue() {
    $key = new PapayaDatabaseRecordKeySequence($this->getSequenceFixture());
    $key->assign(array('id' => 'PROVIDED_SEQUENCE_ID'));
    $this->assertTrue($key->exists());
  }

  /**
  * @covers PapayaDatabaseRecordKeySequence::exists
  */
  public function testExistsExpectingFalse() {
    $key = new PapayaDatabaseRecordKeySequence($this->getSequenceFixture());
    $this->assertFalse($key->exists());
  }

  /**
  * @covers PapayaDatabaseRecordKeySequence::getQualities
  */
  public function testGetQualities() {
    $key = new PapayaDatabaseRecordKeySequence($this->getSequenceFixture());
    $this->assertEquals(PapayaDatabaseInterfaceKey::CLIENT_GENERATED, $key->getQualities());
  }

  /**
  * @covers PapayaDatabaseRecordKeySequence::__toString
  */
  public function testMagicToString() {
    $key = new PapayaDatabaseRecordKeySequence($this->getSequenceFixture());
    $key->assign(array('id' => 'PROVIDED_SEQUENCE_ID'));
    $this->assertSame('PROVIDED_SEQUENCE_ID', (string)$key);
  }

  /**
  * @covers PapayaDatabaseRecordKeySequence::clear
  */
  public function testClear() {
    $key = new PapayaDatabaseRecordKeySequence($this->getSequenceFixture());
    $key->assign(array('id' => 42));
    $key->clear();
    $this->assertSame('', (string)$key);
  }

  /**
  * @covers PapayaDatabaseRecordKeySequence::getProperties
  */
  public function testGetProperties() {
    $key = new PapayaDatabaseRecordKeySequence($this->getSequenceFixture());
    $this->assertEquals(array('id'), $key->getProperties());
  }

  private function getSequenceFixture() {
    $sequence = $this
      ->getMockBuilder(PapayaDatabaseSequence::class)
      ->disableOriginalConstructor()
      ->getMock();
    return $sequence;
  }
}
