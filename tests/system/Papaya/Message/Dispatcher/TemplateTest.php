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

use Papaya\Template;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaMessageDispatcherTemplateTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Message\Dispatcher\Template::dispatch
  * @backupGlobals enabled
  */
  public function testDispatch() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Displayable $message */
    $message = $this->createMock(\Papaya\Message\Displayable::class);
    $message
      ->expects($this->once())
      ->method('getType')
      ->will($this->returnValue(\Papaya\Message::SEVERITY_WARNING));
    $message
      ->expects($this->once())
      ->method('getMessage')
      ->will($this->returnValue('Sample message'));
    $values = $this->createMock(Template\Values::class);
    $values
      ->expects($this->once())
      ->method('append')
      ->with(
        '/page/messages',
        'message',
        array(
          'severity' => 'warning'
        ),
        $this->equalTo('Sample message')
      );
    $GLOBALS['PAPAYA_LAYOUT'] = $this->createMock(Template::class);
    $GLOBALS['PAPAYA_LAYOUT']
      ->expects($this->once())
      ->method('values')
      ->will($this->returnValue($values));
    $dispatcher = new \Papaya\Message\Dispatcher\Template();
    $this->assertTrue($dispatcher->dispatch($message));
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Template::dispatch
  */
  public function testDispatchWithInvalidMessageExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message $message */
    $message = $this->createMock(\Papaya\Message::class);
    $dispatcher = new \Papaya\Message\Dispatcher\Template();
    $this->assertFalse($dispatcher->dispatch($message));
  }

  /**
  * @covers \Papaya\Message\Dispatcher\Template::dispatch
  */
  public function testDispatchWithoutGlobalObjectExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Displayable $message */
    $message = $this->createMock(\Papaya\Message\Displayable::class);
    $dispatcher = new \Papaya\Message\Dispatcher\Template();
    $this->assertFalse($dispatcher->dispatch($message));
  }
}
