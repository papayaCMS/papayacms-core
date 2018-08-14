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

use Papaya\Database\Interfaces\Mapping;
use Papaya\Database\Interfaces\Order;
use Papaya\Database\Record\Order\By\Properties;

require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaDatabaseRecordOrderByPropertiesTest extends \PapayaTestCase {

  /**
  * @covers Properties::__construct
  * @covers Properties::__toString
  */
  public function testWithSimpleField() {
    $orderBy = new Properties(
      array('property' => -1),
      $this->getMappingFixture(array('property' => 'field'))
    );
    $this->assertEquals('field ASC', (string)$orderBy);
  }

  /**
  * @covers Properties::__construct
  * @covers Properties::__toString
  */
  public function testWithTwoProperties() {
    $orderBy = new Properties(
      array(
        'one' => Order::DESCENDING,
        'two' => Order::ASCENDING
      ),
      $this->getMappingFixture(
        array('one' => 'field_one', 'two' => 'field_two')
      )
    );
    $this->assertEquals('field_one DESC, field_two ASC', (string)$orderBy);
  }

  /**
  * @covers Properties::__construct
  * @covers Properties::__toString
  * @covers Properties::setProperties
  */
  public function testWithTwoPropertiesOneWithoutMapping() {
    $orderBy = new Properties(
      array(
        'one' => Order::DESCENDING,
        'two' => Order::ASCENDING
      ),
      $this->getMappingFixture(
        array('one' => 'field_one')
      )
    );
    $this->assertEquals('field_one DESC', (string)$orderBy);
  }

  /**
  * @covers Properties::setProperties
  */
  public function testSetFieldClearsExistingProperties() {
    $orderBy = new Properties(
      array(
        'one' => Order::ASCENDING
      ),
      $this->getMappingFixture(
        array('one' => 'field_one', 'two' => 'field_two')
      )
    );
    $orderBy->setProperties(
      array(
        'two' => Order::DESCENDING
      )
    );
    $this->assertEquals('field_two DESC', (string)$orderBy);
  }

  /**
  * @covers Properties::getIterator
  */
  public function testIterator() {
    $orderBy = new Properties(
      array(
        'one' => Order::DESCENDING,
        'two' => Order::ASCENDING
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
   * @return array|\PHPUnit_Framework_MockObject_MockObject|Mapping
   */
  private function getMappingFixture(array $mappingData) {
    $valueMap = array();
    foreach ($mappingData as $property => $field) {
      $valueMap[] = array($property, TRUE, $field);
    }
    $mapping = $this->createMock(Mapping::class);
    $mapping
      ->expects($this->any())
      ->method('getField')
      ->will($this->returnValueMap($valueMap));
    return $mapping;
  }
}
