<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaDatabaseRecordOrderFieldTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseRecordOrderField::__construct
  * @covers PapayaDatabaseRecordOrderField::__toString
  */
  public function testSimpleFieldName() {
    $orderBy = new PapayaDatabaseRecordOrderField('field');
    $this->assertEquals('field ASC', (string)$orderBy);
  }

  /**
  * @covers PapayaDatabaseRecordOrderField::__construct
  * @covers PapayaDatabaseRecordOrderField::__toString
  * @covers PapayaDatabaseRecordOrderField::getDirectionString
  */
  public function testFieldNameAndDirection() {
    $orderBy = new PapayaDatabaseRecordOrderField(
      'field', PapayaDatabaseRecordOrderField::DESCENDING
    );
    $this->assertEquals('field DESC', (string)$orderBy);
  }

  /**
  * @covers PapayaDatabaseRecordOrderField::__construct
  * @covers PapayaDatabaseRecordOrderField::__toString
  * @covers PapayaDatabaseRecordOrderField::getDirectionString
  */
  public function testWithInvalidDirectionExpectingAscending() {
    $orderBy = new PapayaDatabaseRecordOrderField(
      'field', -23
    );
    $this->assertEquals('field ASC', (string)$orderBy);
  }
}