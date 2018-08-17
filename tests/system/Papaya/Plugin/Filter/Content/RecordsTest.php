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

namespace Papaya\Plugin\Filter\Content;

require_once __DIR__.'/../../../../../bootstrap.php';

class RecordsTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Plugin\Filter\Content\Records
   */
  public function testRecordsGetAfterSet() {
    $filterGroup = new Records($this->getPageFixture());
    $filterGroup->records($records = $this->createMock(\Papaya\Content\View\Configurations::class));
    $this->assertSame($records, $filterGroup->records());
  }

  /**
   * @covers \Papaya\Plugin\Filter\Content\Records
   */
  public function testRecordsImplicitCreate() {
    $filterGroup = new Records($this->getPageFixture());
    $this->assertInstanceOf(\Papaya\Content\View\Configurations::class, $filterGroup->records());
  }

  /**
   * @covers \Papaya\Plugin\Filter\Content\Records
   */
  public function testIteratorFetchesPlugins() {
    $plugins = $this->createMock(\Papaya\Plugin\Loader::class);
    $plugins
      ->expects($this->once())
      ->method('get')
      ->with('guid', $this->isInstanceOf(\Papaya\UI\Content\Page::class), 'options')
      ->will($this->returnValue($this->createMock(\Papaya\Plugin\Filter\Content::class)));

    $records = $this->createMock(\Papaya\Content\View\Configurations::class);
    $records
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(
          new \ArrayIterator(
            array(
              array(
                'module_guid' => 'guid',
                'options' => 'options'
              )
            )
          )
        )
      );

    $filterGroup = new Records($this->getPageFixture());
    $filterGroup->papaya($this->mockPapaya()->application(array('plugins' => $plugins)));
    $filterGroup->records($records);

    $this->assertCount(1, iterator_to_array($filterGroup));
  }

  public function getPageFixture($viewId = NULL) {
    $page = $this
      ->getMockBuilder(\Papaya\UI\Content\Page::class)
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
