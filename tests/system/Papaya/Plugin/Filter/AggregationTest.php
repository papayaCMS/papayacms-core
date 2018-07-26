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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaPluginFilterAggregationTest extends PapayaTestCase {

  public function testContentGetAfterSet() {
    $plugin = new \PapayaPluginFilterAggregation_TestProxy(
      $page = $this->createMock(PapayaUiContentPage::class)
    );
    $plugin->filters($content = $this->createMock(PapayaPluginFilterContent::class));
    $this->assertSame($content, $plugin->filters());
  }

  public function testContentGetWithImplicitCreate() {
    $plugin = new \PapayaPluginFilterAggregation_TestProxy(
      $page = $this->createMock(PapayaUiContentPage::class)
    );
    $content = $plugin->filters();
    $this->assertInstanceOf(PapayaPluginFilterContent::class, $content);
    $this->assertSame($content, $plugin->filters());
  }

}

class PapayaPluginFilterAggregation_TestProxy {

  use PapayaPluginFilterAggregation;

  public function __construct($page) {
    $this->_page = $page;
  }

}

