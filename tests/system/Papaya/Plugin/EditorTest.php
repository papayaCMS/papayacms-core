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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaPluginEditorTest extends PapayaTestCase {

  /**
   * @covers PapayaPluginEditor::__construct
   * @covers PapayaPluginEditor::getContent
   */
  public function testConstructorAndGetContent() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaPluginEditableContent $content */
    $content = $this->createMock(PapayaPluginEditableContent::class);
    $editor = new PapayaPluginEditor_TestProxy($content);
    $this->assertSame($content, $editor->getContent());
  }

  /**
   * @covers PapayaPluginEditor::context
   */
  public function testContextGetAfterSet() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaPluginEditableContent $content */
    $content = $this->createMock(PapayaPluginEditableContent::class);
    $editor = new PapayaPluginEditor_TestProxy($content);
    $editor->context($context = $this->createMock(PapayaRequestParameters::class));
    $this->assertSame($context, $editor->context());
  }

  /**
   * @covers PapayaPluginEditor::context
   */
  public function testContextGetImplicitCreate() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaPluginEditableContent $content */
    $content = $this->createMock(PapayaPluginEditableContent::class);
    $editor = new PapayaPluginEditor_TestProxy($content);
    $this->assertInstanceOf(PapayaRequestParameters::class, $editor->context());
  }

}

class PapayaPluginEditor_TestProxy extends PapayaPluginEditor {

  public function appendTo(PapayaXmlElement $parent) {

  }
}
