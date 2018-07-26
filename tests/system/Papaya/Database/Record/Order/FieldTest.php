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

use Papaya\Database\Record\Order\Field;

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaDatabaseRecordOrderFieldTest extends PapayaTestCase {

  /**
  * @covers Field::__construct
  * @covers Field::__toString
  */
  public function testSimpleFieldName() {
    $orderBy = new Field('field');
    $this->assertEquals('field ASC', (string)$orderBy);
  }

  /**
  * @covers Field::__construct
  * @covers Field::__toString
  * @covers Field::getDirectionString
  */
  public function testFieldNameAndDirection() {
    $orderBy = new Field(
      'field', Field::DESCENDING
    );
    $this->assertEquals('field DESC', (string)$orderBy);
  }

  /**
  * @covers Field::__construct
  * @covers Field::__toString
  * @covers Field::getDirectionString
  */
  public function testWithInvalidDirectionExpectingAscending() {
    $orderBy = new Field(
      'field', -23
    );
    $this->assertEquals('field ASC', (string)$orderBy);
  }
}
