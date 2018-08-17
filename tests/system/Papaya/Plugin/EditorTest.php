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

namespace Papaya\Plugin {

  require_once __DIR__.'/../../../bootstrap.php';

  class EditorTest extends \PapayaTestCase {

    /**
     * @covers \Papaya\Plugin\Editor::__construct
     * @covers \Papaya\Plugin\Editor::getContent
     */
    public function testConstructorAndGetContent() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Editable\Content $content */
      $content = $this->createMock(Editable\Content::class);
      $editor = new PapayaPluginEditor_TestProxy($content);
      $this->assertSame($content, $editor->getData());
    }

    /**
     * @covers \Papaya\Plugin\Editor::context
     */
    public function testContextGetAfterSet() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Editable\Content $content */
      $content = $this->createMock(Editable\Content::class);
      $editor = new PapayaPluginEditor_TestProxy($content);
      $editor->context($context = $this->createMock(\Papaya\Request\Parameters::class));
      $this->assertSame($context, $editor->context());
    }

    /**
     * @covers \Papaya\Plugin\Editor::context
     */
    public function testContextGetImplicitCreate() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Editable\Content $content */
      $content = $this->createMock(Editable\Content::class);
      $editor = new PapayaPluginEditor_TestProxy($content);
      $this->assertInstanceOf(\Papaya\Request\Parameters::class, $editor->context());
    }

  }

  class PapayaPluginEditor_TestProxy extends Editor {

    public function appendTo(\Papaya\XML\Element $parent) {

    }
  }
}
