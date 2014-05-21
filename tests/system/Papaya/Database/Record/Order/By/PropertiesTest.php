<?php
require_once(dirname(__FILE__).'/../../../../../../bootstrap.php');

class PapayaDatabaseRecordOrderByPropertiesTest extends PapayaTestCase {

  /**
  * @covers PapayaDatabaseRecordOrderByProperties::__construct
  * @covers PapayaDatabaseRecordOrderByProperties::__toString
  */
  public function testWithSimpleField() {
    $orderBy = new PapayaDatabaseRecordOrderByProperties(
      array('property' => -1),
      $this->getMappingFixture(array('property' => 'field'))
    );
    $this->assertEquals('field ASC', (string)$orderBy);
  }

  /**
  * @covers PapayaDatabaseRecordOrderByProperties::__construct
  * @covers PapayaDatabaseRecordOrderByProperties::__toString
  */
  public function testWithTwoProperties() {
    $orderBy = new PapayaDatabaseRecordOrderByProperties(
      array(
        'one' => PapayaDatabaseInterfaceOrder::DESCENDING,
        'two' => PapayaDatabaseInterfaceOrder::ASCENDING
      ),
      $this->getMappingFixture(
        array('one' => 'field_one', 'two' => 'field_two')
      )
    );
    $this->assertEquals('field_one DESC, field_two ASC', (string)$orderBy);
  }

  /**
  * @covers PapayaDatabaseRecordOrderByProperties::__construct
  * @covers PapayaDatabaseRecordOrderByProperties::__toString
  * @covers PapayaDatabaseRecordOrderByProperties::setProperties
  */
  public function testWithTwoPropertiesOneWithoutMapping() {
    $orderBy = new PapayaDatabaseRecordOrderByProperties(
      array(
        'one' => PapayaDatabaseInterfaceOrder::DESCENDING,
        'two' => PapayaDatabaseInterfaceOrder::ASCENDING
      ),
      $this->getMappingFixture(
        array('one' => 'field_one')
      )
    );
    $this->assertEquals('field_one DESC', (string)$orderBy);
  }

  /**
  * @covers PapayaDatabaseRecordOrderByProperties::setProperties
  */
  public function testSetFieldClearsExistingProperties() {
    $orderBy = new PapayaDatabaseRecordOrderByProperties(
      array(
        'one' => PapayaDatabaseInterfaceOrder::ASCENDING
      ),
      $this->getMappingFixture(
        array('one' => 'field_one', 'two' => 'field_two')
      )
    );
    $orderBy->setProperties(
      array(
        'two' => PapayaDatabaseInterfaceOrder::DESCENDING
      )
    );
    $this->assertEquals('field_two DESC', (string)$orderBy);
  }

  /**
  * @covers PapayaDatabaseRecordOrderByProperties::getIterator
  */
  public function testIterator() {
    $orderBy = new PapayaDatabaseRecordOrderByProperties(
      array(
        'one' => PapayaDatabaseInterfaceOrder::DESCENDING,
        'two' => PapayaDatabaseInterfaceOrder::ASCENDING
      ),
      $this->getMappingFixture(
        array('one' => 'field_one', 'two' => 'field_two')
      )
    );
    $this->assertCount(2, iterator_to_array($orderBy));
  }

  /*********************
   * Fixtures
   ********************/

  private function getMappingFixture(array $mapping) {
    $valueMap = array();
    foreach ($mapping as $property => $field) {
      $valueMap[] = array($property, TRUE, $field);
    }
    $mapping = $this->getMock('PapayaDatabaseInterfaceMapping');
    $mapping
      ->expects($this->any())
      ->method('getField')
      ->will($this->returnValueMap($valueMap));
    return $mapping;
  }
}