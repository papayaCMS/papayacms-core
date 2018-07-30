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

class PapayaMessageDebugTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Message\Debug::__construct
  */
  public function testConstructor() {
    $message = new \Papaya\Message\Debug(\Papaya\Message\Logable::GROUP_SYSTEM, 'Sample Message');
    $this->assertAttributeEquals(
      \Papaya\Message\Logable::GROUP_SYSTEM,
      '_group',
      $message
    );
    $this->assertAttributeEquals(
      'Sample Message',
      '_message',
      $message
    );
    $this->assertAttributeInstanceOf(
      \Papaya\Message\Context\Group::class,
      '_context',
      $message
    );
  }

  /**
  * @covers \Papaya\Message\Debug::getGroup
  */
  public function testGetGroup() {
    $message = new \Papaya\Message\Debug();
    $this->assertEquals(
      \Papaya\Message\Logable::GROUP_DEBUG,
      $message->getGroup()
    );
  }


  /**
  * @covers \Papaya\Message\Debug::getType
  */
  public function testGetType() {
    $message = new \Papaya\Message\Debug();
    $this->assertEquals(
      \Papaya\Message::SEVERITY_DEBUG,
      $message->getType()
    );
  }

  /**
  * @covers \Papaya\Message\Debug::context
  */
  public function testContext() {
    $message = new \Papaya\Message\Debug();
    $found = array();
    foreach ($message->context() as $subContext) {
      $found[] = get_class($subContext);
    }
    $this->assertEquals(
      array(
        \Papaya\Message\Context\Memory::class,
        \Papaya\Message\Context\Runtime::class,
        \Papaya\Message\Context\Backtrace::class
      ),
      $found
    );
  }

  /**
  * @covers \Papaya\Message\Debug::getMessage
  */
  public function testGetMessage() {
    $message = new \Papaya\Message\Debug(
      \Papaya\Message\Logable::GROUP_DEBUG,
      'Sample Message'
    );
    $this->assertEquals(
      'Sample Message',
      $message->getMessage()
    );
  }
}
