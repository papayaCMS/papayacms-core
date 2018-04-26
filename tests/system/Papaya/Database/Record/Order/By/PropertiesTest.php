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

require_once __DIR__.'/../../../../../../bootstrap.php';

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

  /**
   * @param array $mappingData
   * @return array|PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseInterfaceMapping
   */
  private function getMappingFixture(array $mappingData) {
    $valueMap = array();
    foreach ($mappingData as $property => $field) {
      $valueMap[] = array($property, TRUE, $field);
    }
    $mapping = $this->createMock(PapayaDatabaseInterfaceMapping::class);
    $mapping
      ->expects($this->any())
      ->method('getField')
      ->will($this->returnValueMap($valueMap));
    return $mapping;
  }
}
