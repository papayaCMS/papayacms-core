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

namespace Papaya\Plugin\Editable {

  require_once __DIR__.'/../../../../bootstrap.php';

  class AggregationTest extends \Papaya\TestCase {

    public function testContentGetAfterSet() {
      $plugin = new EditableAggregation_TestProxy();
      $plugin->content($content = $this->createMock(Content::class));
      $this->assertSame($content, $plugin->content());
    }

    public function testContentGetWithImplicitCreate() {
      $plugin = new EditableAggregation_TestProxy();
      $content = $plugin->content();
      $this->assertInstanceOf(Content::class, $content);
      $this->assertSame($content, $plugin->content());
      $this->assertInstanceOf(\Papaya\Plugin\Editor::class, $content->editor());
    }

  }

  class EditableAggregation_TestProxy implements \Papaya\Plugin\Editable {

    use Aggregation;

    public function createEditor(Content $content) {
      return new \Papaya\Administration\Plugin\Editor\Dialog($content);
    }
  }
}

