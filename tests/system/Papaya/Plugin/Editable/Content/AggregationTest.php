<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Plugin\Editable\Content {

  use Papaya\Administration\Plugin\Editor\Dialog;
  use Papaya\Plugin\Editable as EditablePlugin;
  use Papaya\Plugin\Editable\Content;
  use Papaya\Plugin\Editor as PluginEditor;
  use Papaya\Test\TestCase;

  /**
   * @covers \Papaya\Plugin\Editable\Content\Aggregation
   */
  class AggregationTest extends TestCase {

    public function testContentGetAfterSet() {
      $plugin = new Aggregation_TestProxy();
      $plugin->content($content = $this->createMock(Content::class));
      $this->assertSame($content, $plugin->content());
    }

    public function testContentGetWithImplicitCreate() {
      $plugin = new Aggregation_TestProxy();
      $content = $plugin->content();
      $this->assertInstanceOf(Content::class, $content);
      $this->assertSame($content, $plugin->content());
      $this->assertInstanceOf(PluginEditor::class, $content->editor());
    }

  }

  class Aggregation_TestProxy implements EditablePlugin {

    use Aggregation;

    public function createEditor(Content $content) {
      return new Dialog($content);
    }
  }

}
