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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaFilterExceptionLengthTest extends PapayaTestCase {

  /**
  * @covers \PapayaFilterExceptionLength::__construct
  */
  public function testConstructor() {
    $e = new \PapayaFilterExceptionLength_TestProxy('Length Error', 42, 21);
    $this->assertEquals(
      'Length Error',
      $e->getMessage()
    );
  }

  /**
  * @covers \PapayaFilterExceptionLength::getExpectedLength
  */
  public function testGetExpectedLength() {
    $e = new \PapayaFilterExceptionLength_TestProxy('Length Error', 42, 21);
    $this->assertEquals(
      42,
      $e->getExpectedLength()
    );
  }

  /**
  * @covers \PapayaFilterExceptionLength::getActualLength
  */
  public function testgetActualLength() {
    $e = new \PapayaFilterExceptionLength_TestProxy('Length Error', 42, 21);
    $this->assertEquals(
      21,
      $e->getActualLength()
    );
  }
}

class PapayaFilterExceptionLength_TestProxy extends PapayaFilterExceptionLength {

}
