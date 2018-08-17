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

namespace Papaya\Plugin\Editable;
require_once __DIR__.'/../../../../bootstrap.php';

class ContentTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Plugin\Editable\Content::getXML
   */
  public function testGetXml() {
    $content = new Content(array('foo' => 'bar', 'bar' => 'foo'));
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<data version="2">
        <data-element name="foo">bar</data-element>
        <data-element name="bar">foo</data-element>
      </data>',
      $content->getXML()
    );
  }

  /**
   * @covers \Papaya\Plugin\Editable\Content::setXML
   */
  public function testSetXml() {
    $content = new Content();
    $content->setXML(
    /** @lang XML */
      '<data version="2">
        <data-element name="foo">bar</data-element>
        <data-element name="bar">foo</data-element>
      </data>'
    );
    $this->assertEquals(
      array('foo' => 'bar', 'bar' => 'foo'),
      (array)$content
    );
  }

  /**
   * @covers \Papaya\Plugin\Editable\Content::setXML
   */
  public function testSetXmlReplacesAllData() {
    $content = new Content(array('foo' => 'bar'));
    $content->setXML(
    /** @lang XML */
      '<data version="2">
        <data-element name="bar">foo</data-element>
      </data>'
    );
    $this->assertEquals(
      array('bar' => 'foo'),
      (array)$content
    );
  }

  /**
   * @covers \Papaya\Plugin\Editable\Content::modified
   */
  public function testModifiedIsTrueOnNewObject() {
    $content = new Content();
    $this->assertTrue($content->modified());
  }

  /**
   * @covers \Papaya\Plugin\Editable\Content::modified
   */
  public function testModifiedIsFalseAfterSetXml() {
    $content = new Content();
    $content->setXML('');
    $this->assertFalse($content->modified());
  }

  /**
   * @covers \Papaya\Plugin\Editable\Content::modified
   */
  public function testModifiedIsTrueAfterChange() {
    $content = new Content();
    $content->setXML(
    /** @lang XML */
      '<data version="2">
        <data-element name="bar">foo</data-element>
      </data>'
    );
    $content['foo'] = 'bar';
    $this->assertTrue($content->modified());
  }

  /**
   * @covers \Papaya\Plugin\Editable\Content::modified
   */
  public function testModfiedIsFalseForEqualData() {
    $content = new Content();
    $content->setXML(
    /** @lang XML */
      '<data version="2">
        <data-element name="foo">bar</data-element>
        <data-element name="bar">foo</data-element>
      </data>'
    );
    $content->clear();
    $content['bar'] = 'foo';
    $content['foo'] = 'bar';
    $this->assertFalse($content->modified());
  }

  /**
   * @covers \Papaya\Plugin\Editable\Content::editor
   */
  public function testEditorGetAfterSet() {
    $editor = $this
      ->getMockBuilder(\Papaya\Plugin\Editor::class)
      ->disableOriginalConstructor()
      ->getMock();
    $content = new Content();
    $content->editor($editor);
    $this->assertSame($editor, $content->editor());
  }

  /**
   * @covers \Papaya\Plugin\Editable\Content::editor
   */
  public function testEditorGetImplicitCreateWithoutCallback() {
    $content = new Content();
    $this->assertInstanceOf(\Papaya\Plugin\Editor::class, $content->editor());
  }

  /**
   * @covers \Papaya\Plugin\Editable\Content::editor
   */
  public function testEditorImplicitCreateWithInvalidCallbackExpectingException() {
    $content = new Content();
    $content->callbacks()->onCreateEditor = array($this, 'callbackOnCreateEditorReturnNull');
    $this->expectException(\LogicException::class);
    $content->editor();
  }

  public function callbackOnCreateEditorReturnNull() {
    return NULL;
  }

  /**
   * @covers \Papaya\Plugin\Editable\Content::editor
   */
  public function testEditorImplicitCreateUsingCallback() {
    $content = new Content();
    $content->callbacks()->onCreateEditor = array($this, 'callbackOnCreateEditor');
    $this->assertInstanceOf(\Papaya\Plugin\Editor::class, $content->editor());
  }

  public function callbackOnCreateEditor() {
    return $this
      ->getMockBuilder(\Papaya\Plugin\Editor::class)
      ->disableOriginalConstructor()
      ->getMock();
  }

  /**
   * @covers \Papaya\Plugin\Editable\Content::callbacks
   */
  public function testCallbacksGetAfterSet() {
    $content = new Content();
    $content->callbacks($callbacks = $this->createMock(Callbacks::class));
    $this->assertSame($callbacks, $content->callbacks());
  }

  /**
   * @covers \Papaya\Plugin\Editable\Content::callbacks
   */
  public function testCallbacksGetImplicitCreate() {
    $content = new Content();
    $this->assertInstanceOf(Callbacks::class, $content->callbacks());
  }
}
