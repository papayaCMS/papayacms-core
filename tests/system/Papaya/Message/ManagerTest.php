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

    /**
     * @var int
     */
    private $_errorReporting;

    public function setUp(): void {
      $this->_errorReporting = error_reporting();
    }

    public function tearDown(): void {
      error_reporting($this->_errorReporting);
    }

    public function testAddDispatcher() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Dispatcher $dispatcher */
      $dispatcher = $this->createMock(Dispatcher::class);
      $manager = new Manager();
      $manager->addDispatcher($dispatcher);
      $this->assertSame(
        [$dispatcher],
        iterator_to_array($manager)
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
            Logable::GROUP_COMMUNITY,
            \Papaya\Message::SEVERITY_INFO,
            'TEST'
          )
        );
      $manager = new Manager();
      $manager->addDispatcher($dispatcher);
      $manager->log(\Papaya\Message::SEVERITY_INFO, Logable::GROUP_COMMUNITY, 'TEST');
    }

    public function testLogWithContext() {
      $message = new Log(
        Logable::GROUP_COMMUNITY,\Papaya\Message::SEVERITY_INFO,  'TEST'
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
         Logable::GROUP_COMMUNITY,\Papaya\Message::SEVERITY_INFO, 'TEST'
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
        Logable::GROUP_COMMUNITY, \Papaya\Message::SEVERITY_INFO, 'TEST'
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
      $this->assertIsCallable($sandbox);
      /** @noinspection PhpUndefinedMethodInspection */
      $this->assertSame($papaya, $sandbox[0]->papaya());
    }

    public function testHooksSettingMultipleHooks() {
      $hookOne = $this->createMock(Hook::class);
      $hookTwo = $this->createMock(Hook::class);
      $manager = new Manager();
      $manager->hooks(
        [$hookOne, $hookTwo]
      );
      $this->assertSame(
        [$hookOne, $hookTwo],
        $manager->hooks()
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

    public function testHooksReadHooksImplicitCreate() {
      $manager = new Manager();
      $this->assertCount(2, $manager->hooks());
    }

    public function testSetUp() {
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

      $this->assertGreaterThan(
        0, Context\Runtime::getStartTime()
      );
      $this->assertEquals(E_ALL & ~E_STRICT, error_reporting());
    }

    public function testSetUpWithTemplate() {
      $template = $this->createMock(Template::class);
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
      $manager->setUp($options, $template);
      $this->assertSame($template, $manager->getTemplate());
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
