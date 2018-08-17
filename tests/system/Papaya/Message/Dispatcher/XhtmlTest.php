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

class XhtmlTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Message\Dispatcher\XHTML::dispatch
   */
  public function testDispatchWithInvalidMessageExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message $message */
    $message = $this->createMock(\Papaya\Message::class);
    $dispatcher = new XHTML();
    $this->assertFalse(
      $dispatcher->dispatch($message)
    );
  }

  /**
   * @covers \Papaya\Message\Dispatcher\XHTML::dispatch
   */
  public function testDispatch() {
    $context = $this->createMock(\Papaya\Message\Context\Interfaces\XHTML::class);
    $context
      ->expects($this->any())
      ->method('asXhtml')
      ->will($this->returnValue('CONTEXT'));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Logable $message */
    $message = $this->createMock(\Papaya\Message\Logable::class);
    $message
      ->expects($this->any())
      ->method('getType')
      ->will($this->returnValue(\Papaya\Message::SEVERITY_WARNING));
    $message
      ->expects($this->any())
      ->method('getMessage')
      ->will($this->returnValue('Test Message'));
    $message
      ->expects($this->any())
      ->method('context')
      ->will($this->returnValue($context));
    $dispatcher = new XHTML();
    $dispatcher->papaya(
      $this->getFixtureApplicationObject(TRUE, TRUE)
    );
    ob_start();
    $dispatcher->dispatch($message);
    $this->assertEquals(
      '</form></table>'.
      '<div class="debug" style="border: none; margin: 3em; padding: 0; font-size: 1em;">'.
      '<h3 style="background-color: #FFCC33; color: #000000; padding: 0.3em; margin: 0;">'.
      'Warning: Test Message</h3>CONTEXT</div>',
      ob_get_clean()
    );
  }

  /**
   * @covers \Papaya\Message\Dispatcher\XHTML::allow
   */
  public function testAllowWithDisabledDispatcherExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Message\Logable $message */
    $message = $this->createMock(\Papaya\Message\Logable::class);
    $dispatcher = new XHTML();
    $dispatcher->papaya(
      $this->getFixtureApplicationObject(FALSE, FALSE)
    );
    $this->assertFalse(
      $dispatcher->dispatch($message)
    );
  }

  /**
   * @covers \Papaya\Message\Dispatcher\XHTML::outputClosers
   */
  public function testOutputClosers() {
    $dispatcher = new XHTML();
    $dispatcher->papaya(
      $this->getFixtureApplicationObject(FALSE, TRUE)
    );
    ob_start();
    $dispatcher->outputClosers();
    $this->assertSame(
      '</form></table>',
      ob_get_clean()
    );
  }

  /**
   * @covers \Papaya\Message\Dispatcher\XHTML::outputClosers
   */
  public function testOutputClosersWithOptionDisabled() {
    $dispatcher = new XHTML();
    $dispatcher->papaya(
      $this->getFixtureApplicationObject(FALSE, FALSE)
    );
    ob_start();
    $dispatcher->outputClosers();
    $this->assertSame(
      '',
      ob_get_clean()
    );
  }

  /**
   * @covers \Papaya\Message\Dispatcher\XHTML::getHeaderOptionsFromType
   */
  public function testGetHeaderOptionsFromType() {
    $dispatcher = new XHTML();
    $this->assertContains(
      'Warning',
      $dispatcher->getHeaderOptionsFromType(\Papaya\Message::SEVERITY_WARNING)
    );
  }

  /**
   * @covers \Papaya\Message\Dispatcher\XHTML::getHeaderOptionsFromType
   */
  public function testGetHeaderOptionsFromTypeWithInvalidTypeExpectingErrorOptions() {
    $dispatcher = new XHTML();
    $this->assertContains(
      'Error',
      $dispatcher->getHeaderOptionsFromType(99999)
    );
  }

  public function getFixtureApplicationObject($active, $outputClosers) {
    return $this->mockPapaya()->application(
      array(
        'Options' => $this->mockPapaya()->options(
          array(
            'PAPAYA_PROTOCOL_XHTML' => $active,
            'PAPAYA_PROTOCOL_XHTML_OUTPUT_CLOSERS' => $outputClosers
          )
        )
      )
    );
  }
}
