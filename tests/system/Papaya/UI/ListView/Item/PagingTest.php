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

namespace Papaya\UI\ListView\Item {

  require_once __DIR__.'/../../../../../bootstrap.php';

  class PagingTest extends \Papaya\TestFramework\TestCase {

    /**
     * @covers \Papaya\UI\ListView\Item\Paging::__construct
     */
    public function testConstructor() {
      $item = new Paging_TestProxy('foo/page', 2, 100);
      $this->assertEquals('foo[page]', (string)$item->parameterName);
      $this->assertEquals(2, $item->currentPage);
      $this->assertEquals(10, $item->currentOffset);
      $this->assertEquals(100, $item->itemsCount);
    }

    /**
     * @covers \Papaya\UI\ListView\Item\Paging::appendTo
     * @covers \Papaya\UI\ListView\Item\Paging::appendCaption
     * @covers \Papaya\UI\ListView\Item\Paging::appendPageLink
     */
    public function testAppendToWithoutPagesExpectingEmptyString() {
      $item = new Paging_TestProxy('page', 1, 100);
      $item->papaya(
        $this->mockPapaya()->application(
          array(
            'Images' => $this->getImagesFixture()
          )
        )
      );
      $this->assertEquals(
        '',
        $item->getXML()
      );
    }

    /**
     * @covers \Papaya\UI\ListView\Item\Paging::appendTo
     * @covers \Papaya\UI\ListView\Item\Paging::appendCaption
     * @covers \Papaya\UI\ListView\Item\Paging::appendPageLink
     */
    public function testAppendToWithTwoPages() {
      $item = new Paging_TestProxy('page', 1, 100);
      $item->papaya(
        $this->mockPapaya()->application(
          array(
            'Images' => $this->getImagesFixture()
          )
        )
      );
      $item->pageList = array(21, 42);
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<listitem image="table.png" href="http://www.test.tld/test.html?page=23">
        <caption>
          <a href="http://www.test.tld/test.html?page=21">21</a>
          <a href="http://www.test.tld/test.html?page=42">42</a>
        </caption>
      </listitem>',
        $item->getXML()
      );
    }

