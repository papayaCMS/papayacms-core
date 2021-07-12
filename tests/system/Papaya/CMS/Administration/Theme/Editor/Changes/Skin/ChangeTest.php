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

namespace Papaya\CMS\Administration\Theme\Editor\Changes\Skin;

require_once __DIR__.'/../../../../../../../../bootstrap.php';

class ChangeTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\CMS\Administration\Theme\Editor\Changes\Skin\Change::createDialog
   */
  public function testCreateDialogWithoutSkinId() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Record $record */
    $record = $this->createMock(\Papaya\Database\Interfaces\Record::class);
    $command = new Change($record);
    $command->papaya($this->mockPapaya()->application());

    $dialog = $command->dialog();
    $dialog->options()->useToken = FALSE;
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<dialog-box action="http://www.test.tld/test.html" method="post">
        <title caption="Add theme skin"/>
        <options>
          <option name="USE_CONFIRMATION" value="yes"/>
          <option name="USE_TOKEN" value="no"/>
          <option name="PROTECT_CHANGES" value="yes"/>
          <option name="CAPTION_STYLE" value="1"/>
          <option name="DIALOG_WIDTH" value="m"/>
          <option name="TOP_BUTTONS" value="no"/>
          <option name="BOTTOM_BUTTONS" value="yes"/>
        </options>
        <input type="hidden" name="cmd" value="skin_edit"/>
        <input type="hidden" name="theme"/>
        <input type="hidden" name="skin_id" value="0"/>
        <input type="hidden" name="confirmation" value="7904e7ffd913c06f24a54db60013d4d9"/>
        <field caption="Title" class="DialogFieldInput" error="no" mandatory="yes">
          <input type="text" name="title" maxlength="200"/>
        </field>
        <button type="submit" align="right">Add</button>
      </dialog-box>',
      $dialog->getXML()
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Theme\Editor\Changes\Skin\Change::createDialog
   */
  public function testCreateDialogWithSkinIdLoadsRecord() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Record $record */
    $record = $this->createMock(\Papaya\Database\Interfaces\Record::class);
    $record
      ->expects($this->once())
      ->method('load')
      ->with(42)
      ->will($this->returnValue(TRUE));
    $command = new Change($record);
    $command->papaya($this->mockPapaya()->application());
    $command->parameters(new \Papaya\Request\Parameters(array('skin_id' => 42)));

    $dialog = $command->dialog();
    $dialog->options()->useToken = FALSE;
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<dialog-box action="http://www.test.tld/test.html" method="post">
        <title caption="Change theme skin"/>
        <options>
          <option name="USE_CONFIRMATION" value="yes"/>
          <option name="USE_TOKEN" value="no"/>
          <option name="PROTECT_CHANGES" value="yes"/>
          <option name="CAPTION_STYLE" value="1"/>
          <option name="DIALOG_WIDTH" value="m"/>
          <option name="TOP_BUTTONS" value="no"/>
          <option name="BOTTOM_BUTTONS" value="yes"/>
        </options>
        <input type="hidden" name="cmd" value="skin_edit"/>
        <input type="hidden" name="theme"/>
        <input type="hidden" name="skin_id" value="42"/>
        <input type="hidden" name="confirmation" value="988a4f33f39918a11723b1729f1e8c21"/>
        <field caption="Title" class="DialogFieldInput" error="no" mandatory="yes">
          <input type="text" name="title" maxlength="200"/>
        </field>
        <button type="submit" align="right">Save</button>
      </dialog-box>',
      $dialog->getXML()
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Theme\Editor\Changes\Skin\Change::createDialog
   */
  public function testCreateDialogWithSkinIdLoadRecordFailed() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Interfaces\Record $record */
    $record = $this->createMock(\Papaya\Database\Interfaces\Record::class);
    $record
      ->expects($this->once())
      ->method('load')
      ->with(42)
      ->will($this->returnValue(FALSE));
    $command = new Change($record);
    $command->papaya($this->mockPapaya()->application());
    $command->parameters(new \Papaya\Request\Parameters(array('skin_id' => 42)));

    $dialog = $command->dialog();
    $dialog->options()->useToken = FALSE;
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<dialog-box action="http://www.test.tld/test.html" method="post">
        <title caption="Add theme skin"/>
        <options>
          <option name="USE_CONFIRMATION" value="yes"/>
          <option name="USE_TOKEN" value="no"/>
          <option name="PROTECT_CHANGES" value="yes"/>
          <option name="CAPTION_STYLE" value="1"/>
          <option name="DIALOG_WIDTH" value="m"/>
          <option name="TOP_BUTTONS" value="no"/>
          <option name="BOTTOM_BUTTONS" value="yes"/>
        </options>
        <input type="hidden" name="cmd" value="skin_edit"/>
        <input type="hidden" name="theme"/>
        <input type="hidden" name="skin_id" value="0"/>
        <input type="hidden" name="confirmation" value="7904e7ffd913c06f24a54db60013d4d9"/>
        <field caption="Title" class="DialogFieldInput" error="no" mandatory="yes">
          <input type="text" name="title" maxlength="200"/>
        </field>
        <button type="submit" align="right">Add</button>
      </dialog-box>',
      $dialog->getXML()
    );
  }
}
