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

namespace Papaya\Database\Record\Order\By;

require_once __DIR__.'/../../../../../../bootstrap.php';

class FieldsTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\Database\Record\Order\By\Fields::__construct
   * @covers \Papaya\Database\Record\Order\By\Fields::__toString
   */
  public function testWithSimpleField() {
    $orderBy = new Fields(array('field' => -1));
    $this->assertEquals('field ASC', (string)$orderBy);
  }

  /**
   * @covers \Papaya\Database\Record\Order\By\Fields::__construct
   * @covers \Papaya\Database\Record\Order\By\Fields::__toString
   */
  public function testWithTwoFields() {
    $orderBy = new Fields(
      array(
        'field_one' => \Papaya\Database\Interfaces\Order::DESCENDING,
        'field_two' => \Papaya\Database\Interfaces\Order::ASCENDING
      )
    );
    $this->assertEquals('field_one DESC, field_two ASC', (string)$orderBy);
  }

  /**
   * @covers \Papaya\Database\Record\Order\By\Fields::setFields
   */
  public function testSetFieldClearsExistingFields() {
    $orderBy = new Fields(array('field_one' => -1));
    $orderBy->setFields(
      array(
        'field_two' => \Papaya\Database\Interfaces\Order::DESCENDING
      )
    );
    $this->assertEquals('field_two DESC', (string)$orderBy);
  }

  /**
   * @covers \Papaya\Database\Record\Order\By\Fields::getIterator
   */
  public function testIterator() {
    $orderBy = new Fields(
      array(
        'field_one' => \Papaya\Database\Interfaces\Order::DESCENDING,
        'field_two' => \Papaya\Database\Interfaces\Order::ASCENDING
      )
    );
    $this->assertCount(2, iterator_to_array($orderBy));
  }
}
