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

namespace Papaya\Message;
require_once __DIR__.'/../../../bootstrap.php';

class DisplayTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Message\Display::__construct
   * @covers \Papaya\Message\Display::_isValidSeverity
   */
  public function testConstructor() {
    $message = new Display(\Papaya\Message::SEVERITY_WARNING, 'Sample Message');
    $this->assertSame(
      \Papaya\Message::SEVERITY_WARNING,
      $message->getSeverity()
    );
    $this->assertSame(
      'Sample Message',
      $message->getMessage()
    );
  }

  /**
   * @covers \Papaya\Message\Display::__construct
   * @covers \Papaya\Message\Display::_isValidSeverity
   */
  public function testConstructorWithInvalidTypeExpectingException() {
    $this->expectException(\InvalidArgumentException::class);
    new Display(\Papaya\Message::SEVERITY_DEBUG, 'Sample Message');
  }

  /**
   * @covers \Papaya\Message\Display::getType
   */
  public function testGetType() {
    $message = new Display(\Papaya\Message::SEVERITY_WARNING, 'Sample Message');
    $this->assertEquals(
      \Papaya\Message::SEVERITY_WARNING,
      $message->getType()
    );
  }

  /**
   * @covers \Papaya\Message\Display::getMessage
   */
  public function testGetMessage() {
    $message = new Display(\Papaya\Message::SEVERITY_WARNING, 'Sample Message');
    $this->assertEquals(
      'Sample Message',
      $message->getMessage()
    );
  }

}
