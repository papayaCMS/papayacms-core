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

namespace Papaya\CMS\Plugin\Filter {

  require_once __DIR__.'/../../../../../bootstrap.php';

  class AggregationTest extends \Papaya\TestFramework\TestCase {

    public function testContentGetAfterSet() {
      $plugin = new FilterAggregation_TestProxy(
        $page = $this->createMock(\Papaya\CMS\Output\Page::class)
      );
      $plugin->filters($content = $this->createMock(Content::class));
      $this->assertSame($content, $plugin->filters());
    }

    public function testContentGetWithImplicitCreate() {
      $plugin = new FilterAggregation_TestProxy(
        $page = $this->createMock(\Papaya\CMS\Output\Page::class)
      );
      $content = $plugin->filters();
      $this->assertInstanceOf(Content::class, $content);
      $this->assertSame($content, $plugin->filters());
    }

  }

  class FilterAggregation_TestProxy {

    use Aggregation;

    public function __construct($page) {
      $this->_page = $page;
    }

  }
}

