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

use Papaya\Database\Exception\Query;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaDatabaseExceptionQueryTest extends PapayaTestCase {

  /**
  * @covers Query::__construct
  */
  public function testConstructorWithMessage() {
    $exception = new Query('Sample');
    $this->assertEquals(
      'Sample', $exception->getMessage()
    );
  }

  /**
  * @covers Query::__construct
  */
  public function testConstructorWithCode() {
    $exception = new Query('Sample', 42);
    $this->assertEquals(
      42, $exception->getCode()
    );
  }

  /**
  * @covers Query::__construct
  * @covers Query::getSeverity
  */
  public function testConstructorWithSeverity() {
    $exception = new Query(
      'Sample', 42, \Papaya\Database\Exception::SEVERITY_INFO
    );
    $this->assertEquals(
      \Papaya\Database\Exception::SEVERITY_INFO, $exception->getSeverity()
    );
  }

  /**
  * @covers Query::__construct
  * @covers Query::getSeverity
  */
  public function testConstructorWithNullAsSeverity() {
    $exception = new Query('Sample', 42, NULL);
    $this->assertEquals(
      \Papaya\Database\Exception::SEVERITY_ERROR, $exception->getSeverity()
    );
  }

  /**
  * @covers Query::__construct
  * @covers Query::getStatement
  */
  public function testConstructorWithSql() {
    $exception = new Query(
      'Sample', 42, \Papaya\Database\Exception::SEVERITY_INFO, 'Select SQL'
    );
    $this->assertEquals(
      'Select SQL', $exception->getStatement()
    );
  }
}
