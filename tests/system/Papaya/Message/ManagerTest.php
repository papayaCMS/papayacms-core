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

namespace Papaya\Message {

  use Papaya\Template;
  use Papaya\TestCase;

  require_once __DIR__.'/../../../bootstrap.php';

  /**
   * @covers \Papaya\Message\Manager
   */
  class ManagerTest extends TestCase {

    public function testAddDispatcher() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Dispatcher $dispatcher */
      $dispatcher = $this->createMock(Dispatcher::class);
      $manager = new Manager();
      $manager->addDispatcher($dispatcher);
      $this->assertAttributeEquals(
        [$dispatcher],
        '_dispatchers',
        $manager
      );
    }

    public function testDispatch() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message $message */
      $message = $this->createMock(\Papaya\Message::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject|Dispatcher $dispatcher */
      $dispatcher = $this->createMock(Dispatcher::class);
      $dispatcher
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->equalTo($message));
      $manager = new Manager();
      $manager->addDispatcher($dispatcher);
      $manager->dispatch($message);
    }

    public function testDisplay() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Dispatcher $dispatcher */
      $dispatcher = $this->createMock(Dispatcher::class);
      $dispatcher
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->isInstanceOf(Display\Translated::class));
      $manager = new Manager();
      $manager->addDispatcher($dispatcher);
      $manager->display(\Papaya\Message::SEVERITY_INFO, 'TEST');
    }

    public function testDisplayInfo() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Dispatcher $dispatcher */
      $dispatcher = $this->createMock(Dispatcher::class);
      $dispatcher
        ->expects($this->once())
        ->method('dispatch')
        ->with(
          $this->callback(
            function (Display\Translated $message) {
              return
                \Papaya\Message::SEVERITY_INFO === $message->getSeverity() &&
                'TEST' === $message->getMessage();
            }
          )
        );
      $manager = new Manager();
      $manager->addDispatcher($dispatcher);
      $manager->displayInfo('TEST');
    }

    public function testDisplayWarning() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Dispatcher $dispatcher */
      $dispatcher = $this->createMock(Dispatcher::class);
      $dispatcher
        ->expects($this->once())
        ->method('dispatch')
        ->with(
          $this->callback(
            function (Display\Translated $message) {
              return
                \Papaya\Message::SEVERITY_WARNING === $message->getSeverity() &&
                'TEST' === $message->getMessage();
            }
          )
        );
      $manager = new Manager();
      $manager->addDispatcher($dispatcher);
      $manager->displayWarning('TEST');
    }

    public function testDisplayError() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Dispatcher $dispatcher */
      $dispatcher = $this->createMock(Dispatcher::class);
      $dispatcher
        ->expects($this->once())
        ->method('dispatch')
        ->with(
          $this->callback(
            function (Display\Translated $message) {
              return
                \Papaya\Message::SEVERITY_ERROR === $message->getSeverity() &&
                'TEST' === $message->getMessage();
            }
          )
        );
      $manager = new Manager();
      $manager->addDispatcher($dispatcher);
      $manager->displayError('TEST');
    }

    public function testLog() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Dispatcher $dispatcher */
      $dispatcher = $this->createMock(Dispatcher::class);
      $dispatcher
        ->expects($this->once())
        ->method('dispatch')
        ->with(
          new Log(
            \Papaya\Message::SEVERITY_INFO,
            Logable::GROUP_COMMUNITY,
            'TEST'
          )
        );
      $manager = new Manager();
      $manager->addDispatcher($dispatcher);
      $manager->log(\Papaya\Message::SEVERITY_INFO, Logable::GROUP_COMMUNITY, 'TEST');
    }

    public function testLogWithContext() {
      $message = new Log(
        \Papaya\Message::SEVERITY_INFO, Logable::GROUP_COMMUNITY, 'TEST'
      );
      /** @var \PHPUnit_Framework_MockObject_MockObject|Context\Data $context */
      $context = $this->createMock(Context\Data::class);
      $message->context()->append($context);
      /** @var \PHPUnit_Framework_MockObject_MockObject|Dispatcher $dispatcher */
      $dispatcher = $this->createMock(Dispatcher::class);
      $dispatcher
        ->expects($this->once())
        ->method('dispatch')
        ->with($message);
      $manager = new Manager();
      $manager->addDispatcher($dispatcher);
      $manager->log(
        \Papaya\Message::SEVERITY_INFO,
        Logable::GROUP_COMMUNITY,
        'TEST',
        $context
      );
    }

    public function testLogWithContextGroup() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Context\Group $context */
      $context = $this->createMock(Context\Group::class);
      $message = new Log(
        \Papaya\Message::SEVERITY_INFO, Logable::GROUP_COMMUNITY, 'TEST'
      );
      $message->setContext($context);
      /** @var \PHPUnit_Framework_MockObject_MockObject|Dispatcher $dispatcher */
      $dispatcher = $this->createMock(Dispatcher::class);
      $dispatcher
        ->expects($this->once())
        ->method('dispatch')
        ->with($message);
      $manager = new Manager();
      $manager->addDispatcher($dispatcher);
      $manager->log(
        \Papaya\Message::SEVERITY_INFO,
        Logable::GROUP_COMMUNITY,
        'TEST',
        $context
      );
    }

    public function testLogWithData() {
      $message = new Log(
        \Papaya\Message::SEVERITY_INFO, Logable::GROUP_COMMUNITY, 'TEST'
      );
      $message->context()->append(new Context\Variable('data'));
      /** @var \PHPUnit_Framework_MockObject_MockObject|Dispatcher $dispatcher */
      $dispatcher = $this->createMock(Dispatcher::class);
      $dispatcher
        ->expects($this->once())
        ->method('dispatch')
        ->with($message);
      $manager = new Manager();
      $manager->addDispatcher($dispatcher);
      $manager->log(
        \Papaya\Message::SEVERITY_INFO,
        Logable::GROUP_COMMUNITY,
        'TEST',
        'data'
      );
    }

    public function testEncapsulate() {
      $manager = new Manager();
      $manager->papaya($papaya = $this->mockPapaya()->application());
      $sandbox = $manager->encapsulate('substr');
      $this->assertInternalType('callable', $sandbox);
      /** @noinspection PhpUndefinedMethodInspection */
      $this->assertSame($papaya, $sandbox[0]->papaya());
    }

    public function testHooksSettingHooks() {
      $hookOne = $this->createMock(Hook::class);
      $hookTwo = $this->createMock(Hook::class);
      $manager = new Manager();
      $manager->hooks(
        [$hookOne, $hookTwo]
      );
      $this->assertAttributeSame(
        [$hookOne, $hookTwo],
        '_hooks',
        $manager
      );
    }

    public function testHooksReadHooks() {
      $hookOne = $this->createMock(Hook::class);
      $manager = new Manager();
      $manager->hooks([$hookOne]);
      $this->assertSame(
        [$hookOne],
        $manager->hooks()
      );
    }

    public function testHooksReadHooksImplizitCreate() {
      $manager = new Manager();
      $this->assertCount(2, $manager->hooks());
    }

    public function testSetUp() {
      $errorReporting = error_reporting();
      $options = $this->mockPapaya()->options();
      $hookOne = $this->createMock(Hook::class);
      $hookOne
        ->expects($this->once())
        ->method('activate');
      $hookTwo = $this->createMock(Hook::class);
      $hookTwo
        ->expects($this->once())
        ->method('activate');

      $manager = new Manager();
      $manager->hooks([$hookOne, $hookTwo]);
      $manager->setUp($options);

      $this->assertAttributeGreaterThan(
        0, '_startTime', Context\Runtime::class
      );
      $this->assertEquals(E_ALL & ~E_STRICT, error_reporting());

      error_reporting($errorReporting);
    }

    public function testSetUpWithTemplate() {
      $errorReporting = error_reporting();

      $template = $this->createMock(Template::class);
      $options = $this->mockPapaya()->options();
      $manager = new Manager();
      $manager->setUp($options, $template);

      $this->assertSame($template, $manager->getTemplate());

      error_reporting($errorReporting);
    }

    public function testDebug() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Dispatcher $dispatcher */
      $dispatcher = $this->createMock(Dispatcher::class);
      $dispatcher
        ->expects($this->once())
        ->method('dispatch')
        ->with($this->isInstanceOf(Log::class));
      $manager = new Manager();
      $manager->addDispatcher($dispatcher);
      $manager->debug('test');
    }
  }
}
