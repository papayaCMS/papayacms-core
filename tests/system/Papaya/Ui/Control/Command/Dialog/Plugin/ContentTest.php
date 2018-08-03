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

require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaUiControlCommandDialogPluginContentTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\UI\Control\Command\Dialog\Plugin\Content
  */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Plugin\Editable\Content $content */
    $content = $this->createMock(\Papaya\Plugin\Editable\Content::class);
    $command = new \Papaya\UI\Control\Command\Dialog\Plugin\Content($content);
    $this->assertSame($content, $command->getContent());
  }

  /**
  * @covers \Papaya\UI\Control\Command\Dialog\Plugin\Content
  */
  public function testCreateDialog() {
    $content = new \Papaya\Plugin\Editable\Content(array('foo' => 'bar'));
    $command = new \Papaya\UI\Control\Command\Dialog\Plugin\Content($content);
    $this->assertEquals(
      (array)$content,
      (array)$command->dialog()->data
    );
  }

  /**
  * @covers \Papaya\UI\Control\Command\Dialog\Plugin\Content
  */
  public function testAppendTo() {
    $dialog = $this->createMock(\Papaya\UI\Dialog::class);
    $dialog
      ->expects($this->once())
      ->method('execute')
      ->will($this->returnValue(FALSE));
    $dialog
      ->expects($this->once())
      ->method('isSubmitted')
      ->will($this->returnValue(FALSE));
    $dialog
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Plugin\Editable\Content $content */
    $content = $this->createMock(\Papaya\Plugin\Editable\Content::class);
    $command = new \Papaya\UI\Control\Command\Dialog\Plugin\Content($content);
    $command->dialog($dialog);
    $command->getXml();
  }

  /**
  * @covers \Papaya\UI\Control\Command\Dialog\Plugin\Content
  */
  public function testAppendToWithSubmittedDialog() {
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
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Plugin\Editable\Content $content */
    $content = $this->createMock(\Papaya\Plugin\Editable\Content::class);
    $command = new \Papaya\UI\Control\Command\Dialog\Plugin\Content($content);
    $command->dialog($dialog);
    $command->getXml();
  }

  /**
  * @covers \Papaya\UI\Control\Command\Dialog\Plugin\Content
  */
  public function testAppendToWithExecutedDialog() {
    $dialog = $this->createMock(\Papaya\UI\Dialog::class);
    $dialog
      ->expects($this->once())
      ->method('execute')
      ->will($this->returnValue(TRUE));
    $dialog
      ->expects($this->never())
      ->method('isSubmitted');
    $dialog
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Plugin\Editable\Content $content */
    $content = $this->createMock(\Papaya\Plugin\Editable\Content::class);
    $content
      ->expects($this->once())
      ->method('assign');
    $command = new \Papaya\UI\Control\Command\Dialog\Plugin\Content($content);
    $command->dialog($dialog);
    $command->getXml();
  }

  /**
  * @covers \Papaya\UI\Control\Command\Dialog\Plugin\Content
  */
  public function testAppendToWithHideExecutedDialog() {
    $dialog = $this->createMock(\Papaya\UI\Dialog::class);
    $dialog
      ->expects($this->once())
      ->method('execute')
      ->will($this->returnValue(TRUE));
    $dialog
      ->expects($this->never())
      ->method('isSubmitted');
    $dialog
      ->expects($this->never())
      ->method('appendTo');
    $content = $this->createMock(\Papaya\Plugin\Editable\Content::class);
    $content
      ->expects($this->once())
      ->method('assign');
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Plugin\Editable\Content $content */
    $command = new \Papaya\UI\Control\Command\Dialog\Plugin\Content($content);
    $command->hideAfterSuccess(TRUE);
    $command->dialog($dialog);
    $command->getXml();
  }
}
