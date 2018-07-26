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


class PapayaMessagePhpExceptionTest extends \PapayaTestCase {

  /**
  * @covers \PapayaMessagePhpException::__construct
  */
  public function testConstructor() {
    $message = new \PapayaMessagePhpException(
      new ErrorException('Sample Error', 0, E_USER_ERROR, 'sample.php', 42)
    );
    $this->assertAttributeEquals(
      \Papaya\Message::SEVERITY_ERROR,
      '_type',
      $message
    );
    $this->assertAttributeEquals(
      'Sample Error',
      '_message',
      $message
    );
    $this->assertCount(1, $message->context());
  }
}
