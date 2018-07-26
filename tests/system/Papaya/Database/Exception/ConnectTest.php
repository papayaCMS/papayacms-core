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

use Papaya\Database\Exception\Connect;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaDatabaseExceptionConnectTest extends \PapayaTestCase {

  /**
  * @covers Connect::__construct
  */
  public function testConstructorWithMessage() {
    $exception = new Connect('Sample');
    $this->assertEquals(
      'Sample', $exception->getMessage()
    );
    $this->assertEquals(
      \Papaya\Database\Exception::SEVERITY_ERROR, $exception->getSeverity()
    );
  }

  /**
  * @covers Connect::__construct
  */
  public function testConstructorWithCode() {
    $exception = new Connect('Sample', 42);
    $this->assertEquals(
      42, $exception->getCode()
    );
  }
}
