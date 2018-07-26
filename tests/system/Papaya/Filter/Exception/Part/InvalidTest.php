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

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaFilterExceptionPartInvalidTest extends \PapayaTestCase {

  /**
  * @covers \PapayaFilterExceptionPartInvalid::__construct
  */
  public function testConstructor() {
    $e = new \PapayaFilterExceptionPartInvalid(
      3,
      'type',
      'Value is too large. Expecting a maximum of "21", got "42".'
    );
    $this->assertEquals(
      'Part number 3 of type "type" is invalid:'.
        ' Value is too large. Expecting a maximum of "21", got "42".',
      $e->getMessage()
    );
  }

  /**
  * @covers \PapayaFilterExceptionPartInvalid::__construct
  */
  public function testConstructorNoMessage() {
    $e = new \PapayaFilterExceptionPartInvalid(3, 'type');
    $this->assertEquals(
      'Part number 3 of type "type" is invalid.',
      $e->getMessage()
    );
  }

}
