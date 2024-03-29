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

namespace Papaya\Message\Dispatcher;

require_once __DIR__.'/../../../../bootstrap.php';

class TemplateTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\Message\Dispatcher\Template::dispatch
   */
  public function testDispatch() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Displayable $message */
    $message = $this->createMock(\Papaya\Message\Displayable::class);
    $message
      ->expects($this->once())
      ->method('getSeverity')
      ->will($this->returnValue(\Papaya\Message::SEVERITY_WARNING));
    $message
      ->expects($this->once())
      ->method('getMessage')
      ->will($this->returnValue('Sample message'));
    $values = $this->createMock(\Papaya\Template\Values::class);
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
    $template = $this->createMock(\Papaya\Template::class);
    $template
      ->expects($this->once())
      ->method('values')
      ->will($this->returnValue($values));
    $messageManager = $this->createMock(\Papaya\Message\Manager::class);
    $messageManager
      ->expects($this->any())
      ->method('getTemplate')
      ->willReturn($template);

    $application = $this->mockPapaya()->application(['messages' => $messageManager]);
    $dispatcher = new Template();
    $dispatcher->papaya($application);
    $this->assertTrue($dispatcher->dispatch($message));
  }

  /**
   * @covers \Papaya\Message\Dispatcher\Template::dispatch
   */
  public function testDispatchWithInvalidMessageExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message $message */
    $message = $this->createMock(\Papaya\Message::class);
    $dispatcher = new Template();
    $this->assertFalse($dispatcher->dispatch($message));
  }

  /**
   * @covers \Papaya\Message\Dispatcher\Template::dispatch
   */
  public function testDispatchWithoutGlobalObjectExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Displayable $message */
    $message = $this->createMock(\Papaya\Message\Displayable::class);
    $dispatcher = new Template();
    $this->assertFalse($dispatcher->dispatch($message));
  }
}
