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

class PapayaMessageExceptionTest extends \PapayaTestCase {

  /**
  * @covers \PapayaMessageException::__construct
  */
  public function testConstructor() {
    $message = new \PapayaMessageException(
      new \PapayaMessageException_Exception('Sample Error')
    );
    $this->assertAttributeEquals(
      \PapayaMessage::SEVERITY_ERROR,
      '_type',
      $message
    );
    $this->assertStringStartsWith(
      "Uncaught exception 'PapayaMessageException_Exception' with message 'Sample Error' in '",
      $this->readAttribute($message, '_message')
    );
    $this->assertCount(1, $message->context());
  }
}

class PapayaMessageException_Exception extends Exception {
}
