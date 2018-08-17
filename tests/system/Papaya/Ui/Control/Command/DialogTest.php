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

namespace Papaya\UI\Control\Command;
require_once __DIR__.'/../../../../../bootstrap.php';

class DialogTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\UI\Control\Command\Dialog::appendTo
   */
  public function testAppendTo() {
    $dialog = $this->createMock(\Papaya\UI\Dialog::class);
    $dialog
      ->expects($this->once())
      ->method('execute')
      ->will($this->returnValue(NULL));
    $dialog
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));
    $command = new Dialog();
    $command->dialog($dialog);
    $command->getXML();
  }

  /**
   * @covers \Papaya\UI\Control\Command\Dialog::appendTo
   */
  public function testAppendToExecuteSuccessful() {
    $dialog = $this->createMock(\Papaya\UI\Dialog::class);
    $dialog
      ->expects($this->once())
      ->method('execute')
      ->will($this->returnValue(TRUE));
    $dialog
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));
    $callbacks = $this
      ->getMockBuilder(Dialog\Callbacks::class)
      ->disableOriginalConstructor()
      ->setMethods(array('onExecuteSuccessful'))
      ->getMock();
    $callbacks
      ->expects($this->once())
      ->method('onExecuteSuccessful')
      ->with($dialog);
    $command = new Dialog();
    $command->dialog($dialog);
    $command->callbacks($callbacks);
    $command->getXML();
  }

  /**
   * @covers \Papaya\UI\Control\Command\Dialog::appendTo
   */
  public function testAppendToExecuteSuccessfulWhileHideAfterSuccessIsTrue() {
    $dialog = $this->createMock(\Papaya\UI\Dialog::class);
    $dialog
      ->expects($this->once())
      ->method('execute')
      ->will($this->returnValue(TRUE));
    $dialog
      ->expects($this->never())
      ->method('appendTo');
    $callbacks = $this
      ->getMockBuilder(Dialog\Callbacks::class)
      ->disableOriginalConstructor()
      ->setMethods(array('onExecuteSuccessful'))
      ->getMock();
    $callbacks
      ->expects($this->once())
      ->method('onExecuteSuccessful')
      ->with($dialog);
    $command = new Dialog();
    $command->dialog($dialog);
    $command->hideAfterSuccess(TRUE);
    $command->callbacks($callbacks);
    $command->getXML();
  }

  /**
   * @covers \Papaya\UI\Control\Command\Dialog::appendTo
   * @covers \Papaya\UI\Control\Command\Dialog::reset
   */
  public function testAppendToExecuteSuccessfulWhileResetAfterSuccessIsTrue() {
    $dialogOne = $this->createMock(\Papaya\UI\Dialog::class);
    $dialogOne
      ->expects($this->once())
      ->method('execute')
      ->will($this->returnValue(TRUE));
    $callbacks = $this
      ->getMockBuilder(Dialog\Callbacks::class)
      ->disableOriginalConstructor()
      ->setMethods(array('onExecuteSuccessful', 'onCreateDialog'))
      ->getMock();
    $callbacks
      ->expects($this->once())
      ->method('onExecuteSuccessful')
      ->with($dialogOne);
    $callbacks
      ->expects($this->once())
      ->method('onCreateDialog')
      ->with($this->isInstanceOf(\Papaya\UI\Dialog::class));
    $command = new Dialog();
    $command->papaya($this->mockPapaya()->application());
    $command->dialog($dialogOne);
    $command->resetAfterSuccess(TRUE);
    $command->callbacks($callbacks);
    $command->getXML();
  }


  /**
   * @covers \Papaya\UI\Control\Command\Dialog::appendTo
   */
  public function testAppendToExecuteFailed() {
    $dialog = $this->createMock(\Papaya\UI\Dialog::class);
    $dialog
      ->expects($this->once())
      ->method('execute')
      ->will($this->returnValue(FALSE));
    $dialog
      ->expects($this->once())
      ->method('isSubmitted')
      ->will($this->returnValue(TRUE));
    $dialog
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));
    $callbacks = $this
      ->getMockBuilder(Dialog\Callbacks::class)
      ->disableOriginalConstructor()
      ->setMethods(array('onExecuteFailed'))
      ->getMock();
    $callbacks
      ->expects($this->once())
      ->method('onExecuteFailed')
      ->with($dialog);
    $command = new Dialog();
    $command->dialog($dialog);
    $command->callbacks($callbacks);
    $command->getXML();
  }

  /**
   * @covers \Papaya\UI\Control\Command\Dialog::dialog
   */
  public function testDialogGetAfterSet() {
    $dialog = $this->createMock(\Papaya\UI\Dialog::class);
    $command = new Dialog();
    $this->assertSame($dialog, $command->dialog($dialog));
  }

  /**
   * @covers \Papaya\UI\Control\Command\Dialog::dialog
   * @covers \Papaya\UI\Control\Command\Dialog::createDialog
   */
  public function testDialogGetImplicitCreate() {
    $command = new Dialog();
    $this->assertInstanceOf(\Papaya\UI\Dialog::class, $command->dialog());
  }

  /**
   * @covers \Papaya\UI\Control\Command\Dialog::dialog
   * @covers \Papaya\UI\Control\Command\Dialog::createDialog
   */
  public function testDialogGetImplicitCreateMergingContext() {
    $context = new \Papaya\Request\Parameters(array('foo' => 'bar'));
    $command = new Dialog();
    $command->context($context);
    $dialog = $command->dialog();
    $this->assertEquals(
      array('foo' => 'bar'),
      iterator_to_array($dialog->hiddenValues())
    );
  }

  /**
   * @covers \Papaya\UI\Control\Command\Dialog::callbacks
   */
  public function testCallbacksGetAfterSet() {
    $callbacks = $this->createMock(Dialog\Callbacks::class);
    $command = new Dialog();
    $this->assertSame($callbacks, $command->callbacks($callbacks));
  }

  /**
   * @covers \Papaya\UI\Control\Command\Dialog::callbacks
   */
  public function testCallbacksGetImplicitCreate() {
    $command = new Dialog();
    $this->assertInstanceOf(Dialog\Callbacks::class, $command->callbacks());
  }

  /**
   * @covers \Papaya\UI\Control\Command\Dialog::hideAfterSuccess
   */
  public function testHideAfterSelectSetToTrue() {
    $command = new Dialog();
    $command->hideAfterSuccess(TRUE);
    $this->assertTrue($command->hideAfterSuccess());
  }

  /**
   * @covers \Papaya\UI\Control\Command\Dialog::hideAfterSuccess
   */
  public function testHideAfterSelectSetToFalse() {
    $command = new Dialog();
    $command->hideAfterSuccess(FALSE);
    $this->assertFalse($command->hideAfterSuccess());
  }

  /**
   * @covers \Papaya\UI\Control\Command\Dialog::context
   */
  public function testContextGetAfterSet() {
    $command = new Dialog();
    $command->context($context = $this->createMock(\Papaya\Request\Parameters::class));
    $this->assertSame($context, $command->context());
  }

  /**
   * @covers \Papaya\UI\Control\Command\Dialog::context
   */
  public function testContextGetWithoutSetExpectingNull() {
    $command = new Dialog();
    $this->assertNull($command->context());
  }

  /**
   * @covers \Papaya\UI\Control\Command\Dialog::resetAfterSuccess
   */
  public function testResetAfterSelectSetToTrue() {
    $command = new Dialog();
    $command->resetAfterSuccess(TRUE);
    $this->assertTrue($command->resetAfterSuccess());
  }

  /**
   * @covers \Papaya\UI\Control\Command\Dialog::resetAfterSuccess
   */
  public function testResetAfterSelectSetToFalse() {
    $command = new Dialog();
    $command->resetAfterSuccess(FALSE);
    $this->assertFalse($command->resetAfterSuccess());
  }

}
