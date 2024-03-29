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

class DebugTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\Message\Debug::__construct
   */
  public function testConstructor() {
    $message = new Debug(Logable::GROUP_SYSTEM, 'Sample Message');
    $this->assertEquals(
      Logable::GROUP_SYSTEM,
      $message->getGroup()
    );
    $this->assertEquals(
      'Sample Message',
      $message->getMessage()
    );
    $this->assertInstanceOf(
      Context\Group::class,
      $message->context()
    );
  }

  /**
   * @covers \Papaya\Message\Debug::getGroup
   */
  public function testGetGroup() {
    $message = new Debug();
    $this->assertEquals(
      Logable::GROUP_DEBUG,
      $message->getGroup()
    );
  }


  /**
   * @covers \Papaya\Message\Debug::getSeverity
   */
  public function testGetSeverity() {
    $message = new Debug();
    $this->assertEquals(
      \Papaya\Message::SEVERITY_DEBUG,
      $message->getSeverity()
    );
  }

  /**
   * @covers \Papaya\Message\Debug::context
   */
  public function testContext() {
    $message = new Debug();
    $found = array();
    foreach ($message->context() as $subContext) {
      $found[] = get_class($subContext);
    }
    $this->assertEquals(
      array(
        Context\Memory::class,
        Context\Runtime::class,
        Context\Backtrace::class
      ),
      $found
    );
  }

  /**
   * @covers \Papaya\Message\Debug::getMessage
   */
  public function testGetMessage() {
    $message = new Debug(
      Logable::GROUP_DEBUG,
      'Sample Message'
    );
    $this->assertEquals(
      'Sample Message',
      $message->getMessage()
    );
  }
}
