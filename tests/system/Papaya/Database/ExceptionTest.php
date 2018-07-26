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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaDatabaseExceptionTest extends PapayaTestCase {

  /**
  * @covers \PapayaDatabaseException::__construct
  */
  public function testConstructorWithMessage() {
    $exception = new \PapayaDatabaseException('Sample');
    $this->assertEquals(
      'Sample', $exception->getMessage()
    );
  }

  /**
  * @covers \PapayaDatabaseException::__construct
  */
  public function testConstructorWithCode() {
    $exception = new \PapayaDatabaseException('Sample', 42);
    $this->assertEquals(
      42, $exception->getCode()
    );
  }

  /**
  * @covers \PapayaDatabaseException::__construct
  * @covers \PapayaDatabaseException::getSeverity
  */
  public function testConstructorWithSeverity() {
    $exception = new \PapayaDatabaseException('Sample', 42, \PapayaDatabaseException::SEVERITY_INFO);
    $this->assertEquals(
      \PapayaDatabaseException::SEVERITY_INFO, $exception->getSeverity()
    );
  }

  /**
  * @covers \PapayaDatabaseException::__construct
  * @covers \PapayaDatabaseException::getSeverity
  */
  public function testConstructorWithNullAsSeverity() {
    $exception = new \PapayaDatabaseException('Sample', 42, NULL);
    $this->assertEquals(
      \PapayaDatabaseException::SEVERITY_ERROR, $exception->getSeverity()
    );
  }
}
