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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaMessageDispatcherXhtmlTest extends PapayaTestCase {

  /**
  * @covers \PapayaMessageDispatcherXhtml::dispatch
  */
  public function testDispatchWithInvalidMessageExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessage $message */
    $message = $this->createMock(PapayaMessage::class);
    $dispatcher = new \PapayaMessageDispatcherXhtml();
    $this->assertFalse(
      $dispatcher->dispatch($message)
    );
  }

  /**
  * @covers \PapayaMessageDispatcherXhtml::dispatch
  */
  public function testDispatch() {
    $context = $this->createMock(PapayaMessageContextInterfaceXhtml::class);
    $context
      ->expects($this->any())
      ->method('asXhtml')
      ->will($this->returnValue('CONTEXT'));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageLogable $message */
    $message = $this->createMock(PapayaMessageLogable::class);
    $message
      ->expects($this->any())
      ->method('getType')
      ->will($this->returnValue(PapayaMessage::SEVERITY_WARNING));
    $message
      ->expects($this->any())
      ->method('getMessage')
      ->will($this->returnValue('Test Message'));
    $message
      ->expects($this->any())
      ->method('context')
      ->will($this->returnValue($context));
    $dispatcher = new \PapayaMessageDispatcherXhtml();
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
  * @covers \PapayaMessageDispatcherXhtml::allow
  */
  public function testAllowWithDisabledDispatcherExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\PapayaMessageLogable $message */
    $message = $this->createMock(PapayaMessageLogable::class);
    $dispatcher = new \PapayaMessageDispatcherXhtml();
    $dispatcher->papaya(
      $this->getFixtureApplicationObject(FALSE, FALSE)
    );
    $this->assertFalse(
      $dispatcher->dispatch($message)
    );
  }

  /**
  * @covers \PapayaMessageDispatcherXhtml::outputClosers
  */
  public function testOutputClosers() {
    $dispatcher = new \PapayaMessageDispatcherXhtml();
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
  * @covers \PapayaMessageDispatcherXhtml::outputClosers
  */
  public function testOutputClosersWithOptionDisabled() {
    $dispatcher = new \PapayaMessageDispatcherXhtml();
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
  * @covers \PapayaMessageDispatcherXhtml::getHeaderOptionsFromType
  */
  public function testGetHeaderOptionsFromType() {
    $dispatcher = new \PapayaMessageDispatcherXhtml();
    $this->assertContains(
      'Warning',
      $dispatcher->getHeaderOptionsFromType(PapayaMessage::SEVERITY_WARNING)
    );
  }

  /**
  * @covers \PapayaMessageDispatcherXhtml::getHeaderOptionsFromType
  */
  public function testGetHeaderOptionsFromTypeWithInvalidTypeExpectingErrorOptions() {
    $dispatcher = new \PapayaMessageDispatcherXhtml();
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
