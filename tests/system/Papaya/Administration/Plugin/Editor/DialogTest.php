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

use Papaya\Administration\Plugin\Editor\Dialog;

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaAdministrationPluginEditorDialogTest extends PapayaTestCase {

  /**
   * @covers Dialog::appendTo
   */
  public function testAppendToWithoutSubmit() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaPluginEditableContent $pluginContent */
    $pluginContent = $this->createMock(PapayaPluginEditableContent::class);
    $pluginContent
      ->expects($this->never())
      ->method('assign');

    $dialog = $this->createMock(PapayaUiDialog::class);
    $dialog
      ->expects($this->any())
      ->method('execute')
      ->will($this->returnValue(FALSE));
    $dialog
      ->expects($this->any())
      ->method('isSubmitted')
      ->will($this->returnValue(FALSE));
    $dialog
      ->expects($this->once())
      ->method('appendTo');

    $editor = new Dialog($pluginContent);
    $editor->dialog($dialog);
    $editor->getXml();
  }

  /**
   * @covers Dialog::appendTo
   */
  public function testAppendToWhileExecuteWasSuccessful() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaPluginEditableContent $pluginContent */
    $pluginContent = $this->createMock(PapayaPluginEditableContent::class);
    $pluginContent
      ->expects($this->once())
      ->method('assign');

    $dialog = $this->createMock(PapayaUiDialog::class);
    $dialog
      ->expects($this->any())
      ->method('execute')
      ->will($this->returnValue(TRUE));
    $dialog
      ->expects($this->once())
      ->method('appendTo');

    $editor = new Dialog($pluginContent);
    $editor->dialog($dialog);
    $editor->getXml();
  }

  /**
   * @covers Dialog::appendTo
   */
  public function testAppendToWhileExecuteWasSuccessfulAndTriggeredCallback() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaPluginEditableContent $pluginContent */
    $pluginContent = $this->createMock(PapayaPluginEditableContent::class);
    $pluginContent
      ->expects($this->never())
      ->method('assign');

    $dialog = $this->createMock(PapayaUiDialog::class);
    $dialog
      ->expects($this->any())
      ->method('execute')
      ->will($this->returnValue(TRUE));
    $dialog
      ->expects($this->once())
      ->method('appendTo');

    $called = FALSE;
    $editor = new Dialog($pluginContent);
    $editor->onExecute(
      function() use (&$called) {
        $called = TRUE;
      }
    );
    $editor->dialog($dialog);
    $editor->getXml();
    $this->assertTrue($called);
  }

  /**
   * @covers Dialog::AppendTo
   */
  public function testAppendToWhileExecuteFailed() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaPluginEditableContent $pluginContent */
    $pluginContent = $this->createMock(PapayaPluginEditableContent::class);
    $pluginContent
      ->expects($this->never())
      ->method('assign');

    $dialogErrors = $this->createMock(PapayaUiDialogErrors::class);
    $dialogErrors
      ->expects($this->once())
      ->method('getSourceCaptions')
      ->will($this->returnValue(array()));

    $dialog = $this->createMock(PapayaUiDialog::class);
    $dialog
      ->expects($this->any())
      ->method('execute')
      ->will($this->returnValue(FALSE));
    $dialog
      ->expects($this->any())
      ->method('isSubmitted')
      ->will($this->returnValue(TRUE));
    $dialog
      ->expects($this->once())
      ->method('appendTo');
    $dialog
      ->expects($this->once())
      ->method('errors')
      ->will($this->returnValue($dialogErrors));

    $messages = $this->createMock(PapayaMessageManager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(PapayaMessageDisplay::class));

    $editor = new Dialog($pluginContent);
    $editor->papaya(
      $this->mockPapaya()->application(
        array('messages' => $messages)
      )
    );
    $editor->dialog($dialog);
    $editor->getXml();
  }

  /**
   * @covers Dialog::dialog
   */
  public function testDialogGetAfterSet() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaPluginEditableContent $pluginContent */
    $pluginContent = $this->createMock(PapayaPluginEditableContent::class);
    $editor = new Dialog($pluginContent);
    $editor->dialog($dialog = $this->createMock(PapayaUiDialog::class));
    $this->assertSame($dialog, $editor->dialog());
  }

  /**
   * @covers Dialog::dialog
   * @covers Dialog::createDialog
   */
  public function testDialogGetImplicitCreate() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaPluginEditableContent $pluginContent */
    $pluginContent = $this->createMock(PapayaPluginEditableContent::class);
    $pluginContent
      ->expects($this->any())
      ->method('getIterator')
      ->will($this->returnValue(new EmptyIterator()));

    $editor = new Dialog($pluginContent);
    $editor->papaya($this->mockPapaya()->application());

    $this->assertInstanceOf(PapayaUiDialog::class, $dialog = $editor->dialog());
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<dialog-box action="http://www.test.tld/test.html" method="post">
         <title caption="Edit content"/>
         <options>
           <option name="USE_CONFIRMATION" value="yes"/>
           <option name="USE_TOKEN" value="yes"/>
           <option name="PROTECT_CHANGES" value="yes"/>
           <option name="CAPTION_STYLE" value="1"/>
           <option name="DIALOG_WIDTH" value="m"/>
           <option name="TOP_BUTTONS" value="yes"/>
           <option name="BOTTOM_BUTTONS" value="yes"/>
         </options>
         <input type="hidden" name="content[confirmation]" value="true"/>
         <input type="hidden" name="content[token]"/>
         <button type="submit" align="right">Save</button>
       </dialog-box>',
      $dialog->getXml()
    );
  }
}
