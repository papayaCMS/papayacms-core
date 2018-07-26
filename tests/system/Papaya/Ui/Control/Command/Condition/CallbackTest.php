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

require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiControlCommandConditionCallbackTest extends \PapayaTestCase {

  /**
  * @covers \PapayaUiControlCommandConditionCallback::__construct
  */
  public function testConstructorExpectingException() {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('InvalidArgumentException: provided $callback is not callable.');
    /** @noinspection PhpParamsInspection */
    new \PapayaUiControlCommandConditionCallback(23);
  }

  /**
  * @covers \PapayaUiControlCommandConditionCallback::__construct
  * @covers \PapayaUiControlCommandConditionCallback::validate
  */
  public function testValidateExpectingTrue() {
    $condition = new \PapayaUiControlCommandConditionCallback(function() { return TRUE; });
    $this->assertTrue($condition->validate());
  }

  /**
  * @covers \PapayaUiControlCommandConditionCallback::__construct
  * @covers \PapayaUiControlCommandConditionCallback::validate
  */
  public function testValidateExpectingFalse() {
    $condition = new \PapayaUiControlCommandConditionCallback(function() { return FALSE; });
    $this->assertFalse($condition->validate());
  }
}
