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

class PapayaAdministrationPluginEditorFieldsTest extends PapayaTestCase {

  /**
   * @covers PapayaAdministrationPluginEditorFields::__construct
   */
  public function testConstructor() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaPluginEditableData $content */
    $content = $this->createMock(PapayaPluginEditableData::class);
    $editor = new PapayaAdministrationPluginEditorFields($content, array());
    $this->assertSame($content, $editor->getData());
  }

  /**
   * @covers PapayaAdministrationPluginEditorFields::dialog
   * @covers PapayaAdministrationPluginEditorFields::createDialog
   */
  public function testDialogGetImplicitCreate() {
    $languageSwitch = $this->createMock(PapayaAdministrationLanguagesSwitch::class);
    $languageSwitch
      ->expects($this->any())
      ->method('getCurrent')
      ->will(
        $this->returnValue(
          array('id' => 42, 'title' => 'Language', 'image' => 'lng.png')
        )
      );

    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaPluginEditableData $pluginContent */
    $pluginContent = $this->createMock(PapayaPluginEditableData::class);
    $pluginContent
      ->expects($this->any())
      ->method('getIterator')
      ->will($this->returnValue(new EmptyIterator()));

    $builder = $this
      ->getMockBuilder(PapayaUiDialogFieldBuilderArray::class)
      ->disableOriginalConstructor()
      ->getMock();
    $builder
      ->expects($this->once())
      ->method('getFields')
      ->will($this->returnValue(array()));

    $editor = new PapayaAdministrationPluginEditorFields($pluginContent, array());
    $editor->papaya(
      $this->mockPapaya()->application(
        array('administrationLanguage' => $languageSwitch)
      )
    );
    $editor->builder($builder);
    $editor->context(new PapayaRequestParameters(array('context' => 'sample')));

    $this->assertInstanceOf(PapayaUiDialog::class, $dialog = $editor->dialog());
  }

  /**
   * @covers PapayaAdministrationPluginEditorFields::builder
   */
  public function testBuilderGetAfterSet() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaPluginEditableContent $content */
    $content = $this->createMock(PapayaPluginEditableContent::class);
    $builder = $this
      ->getMockBuilder(PapayaUiDialogFieldBuilderArray::class)
      ->disableOriginalConstructor()
      ->getMock();
    $editor = new PapayaAdministrationPluginEditorFields($content, array());
    $editor->builder($builder);
    $this->assertSame($builder, $editor->builder());
  }

  /**
   * @covers PapayaAdministrationPluginEditorFields::builder
   */
  public function testBuilderGetImplicitCreate() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaPluginEditableContent $content */
    $content = $this->createMock(PapayaPluginEditableContent::class);
    $editor = new PapayaAdministrationPluginEditorFields($content, array());
    $this->assertInstanceOf(PapayaUiDialogFieldBuilderArray::class, $editor->builder());
  }
}
