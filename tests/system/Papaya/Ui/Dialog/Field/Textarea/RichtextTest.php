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

require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiDialogFieldTextareaRichtextTest extends PapayaTestCase {

  /**
   * @covers \PapayaUiDialogFieldTextareaRichtext::__construct
   */
  public function testConstructorSettingRteMode() {
    $richtext = new \PapayaUiDialogFieldTextareaRichtext(
      'Caption', 'name', 12, NULL, NULL, \PapayaUiDialogFieldTextareaRichtext::RTE_SIMPLE
    );
    $this->assertEquals(
      \PapayaUiDialogFieldTextareaRichtext::RTE_SIMPLE, $richtext->getRteMode()
    );
  }

  /**
   * @covers \PapayaUiDialogFieldTextareaRichtext::appendTo
   */
  public function testAppendTo() {
    $richtext = new \PapayaUiDialogFieldTextareaRichtext('Caption', 'name');
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Caption" class="DialogFieldTextareaRichtext" error="no">
        <textarea type="text" name="name" lines="10" data-rte="standard"/>
      </field>',
      $richtext->getXml()
    );
  }


  /**
   * @covers \PapayaUiDialogFieldTextareaRichtext::appendTo
   */
  public function testAppendToWithAllParameters() {
    $richtext = new \PapayaUiDialogFieldTextareaRichtext(
      'Caption', 'name', 12, NULL, NULL, \PapayaUiDialogFieldTextareaRichtext::RTE_SIMPLE
    );
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<field caption="Caption" class="DialogFieldTextareaRichtext" error="no">
        <textarea type="text" name="name" lines="12" data-rte="simple"/>
      </field>',
      $richtext->getXml()
    );
  }

  /**
   * @covers \PapayaUiDialogFieldTextareaRichtext::setRteMode
   * @covers \PapayaUiDialogFieldTextareaRichtext::getRteMode
   */
  public function testGetRteModeAfterSetRteMode() {
    $richtext = new \PapayaUiDialogFieldTextareaRichtext('Caption', 'name');
    $richtext->setRteMode(PapayaUiDialogFieldTextareaRichtext::RTE_SIMPLE);
    $this->assertEquals(
      \PapayaUiDialogFieldTextareaRichtext::RTE_SIMPLE, $richtext->getRteMode()
    );
  }
}
