<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaDatabaseRecordKeyAutoincrementTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseRecordKeyAutoincrement::__construct
  */
  public function testConstructor() {
    $key = new PapayaDatabaseRecordKeyAutoincrement();
    $this->assertEquals(
      array('id'), $key->getProperties()
    );
  }

  /**
  * @covers PapayaDatabaseRecordKeyAutoincrement::__construct
  * @covers PapayaDatabaseRecordKeyAutoincrement::getProperties
  */
  public function testConstructorWithPropertyParameter() {
    $key = new PapayaDatabaseRecordKeyAutoincrement('other');
    $this->assertEquals(
      array('other'), $key->getProperties()
    );
  }

  /**
  * PapayaDatabaseRecordKeyAutoincrement::assign
  * PapayaDatabaseRecordKeyAutoincrement::getFilter
  */
  public function testAssignAndGetFilter() {
    $key = new PapayaDatabaseRecordKeyAutoincrement();
    $this->assertTrue($key->assign(array('id' => 42)));
    $this->assertEquals(
      array('id' => 42), $key->getFilter()
    );
  }

  /**
  * @covers PapayaDatabaseRecordKeyAutoincrement::assign
  * @covers PapayaDatabaseRecordKeyAutoincrement::getFilter
  */
  public function testAssignWithInvalidData() {
    $key = new PapayaDatabaseRecordKeyAutoincrement();
    $this->assertFalse($key->assign(array('other' => 42)));
    $this->assertEquals(
      array('id' => NULL), $key->getFilter()
    );
  }

  /**
  * @covers PapayaDatabaseRecordKeyAutoincrement::getFilter
  */
  public function testGetFilterWithoutAssign() {
    $key = new PapayaDatabaseRecordKeyAutoincrement();
    $this->assertEquals(
      array('id' => NULL), $key->getFilter()
    );
  }

  /**
  * @covers PapayaDatabaseRecordKeyAutoincrement::exists
  */
  public function testExistsExpectingTrue() {
    $key = new PapayaDatabaseRecordKeyAutoincrement();
    $key->assign(array('id' => 42));
    $this->assertTrue($key->exists());
  }

  /**
  * @covers PapayaDatabaseRecordKeyAutoincrement::exists
  */
  public function testExistsExpectingFalse() {
    $key = new PapayaDatabaseRecordKeyAutoincrement();
    $this->assertFalse($key->exists());
  }

  /**
  * @covers PapayaDatabaseRecordKeyAutoincrement::getQualities
  */
  public function testGetQualities() {
    $key = new PapayaDatabaseRecordKeyAutoincrement();
    $this->assertEquals(PapayaDatabaseInterfaceKey::DATABASE_PROVIDED, $key->getQualities());
  }

  /**
  * @covers PapayaDatabaseRecordKeyAutoincrement::__toString
  */
  public function testMagicToString() {
    $key = new PapayaDatabaseRecordKeyAutoincrement();
    $key->assign(array('id' => 42));
    $this->assertSame('42', (string)$key);
  }

  /**
  * @covers PapayaDatabaseRecordKeyAutoincrement::clear
  */
  public function testClear() {
    $key = new PapayaDatabaseRecordKeyAutoincrement();
    $key->assign(array('id' => 42));
    $key->clear();
    $this->assertSame('', (string)$key);
  }
}
