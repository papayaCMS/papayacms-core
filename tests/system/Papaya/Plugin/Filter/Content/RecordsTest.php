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

use Papaya\Content\View\Configurations;

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaPluginFilterContentRecordsTest extends \PapayaTestCase {

  /**
   * @covers \PapayaPluginFilterContentRecords
   */
  public function testRecordsGetAfterSet() {
    $filterGroup = new \PapayaPluginFilterContentRecords($this->getPageFixture());
    $filterGroup->records($records = $this->createMock(Configurations::class));
    $this->assertSame($records, $filterGroup->records());
  }

  /**
   * @covers \PapayaPluginFilterContentRecords
   */
  public function testRecordsImplicitCreate() {
    $filterGroup = new \PapayaPluginFilterContentRecords($this->getPageFixture());
    $this->assertInstanceOf(Configurations::class, $filterGroup->records());
  }

  /**
   * @covers \PapayaPluginFilterContentRecords
   */
  public function testIteratorFetchesPlugins() {
    $plugins = $this->createMock(\PapayaPluginLoader::class);
    $plugins
      ->expects($this->once())
      ->method('get')
      ->with('guid', $this->isInstanceOf(\PapayaUiContentPage::class), 'options')
      ->will($this->returnValue($this->createMock(\PapayaPluginFilterContent::class)));

    $records = $this->createMock(Configurations::class);
    $records
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(
          new ArrayIterator(
            array(
              array(
                'module_guid' => 'guid',
                'options' => 'options'
              )
            )
          )
        )
      );

    $filterGroup = new \PapayaPluginFilterContentRecords($this->getPageFixture());
    $filterGroup->papaya($this->mockPapaya()->application(array('plugins' => $plugins)));
    $filterGroup->records($records);

    $this->assertCount(1, iterator_to_array($filterGroup));
  }

  public function getPageFixture($viewId = NULL) {
    $page = $this
      ->getMockBuilder(\PapayaUiContentPage::class)
      ->disableOriginalConstructor()
      ->getMock();
    if (NULL !== $viewId) {
      $page
        ->expects($this->once())
        ->method('getPageViewId')
        ->will($this->returnValue($viewId));
    }
    return $page;
  }

}
