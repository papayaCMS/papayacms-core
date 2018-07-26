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

use Papaya\Database\Interfaces\Order;

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
        'field_one' => Order::DESCENDING,
        'field_two' => Order::ASCENDING
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
        'field_two' => Order::DESCENDING
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
        'field_one' => Order::DESCENDING,
        'field_two' => Order::ASCENDING
      )
    );
    $this->assertCount(2, iterator_to_array($orderBy));
  }
}
