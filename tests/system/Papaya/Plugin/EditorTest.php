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

  use Papaya\Request\Parameters;
  use Papaya\TestCase;
  use Papaya\XML\Element as XMLElement;

  require_once __DIR__.'/../../../bootstrap.php';

    /**
     * @covers \Papaya\Plugin\Editor
     */
  class EditorTest extends TestCase {

    public function testConstructorAndGetContent() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Editable\Content $content */
      $content = $this->createMock(Editable\Content::class);
      $editor = new Editor_TestProxy($content);
      $this->assertSame($content, $editor->getData());
      $this->assertSame($content, $editor->getContent());
    }

    public function testContextGetAfterSet() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Editable\Content $content */
      $content = $this->createMock(Editable\Content::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject|Parameters $context */
      $context = $this->createMock(Parameters::class);
      $editor = new Editor_TestProxy($content);
      $editor->context($context);
      $this->assertSame($context, $editor->context());
    }

    public function testContextGetImplicitCreate() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Editable\Content $content */
      $content = $this->createMock(Editable\Content::class);
      $editor = new Editor_TestProxy($content);
      $this->assertInstanceOf(Parameters::class, $editor->context());
    }

  }

  class Editor_TestProxy extends Editor {

    public function appendTo(XMLElement $parent) {

    }
  }
}
