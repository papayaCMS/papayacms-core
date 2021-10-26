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

namespace Papaya\CMS\Plugin\Options {

  use Papaya\CMS\Administration\Plugin\Editor\Dialog;
  use Papaya\Plugin;
  use Papaya\Plugin\Configurable\Options as ConfigurableOptionsPlugin;
  use Papaya\Plugin\Editor as PluginEditor;
  use Papaya\TestFramework\TestCase;

  /**
   * @covers \Papaya\Plugin\Editable\Options\Aggregation
   */
  class AggregationTest extends TestCase {

    public function testOptionsGetAfterSet() {
      $plugin = new Aggregation_TestProxy();
      $plugin->options($options = $this->createMock(Plugin\Editable\Options::class));
      $this->assertSame($options, $plugin->options());
    }

    public function testGetPluginGuid() {
      $plugin = new Aggregation_TestProxy();
      $plugin->guid = 'af123456789012345678901234567890';
      $this->assertSame('af123456789012345678901234567890', $plugin->getPluginGuid());
    }

    public function testGetPluginGuidWithoutGuidExpectingException() {
      $plugin = new Aggregation_TestProxy();
      $this->expectException(\LogicException::class);
      $this->expectExceptionMessage('No plugin guid found.');
      $plugin->getPluginGuid();
    }

  }

  class Aggregation_TestProxy implements ConfigurableOptionsPlugin {

    use Aggregation;

    public $guid;

    /**
     * @param Plugin\Editable\Options $options
     *
     * @return PluginEditor
     */
    public function createOptionsEditor(Plugin\Editable\Options $options) {
      return new Dialog($options);
    }
  }
}
