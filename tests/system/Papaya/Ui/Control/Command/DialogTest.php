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

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiControlCommandDialogTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Ui\Control\Command\Dialog::appendTo
  */
  public function testAppendTo() {
    $dialog = $this->createMock(\PapayaUiDialog::class);
    $dialog
      ->expects($this->once())
      ->method('execute')
      ->will($this->returnValue(NULL));
    $dialog
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    $command = new \Papaya\Ui\Control\Command\Dialog();
    $command->dialog($dialog);
    $command->getXml();
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Dialog::appendTo
  */
  public function testAppendToExecuteSuccessful() {
    $dialog = $this->createMock(\PapayaUiDialog::class);
    $dialog
      ->expects($this->once())
      ->method('execute')
      ->will($this->returnValue(TRUE));
    $dialog
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    $callbacks = $this
      ->getMockBuilder(\Papaya\Ui\Control\Command\Dialog\Callbacks::class)
      ->disableOriginalConstructor()
      ->setMethods(array('onExecuteSuccessful'))
      ->getMock();
    $callbacks
      ->expects($this->once())
      ->method('onExecuteSuccessful')
      ->with($dialog);
    $command = new \Papaya\Ui\Control\Command\Dialog();
    $command->dialog($dialog);
    $command->callbacks($callbacks);
    $command->getXml();
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Dialog::appendTo
  */
  public function testAppendToExecuteSuccessfulWhileHideAfterSuccessIsTrue() {
    $dialog = $this->createMock(\PapayaUiDialog::class);
    $dialog
      ->expects($this->once())
      ->method('execute')
      ->will($this->returnValue(TRUE));
    $dialog
      ->expects($this->never())
      ->method('appendTo');
    $callbacks = $this
      ->getMockBuilder(\Papaya\Ui\Control\Command\Dialog\Callbacks::class)
      ->disableOriginalConstructor()
      ->setMethods(array('onExecuteSuccessful'))
      ->getMock();
    $callbacks
      ->expects($this->once())
      ->method('onExecuteSuccessful')
      ->with($dialog);
    $command = new \Papaya\Ui\Control\Command\Dialog();
    $command->dialog($dialog);
    $command->hideAfterSuccess(TRUE);
    $command->callbacks($callbacks);
    $command->getXml();
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Dialog::appendTo
  * @covers \Papaya\Ui\Control\Command\Dialog::reset
  */
  public function testAppendToExecuteSuccessfulWhileResetAfterSuccessIsTrue() {
    $dialogOne = $this->createMock(\PapayaUiDialog::class);
    $dialogOne
      ->expects($this->once())
      ->method('execute')
      ->will($this->returnValue(TRUE));
    $callbacks = $this
      ->getMockBuilder(\Papaya\Ui\Control\Command\Dialog\Callbacks::class)
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
      ->with($this->isInstanceOf(\PapayaUiDialog::class));
    $command = new \Papaya\Ui\Control\Command\Dialog();
    $command->papaya($this->mockPapaya()->application());
    $command->dialog($dialogOne);
    $command->resetAfterSuccess(TRUE);
    $command->callbacks($callbacks);
    $command->getXml();
  }


  /**
  * @covers \Papaya\Ui\Control\Command\Dialog::appendTo
  */
  public function testAppendToExecuteFailed() {
    $dialog = $this->createMock(\PapayaUiDialog::class);
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
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    $callbacks = $this
      ->getMockBuilder(\Papaya\Ui\Control\Command\Dialog\Callbacks::class)
      ->disableOriginalConstructor()
      ->setMethods(array('onExecuteFailed'))
      ->getMock();
    $callbacks
      ->expects($this->once())
      ->method('onExecuteFailed')
      ->with($dialog);
    $command = new \Papaya\Ui\Control\Command\Dialog();
    $command->dialog($dialog);
    $command->callbacks($callbacks);
    $command->getXml();
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Dialog::dialog
  */
  public function testDialogGetAfterSet() {
    $dialog = $this->createMock(\PapayaUiDialog::class);
    $command = new \Papaya\Ui\Control\Command\Dialog();
    $this->assertSame($dialog, $command->dialog($dialog));
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Dialog::dialog
  * @covers \Papaya\Ui\Control\Command\Dialog::createDialog
  */
  public function testDialogGetImplicitCreate() {
    $command = new \Papaya\Ui\Control\Command\Dialog();
    $this->assertInstanceOf(\PapayaUiDialog::class, $command->dialog());
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Dialog::dialog
  * @covers \Papaya\Ui\Control\Command\Dialog::createDialog
  */
  public function testDialogGetImplicitCreateMergingContext() {
    $context = new \Papaya\Request\Parameters(array('foo' => 'bar'));
    $command = new \Papaya\Ui\Control\Command\Dialog();
    $command->context($context);
    $dialog = $command->dialog();
    $this->assertEquals(
      array('foo' => 'bar'),
      iterator_to_array($dialog->hiddenValues())
    );
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Dialog::callbacks
  */
  public function testCallbacksGetAfterSet() {
    $callbacks = $this->createMock(\Papaya\Ui\Control\Command\Dialog\Callbacks::class);
    $command = new \Papaya\Ui\Control\Command\Dialog();
    $this->assertSame($callbacks, $command->callbacks($callbacks));
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Dialog::callbacks
  */
  public function testCallbacksGetImplicitCreate() {
    $command = new \Papaya\Ui\Control\Command\Dialog();
    $this->assertInstanceOf(\Papaya\Ui\Control\Command\Dialog\Callbacks::class, $command->callbacks());
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Dialog::hideAfterSuccess
  */
  public function testHideAfterSelectSetToTrue() {
    $command = new \Papaya\Ui\Control\Command\Dialog();
    $command->hideAfterSuccess(TRUE);
    $this->assertTrue($command->hideAfterSuccess());
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Dialog::hideAfterSuccess
  */
  public function testHideAfterSelectSetToFalse() {
    $command = new \Papaya\Ui\Control\Command\Dialog();
    $command->hideAfterSuccess(FALSE);
    $this->assertFalse($command->hideAfterSuccess());
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Dialog::context
  */
  public function testContextGetAfterSet() {
    $command = new \Papaya\Ui\Control\Command\Dialog();
    $command->context($context = $this->createMock(\Papaya\Request\Parameters::class));
    $this->assertSame($context, $command->context());
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Dialog::context
  */
  public function testContextGetWithoutSetExpectingNull() {
    $command = new \Papaya\Ui\Control\Command\Dialog();
    $this->assertNull($command->context());
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Dialog::resetAfterSuccess
  */
  public function testResetAfterSelectSetToTrue() {
    $command = new \Papaya\Ui\Control\Command\Dialog();
    $command->resetAfterSuccess(TRUE);
    $this->assertTrue($command->resetAfterSuccess());
  }

  /**
  * @covers \Papaya\Ui\Control\Command\Dialog::resetAfterSuccess
  */
  public function testResetAfterSelectSetToFalse() {
    $command = new \Papaya\Ui\Control\Command\Dialog();
    $command->resetAfterSuccess(FALSE);
    $this->assertFalse($command->resetAfterSuccess());
  }

}
