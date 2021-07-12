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

namespace Papaya\CMS\Plugin\PageModule {

  use Papaya\CMS\Plugin\PageModule;
  use Papaya\TestFramework\TestCase;
  use Papaya\CMS\Output\Page;

  /**
   * @covers \Papaya\CMS\Plugin\PageModule\Aggregation
   */
  class AggregationTest extends TestCase {

    public function testGetPage() {
      $page = $this->createMock(Page::class);
      $pageModule = new Aggregation_TestProxy($page);
      $this->assertSame($page, $pageModule->getPage());
    }
  }

  class Aggregation_TestProxy implements PageModule {

    use Aggregation;
  }
}
