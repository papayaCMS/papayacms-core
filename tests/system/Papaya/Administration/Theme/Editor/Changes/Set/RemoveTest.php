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

use Papaya\Administration\Theme\Editor\Changes\Set\Remove;
use Papaya\Database\Interfaces\Record;

require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaAdministrationThemeEditorChangesSetRemoveTest extends \PapayaTestCase {

  /**
   * @covers Remove::createDialog
   */
  public function testCreateDialogWithoutSetId() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Record $record */
    $record = $this->createMock(Record::class);
    $command = new Remove($record);
    $command->papaya($this->mockPapaya()->application());

    $dialog = $command->dialog();
    $dialog->options()->useToken = FALSE;
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<dialog-box action="http://www.test.tld/test.html" method="post">
        <title caption="Delete theme set"/>
        <options>
          <option name="USE_CONFIRMATION" value="yes"/>
          <option name="USE_TOKEN" value="no"/>
          <option name="PROTECT_CHANGES" value="yes"/>
          <option name="CAPTION_STYLE" value="1"/>
          <option name="DIALOG_WIDTH" value="m"/>
          <option name="TOP_BUTTONS" value="no"/>
          <option name="BOTTOM_BUTTONS" value="yes"/>
        </options>
        <input type="hidden" name="confirmation" value="true"/>
        <field class="DialogFieldMessage" error="no">
          <message>Theme set not found.</message>
        </field>
      </dialog-box>',
      $dialog->getXML()
    );
  }

  /**
   * @covers Remove::createDialog
   */
  public function testCreateDialogWithSetIdLoadsRecord() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Record $record */
    $record = $this->createMock(Record::class);
    $record
      ->expects($this->once())
      ->method('load')
      ->with(42)
      ->will($this->returnValue(TRUE));
    $command = new Remove($record);
    $command->papaya($this->mockPapaya()->application());
    $command->parameters(new \Papaya\Request\Parameters(array('set_id' => 42)));

    $dialog = $command->dialog();
    $dialog->options()->useToken = FALSE;
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<dialog-box action="http://www.test.tld/test.html" method="post">
        <title caption="Delete theme set"/>
        <options>
          <option name="USE_CONFIRMATION" value="yes"/>
          <option name="USE_TOKEN" value="no"/>
          <option name="PROTECT_CHANGES" value="yes"/>
          <option name="CAPTION_STYLE" value="1"/>
          <option name="DIALOG_WIDTH" value="m"/>
          <option name="TOP_BUTTONS" value="no"/>
          <option name="BOTTOM_BUTTONS" value="yes"/>
        </options>
        <input type="hidden" name="cmd" value="set_delete"/>
        <input type="hidden" name="theme"/>
        <input type="hidden" name="set_id" value="42"/>
        <input type="hidden" name="confirmation" value="e243360ba3bba3aeae4579dbede9fdda"/>
        <field class="DialogFieldInformation" error="no">
          <message>Delete theme set</message>
        </field>
        <button type="submit" align="right">Delete</button>
      </dialog-box>',
      $dialog->getXML()
    );
  }

  /**
   * @covers Remove::callbackDeleted
   */
  public function testCallbackDeleted() {
    $messages = $this->createMock(\Papaya\Message\Manager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(\Papaya\Message\Display::class));
    /** @var PHPUnit_Framework_MockObject_MockObject|Record $record */
    $record = $this->createMock(Record::class);
    $command = new Remove($record);
    $command->papaya(
      $this->mockPapaya()->application(
        array('messages' => $messages)
      )
    );
    $command->callbackDeleted();
  }
}
