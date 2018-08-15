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

namespace Papaya\Database;

require_once __DIR__.'/../../../bootstrap.php';

class ExceptionTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Database\Exception::__construct
   */
  public function testConstructorWithMessage() {
    $exception = new Exception('Sample');
    $this->assertEquals(
      'Sample', $exception->getMessage()
    );
  }

  /**
   * @covers \Papaya\Database\Exception::__construct
   */
  public function testConstructorWithCode() {
    $exception = new Exception('Sample', 42);
    $this->assertEquals(
      42, $exception->getCode()
    );
  }

  /**
   * @covers \Papaya\Database\Exception::__construct
   * @covers \Papaya\Database\Exception::getSeverity
   */
  public function testConstructorWithSeverity() {
    $exception = new Exception('Sample', 42, Exception::SEVERITY_INFO);
    $this->assertEquals(
      Exception::SEVERITY_INFO, $exception->getSeverity()
    );
  }

  /**
   * @covers \Papaya\Database\Exception::__construct
   * @covers \Papaya\Database\Exception::getSeverity
   */
  public function testConstructorWithNullAsSeverity() {
    $exception = new Exception('Sample', 42, NULL);
    $this->assertEquals(
      Exception::SEVERITY_ERROR, $exception->getSeverity()
    );
  }
}