    /**
     * @covers \Papaya\UI\ListView\Item\Paging::appendTo
     * @covers \Papaya\UI\ListView\Item\Paging::appendCaption
     * @covers \Papaya\UI\ListView\Item\Paging::appendPageLink
     */
    public function testAppendToWithSeparator() {
      $item = new Paging_TestProxy('page', 1, 100);
      $item->papaya(
        $this->mockPapaya()->application(
          array(
            'Images' => $this->getImagesFixture()
          )
        )
      );
      $item->separator = ' | ';
      $item->pageList = array(21, 42);
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<listitem image="table.png" href="http://www.test.tld/test.html?page=23">
        <caption><a 
        href="http://www.test.tld/test.html?page=21">21</a> | <a 
        href="http://www.test.tld/test.html?page=42">42</a></caption>
      </listitem>',
        $item->getXML()
      );
    }

    /**
     * @covers \Papaya\UI\ListView\Item\Paging::appendTo
     */
    public function testAppendToWithColumnSpan() {
      $item = new Paging_TestProxy('page', 1, 100);
      $item->papaya(
        $this->mockPapaya()->application(
          array(
            'Images' => $this->getImagesFixture()
          )
        )
      );
      $item->columnSpan = 4;
      $item->pageList = array(21, 42);
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<listitem image="table.png" href="http://www.test.tld/test.html?page=23" span="4">
        <caption>
          <a href="http://www.test.tld/test.html?page=21">21</a>
          <a href="http://www.test.tld/test.html?page=42">42</a>
        </caption>
      </listitem>',
        $item->getXML()
      );
    }

    /**
     * @covers \Papaya\UI\ListView\Item\Paging::appendTo
     */
    public function testAppendToWhileSelected() {
      $item = new Paging_TestProxy('page', 1, 100);
      $item->papaya(
        $this->mockPapaya()->application(
          array(
            'Images' => $this->getImagesFixture()
          )
        )
      );
      $item->selected = TRUE;
      $item->pageList = array(21, 42);
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<listitem image="table.png" href="http://www.test.tld/test.html?page=23"
         selected="selected">
        <caption>
          <a href="http://www.test.tld/test.html?page=21">21</a>
          <a href="http://www.test.tld/test.html?page=42">42</a>
        </caption>
      </listitem>',
        $item->getXML()
      );
    }

    /**
     * @covers \Papaya\UI\ListView\Item\Paging::setItemsCount
     */
    public function testSetItemsCount() {
      $item = new Paging_TestProxy('page', 0, 30);
      $item->itemsCount = 100;
      $this->assertEquals(100, $item->itemsCount);
    }

    /**
     * @covers \Papaya\UI\ListView\Item\Paging::setItemsCount
     */
    public function testSetItemsCountExpectingException() {
      $item = new Paging_TestProxy('page', 0, 30);
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('UnexpectedValueException: Item count can not be negative.');
      $item->itemsCount = -42;
    }

    /**
     * @covers \Papaya\UI\ListView\Item\Paging::setItemsPerPage
     */
    public function testSetItemsPerPage() {
      $item = new Paging_TestProxy('page', 0, 30);
      $item->itemsPerPage = 15;
      $this->assertEquals(15, $item->itemsPerPage);
    }

    /**
     * @covers \Papaya\UI\ListView\Item\Paging::setItemsPerPage
     */
    public function testSetItemsPerPageExpectingException() {
      $item = new Paging_TestProxy('page', 0, 30);
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('UnexpectedValueException: Item page limit can not be less than 1.');
      $item->itemsPerPage = 0;
    }

    /**
     * @covers \Papaya\UI\ListView\Item\Paging::setPageLimit
     */
    public function testSetPageLimit() {
      $item = new Paging_TestProxy('page', 0, 30);
      $item->pageLimit = 15;
      $this->assertEquals(15, $item->pageLimit);
    }

    /**
     * @covers \Papaya\UI\ListView\Item\Paging::setPageLimit
     */
    public function testSetPageLimitExpectingException() {
      $item = new Paging_TestProxy('page', 0, 30);
      $this->expectException(\UnexpectedValueException::class);
      $this->expectExceptionMessage('UnexpectedValueException: Page limit can not be less than 1.');
      $item->pageLimit = 0;
    }

    /**
     * @covers \Papaya\UI\ListView\Item\Paging::setCurrentValue
     */
    public function testSetCurrentValueUsingPageMode() {
      $item = new Paging_TestProxy('page', 0, 30);
      $item->setCurrentValue(2);
      $this->assertEquals(2, $item->currentPage);
    }

    /**
     * @covers \Papaya\UI\ListView\Item\Paging::setCurrentValue
     */
    public function testSetCurrentValueUsingOffsetMode() {
      $item = new Paging_TestProxy(
        'page', 0, 30, Paging::MODE_OFFSET
      );
      $item->setCurrentValue(10);
      $this->assertEquals(2, $item->currentPage);
    }

    /**
     * @covers \Papaya\UI\ListView\Item\Paging::setCurrentPage
     * @covers \Papaya\UI\ListView\Item\Paging::getCurrentPage
     */
    public function testGetCurrentPageAfterSet() {
      $item = new Paging_TestProxy('page', 0, 30);
      $item->currentPage = 2;
      $this->assertEquals(2, $item->currentPage);
    }

    /**
     * @covers \Papaya\UI\ListView\Item\Paging::getCurrentPage
     */
    public function testGetCurentPageAfterSettingToSmallValue() {
      $item = new Paging_TestProxy('page', 0, 30);
      $item->currentPage = -99;
      $this->assertEquals(1, $item->currentPage);
    }

    /**
     * @covers \Papaya\UI\ListView\Item\Paging::getCurrentPage
     */
    public function testGetCurentPageAfterSettingToLargeValue() {
      $item = new Paging_TestProxy('page', 0, 30);
      $item->currentPage = 99;
      $this->assertEquals(3, $item->currentPage);
    }

    /**
     * @covers \Papaya\UI\ListView\Item\Paging::setCurrentOffset
     * @covers \Papaya\UI\ListView\Item\Paging::getCurrentOffset
     */
    public function testGetCurrentOffsetAfterSet() {
      $item = new Paging_TestProxy('page', 0, 30);
      $item->currentOffset = 10;
      $this->assertEquals(10, $item->currentOffset);
    }

    /**
     * @covers \Papaya\UI\ListView\Item\Paging::getLastPage
     * @dataProvider provideLastPageCalculationData
     * @param int $itemsPerPage
     * @param int $itemsCount
     * @param int $expectedMaximum
     */
    public function testLastPageCalculation($itemsPerPage, $itemsCount, $expectedMaximum) {
      $item = new Paging_TestProxy('page', 0, $itemsCount);
      $item->itemsPerPage = $itemsPerPage;
      $this->assertEquals(
        $expectedMaximum, $item->lastPage
      );
    }

    /******************
     * Fixtures
     ******************/

    private function getImagesFixture() {
      return array(
        'items-table' => 'table.png'
      );
    }

    /******************
     * Data Provider
     ******************/

    public static function provideLastPageCalculationData() {
      return array(
        array(1, 1, 1),
        array(10, 100, 10),
        array(10, 99, 10),
        array(10, 101, 11)
      );
    }
  }

  class Paging_TestProxy extends Paging {

    public $pageList = array();

    public function getPages() {
      return $this->pageList;
    }

    public function getImagePage() {
      return 23;
    }
  }
}
