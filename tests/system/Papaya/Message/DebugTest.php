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

class DebugTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Message\Debug::__construct
   */
  public function testConstructor() {
    $message = new Debug(Logable::GROUP_SYSTEM, 'Sample Message');
    $this->assertAttributeEquals(
      Logable::GROUP_SYSTEM,
      '_group',
      $message
    );
    $this->assertAttributeEquals(
      'Sample Message',
      '_message',
      $message
    );
    $this->assertAttributeInstanceOf(
      Context\Group::class,
      '_context',
      $message
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
   * @covers \Papaya\Message\Debug::getType
   */
  public function testGetType() {
    $message = new Debug();
    $this->assertEquals(
      \Papaya\Message::SEVERITY_DEBUG,
      $message->getType()
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
