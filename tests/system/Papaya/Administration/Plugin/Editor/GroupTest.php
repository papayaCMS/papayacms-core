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

namespace Papaya\Administration\Plugin\Editor;

require_once __DIR__.'/../../../../../bootstrap.php';

class GroupTest extends \PapayaTestCase {

  /**
   * @covers Group
   */
  public function testAppendToWithOneEditor() {
    $context = new \Papaya\Request\Parameters();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Plugin\Editable\Content $content */
    $content = $this->createMock(\Papaya\Plugin\Editable\Content::class);

    $editorGroup = new Group($content);
    $editorGroup->papaya($this->mockPapaya()->application());

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Plugin\Editor $editor */
    $editor = $this->createMock(\Papaya\Plugin\Editor::class);
    $editor
      ->expects($this->once())
      ->method('context')
      ->willReturn($context);
    $editor
      ->expects($this->once())
      ->method('appendTo');

    $editorGroup->add($editor, 'TEST CAPTION');

    $this->assertXmlFragmentEqualsXmlFragment(
    /** @lang XML */
      '<toolbar>
          <button down="down" href="http://www.test.tld/test.html?editor_index=0" title="TEST CAPTION"/>
        </toolbar>',
      $editorGroup->getXML()
    );
  }

  /**
   * @covers Group
   */
  public function testAppendToWithOneEditorAndContextData() {
    $context = new \Papaya\Request\Parameters(array('foo' => 'bar'));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Plugin\Editable\Content $content */
    $content = $this->createMock(\Papaya\Plugin\Editable\Content::class);

    $editorGroup = new Group($content);
    $editorGroup->papaya($this->mockPapaya()->application());
    $editorGroup->context($context);

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Plugin\Editor $editor */
    $editor = $this->createMock(\Papaya\Plugin\Editor::class);
    $editor
      ->expects($this->any())
      ->method('context')
      ->willReturn($context);

    $editorGroup->add($editor, 'TEST CAPTION');

    $this->assertXmlFragmentEqualsXmlFragment(
    /** @lang XML */
      '<toolbar>
          <button down="down" href="http://www.test.tld/test.html?editor_index=0&amp;foo=bar" title="TEST CAPTION"/>
        </toolbar>',
      $editorGroup->getXML()
    );
  }

  /**
   * @covers Group
   */
  public function testAppendToWithTwoEditorsSelectingSecond() {
    $context = new \Papaya\Request\Parameters(array('dialog-index' => 1));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Plugin\Editable\Content $content */
    $content = $this->createMock(\Papaya\Plugin\Editable\Content::class);

    $editorGroup = new Group($content, 'dialog-index');
    $editorGroup->papaya(
      $this->mockPapaya()->application(
        array('request' => $this->mockPapaya()->request(array('dialog-index' => 1)))
      )
    );
    $editorGroup->parameters($context);

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Plugin\Editor $editorOne */
    $editorOne = $this->createMock(\Papaya\Plugin\Editor::class);
    $editorOne
      ->expects($this->never())
      ->method('context');
    $editorOne
      ->expects($this->never())
      ->method('appendTo');
    $editorGroup->add($editorOne, 'ONE', 'image1');

    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Plugin\Editor $editorTwo */
    $editorTwo = $this->createMock(\Papaya\Plugin\Editor::class);
    $editorTwo
      ->expects($this->once())
      ->method('context')
      ->willReturn($context);
    $editorTwo
      ->expects($this->once())
      ->method('appendTo');
    $editorGroup->add($editorTwo, 'TWO', 'image2');

    $this->assertXmlFragmentEqualsXmlFragment(
    /** @lang XML */
      '<toolbar>
        <button href="http://www.test.tld/test.html?dialog-index=0" title="ONE"/>
        <button down="down" href="http://www.test.tld/test.html?dialog-index=1" title="TWO"/>
      </toolbar>',
      $editorGroup->getXML()
    );
  }

  /**
   * @covers Group
   */
  public function testAppendToWithoutEditorExpectingException() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Plugin\Editable\Content $content */
    $content = $this->createMock(\Papaya\Plugin\Editable\Content::class);
    $editorGroup = new Group($content);
    $editorGroup->papaya($this->mockPapaya()->application());

    $this->expectException(\LogicException::class);
    $editorGroup->getXML();
  }
}
