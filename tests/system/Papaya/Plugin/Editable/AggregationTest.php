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

use Papaya\Administration\Plugin\Editor\Dialog;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaPluginEditableAggregationTest extends PapayaTestCase {

  public function testContentGetAfterSet() {
    $plugin = new \PapayaPluginEditableAggregation_TestProxy();
    $plugin->content($content = $this->createMock(PapayaPluginEditableContent::class));
    $this->assertSame($content, $plugin->content());
  }

  public function testContentGetWithImplicitCreate() {
    $plugin = new \PapayaPluginEditableAggregation_TestProxy();
    $content = $plugin->content();
    $this->assertInstanceOf(PapayaPluginEditableContent::class, $content);
    $this->assertSame($content, $plugin->content());
    $this->assertInstanceOf(PapayaPluginEditor::class, $content->editor());
  }

}

class PapayaPluginEditableAggregation_TestProxy implements PapayaPluginEditable {

  use PapayaPluginEditableAggregation;

  public function createEditor(PapayaPluginEditableContent $content) {
    return new Dialog($content);
  }
}

