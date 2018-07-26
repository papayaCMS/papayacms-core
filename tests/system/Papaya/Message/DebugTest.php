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
  * @covers \PapayaMessageDebug::__construct
  */
  public function testConstructor() {
    $message = new \PapayaMessageDebug(\PapayaMessageLogable::GROUP_SYSTEM, 'Sample Message');
    $this->assertAttributeEquals(
      \PapayaMessageLogable::GROUP_SYSTEM,
      '_group',
      $message
    );
    $this->assertAttributeEquals(
      'Sample Message',
      '_message',
      $message
    );
    $this->assertAttributeInstanceOf(
      \PapayaMessageContextGroup::class,
      '_context',
      $message
    );
  }

  /**
  * @covers \PapayaMessageDebug::getGroup
  */
  public function testGetGroup() {
    $message = new \PapayaMessageDebug();
    $this->assertEquals(
      \PapayaMessageLogable::GROUP_DEBUG,
      $message->getGroup()
    );
  }


  /**
  * @covers \PapayaMessageDebug::getType
  */
  public function testGetType() {
    $message = new \PapayaMessageDebug();
    $this->assertEquals(
      Papaya\Message::SEVERITY_DEBUG,
      $message->getType()
    );
  }

  /**
  * @covers \PapayaMessageDebug::context
  */
  public function testContext() {
    $message = new \PapayaMessageDebug();
    $found = array();
    foreach ($message->context() as $subContext) {
      $found[] = get_class($subContext);
    }
    $this->assertEquals(
      array(
        \PapayaMessageContextMemory::class,
        \PapayaMessageContextRuntime::class,
        \PapayaMessageContextBacktrace::class
      ),
      $found
    );
  }

  /**
  * @covers \PapayaMessageDebug::getMessage
  */
  public function testGetMessage() {
    $message = new \PapayaMessageDebug(
      \PapayaMessageLogable::GROUP_DEBUG,
      'Sample Message'
    );
    $this->assertEquals(
      'Sample Message',
      $message->getMessage()
    );
  }
}
