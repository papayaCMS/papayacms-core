<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaDatabaseRecordOrderByFieldsTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseRecordOrderByFields::__construct
  * @covers PapayaDatabaseRecordOrderByFields::__toString
  */
  public function testWithSimpleField() {
    $orderBy = new PapayaDatabaseRecordOrderByFields(array('field' => -1));
    $this->assertEquals('field ASC', (string)$orderBy);
  }

  /**
  * @covers PapayaDatabaseRecordOrderByFields::__construct
  * @covers PapayaDatabaseRecordOrderByFields::__toString
  */
  public function testWithTwoFields() {
    $orderBy = new PapayaDatabaseRecordOrderByFields(
      array(
        'field_one' => PapayaDatabaseInterfaceOrder::DESCENDING,
        'field_two' => PapayaDatabaseInterfaceOrder::ASCENDING
      )
    );
    $this->assertEquals('field_one DESC, field_two ASC', (string)$orderBy);
  }

  /**
  * @covers PapayaDatabaseRecordOrderByFields::setFields
  */
  public function testSetFieldClearsExistingFields() {
    $orderBy = new PapayaDatabaseRecordOrderByFields(array('field_one' => -1));
    $orderBy->setFields(
      array(
        'field_two' => PapayaDatabaseInterfaceOrder::DESCENDING
      )
    );
    $this->assertEquals('field_two DESC', (string)$orderBy);
  }

  /**
  * @covers PapayaDatabaseRecordOrderByFields::getIterator
  */
  public function testIterator() {
    $orderBy = new PapayaDatabaseRecordOrderByFields(
      array(
        'field_one' => PapayaDatabaseInterfaceOrder::DESCENDING,
        'field_two' => PapayaDatabaseInterfaceOrder::ASCENDING
      )
    );
    $this->assertCount(2, iterator_to_array($orderBy));
  }
}
