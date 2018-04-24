<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiListviewItemPagingTest extends PapayaTestCase {

  /**
  * @covers PapayaUiListviewItemPaging::__construct
  */
  public function testConstructor() {
    $item = new PapayaUiListviewItemPaging_TestProxy('foo/page', 2, 100);
    $this->assertEquals('foo[page]', (string)$item->parameterName);
    $this->assertEquals(2, $item->currentPage);
    $this->assertEquals(10, $item->currentOffset);
    $this->assertEquals(100, $item->itemsCount);
  }

  /**
  * @covers PapayaUiListviewItemPaging::appendTo
  * @covers PapayaUiListviewItemPaging::appendCaption
  * @covers PapayaUiListviewItemPaging::appendPageLink
  */
  public function testAppendToWithoutPagesExpectingEmptyString() {
    $item = new PapayaUiListviewItemPaging_TestProxy('page', 1, 100);
    $item->papaya(
      $this->mockPapaya()->application(
        array(
          'Images' => $this->getImagesFixture()
        )
      )
    );
    $this->assertEquals(
      '',
      $item->getXml()
    );
  }

  /**
  * @covers PapayaUiListviewItemPaging::appendTo
  * @covers PapayaUiListviewItemPaging::appendCaption
  * @covers PapayaUiListviewItemPaging::appendPageLink
  */
  public function testAppendToWithTwoPages() {
    $item = new PapayaUiListviewItemPaging_TestProxy('page', 1, 100);
    $item->papaya(
      $this->mockPapaya()->application(
        array(
          'Images' => $this->getImagesFixture()
        )
      )
    );
    $item->pageList = array(21, 42);
    $this->assertEquals(
      '<listitem image="table.png" href="http://www.test.tld/test.html?page=23">'.
        '<caption>'.
          '<a href="http://www.test.tld/test.html?page=21">21</a>'.
          '<a href="http://www.test.tld/test.html?page=42">42</a>'.
        '</caption>'.
      '</listitem>',
      $item->getXml()
    );
  }

  /**
  * @covers PapayaUiListviewItemPaging::appendTo
  * @covers PapayaUiListviewItemPaging::appendCaption
  * @covers PapayaUiListviewItemPaging::appendPageLink
  */
  public function testAppendToWithSeparator() {
    $item = new PapayaUiListviewItemPaging_TestProxy('page', 1, 100);
    $item->papaya(
      $this->mockPapaya()->application(
        array(
          'Images' => $this->getImagesFixture()
        )
      )
    );
    $item->separator = ' | ';
    $item->pageList = array(21, 42);
    $this->assertEquals(
      '<listitem image="table.png" href="http://www.test.tld/test.html?page=23">'.
        '<caption>'.
          '<a href="http://www.test.tld/test.html?page=21">21</a> | '.
          '<a href="http://www.test.tld/test.html?page=42">42</a>'.
        '</caption>'.
      '</listitem>',
      $item->getXml()
    );
  }

  /**
  * @covers PapayaUiListviewItemPaging::appendTo
  */
  public function testAppendToWithColumnSpan() {
    $item = new PapayaUiListviewItemPaging_TestProxy('page', 1, 100);
    $item->papaya(
      $this->mockPapaya()->application(
        array(
          'Images' => $this->getImagesFixture()
        )
      )
    );
    $item->columnSpan = 4;
    $item->pageList = array(21, 42);
    $this->assertEquals(
      '<listitem image="table.png" href="http://www.test.tld/test.html?page=23" span="4">'.
        '<caption>'.
          '<a href="http://www.test.tld/test.html?page=21">21</a>'.
          '<a href="http://www.test.tld/test.html?page=42">42</a>'.
        '</caption>'.
      '</listitem>',
      $item->getXml()
    );
  }

  /**
  * @covers PapayaUiListviewItemPaging::appendTo
  */
  public function testAppendToWhileSelected() {
    $item = new PapayaUiListviewItemPaging_TestProxy('page', 1, 100);
    $item->papaya(
      $this->mockPapaya()->application(
        array(
          'Images' => $this->getImagesFixture()
        )
      )
    );
    $item->selected = TRUE;
    $item->pageList = array(21, 42);
    $this->assertEquals(
      '<listitem image="table.png" href="http://www.test.tld/test.html?page=23"'.
        ' selected="selected">'.
        '<caption>'.
          '<a href="http://www.test.tld/test.html?page=21">21</a>'.
          '<a href="http://www.test.tld/test.html?page=42">42</a>'.
        '</caption>'.
      '</listitem>',
      $item->getXml()
    );
  }

  /**
  * @covers PapayaUiListviewItemPaging::setItemsCount
  */
  public function testSetItemsCount() {
    $item = new PapayaUiListviewItemPaging_TestProxy('page', 0, 30);
    $item->itemsCount = 100;
    $this->assertEquals(100, $item->itemsCount);
  }

  /**
  * @covers PapayaUiListviewItemPaging::setItemsCount
  */
  public function testSetItemsCountExpectingException() {
    $item = new PapayaUiListviewItemPaging_TestProxy('page', 0, 30);
    $this->setExpectedException(
      'UnexpectedValueException',
      'UnexpectedValueException: Item count can not be negative.'
    );
    $item->itemsCount = -42;
  }

  /**
  * @covers PapayaUiListviewItemPaging::setItemsPerPage
  */
  public function testSetItemsPerPage() {
    $item = new PapayaUiListviewItemPaging_TestProxy('page', 0, 30);
    $item->itemsPerPage = 15;
    $this->assertEquals(15, $item->itemsPerPage);
  }

  /**
  * @covers PapayaUiListviewItemPaging::setItemsPerPage
  */
  public function testSetItemsPerPageExpectingException() {
    $item = new PapayaUiListviewItemPaging_TestProxy('page', 0, 30);
    $this->setExpectedException(
      'UnexpectedValueException',
      'UnexpectedValueException: Item page limit can not be less than 1.'
    );
    $item->itemsPerPage = 0;
  }

  /**
  * @covers PapayaUiListviewItemPaging::setPageLimit
  */
  public function testSetPageLimit() {
    $item = new PapayaUiListviewItemPaging_TestProxy('page', 0, 30);
    $item->pageLimit = 15;
    $this->assertEquals(15, $item->pageLimit);
  }

  /**
  * @covers PapayaUiListviewItemPaging::setPageLimit
  */
  public function testSetPageLimitExpectingException() {
    $item = new PapayaUiListviewItemPaging_TestProxy('page', 0, 30);
    $this->setExpectedException(
      'UnexpectedValueException',
      'UnexpectedValueException: Page limit can not be less than 1.'
    );
    $item->pageLimit = 0;
  }

  /**
  * @covers PapayaUiListviewItemPaging::setCurrentValue
  */
  public function testSetCurrentValueUsingPageMode() {
    $item = new PapayaUiListviewItemPaging_TestProxy('page', 0, 30);
    $item->setCurrentValue(2);
    $this->assertEquals(2, $item->currentPage);
  }

  /**
  * @covers PapayaUiListviewItemPaging::setCurrentValue
  */
  public function testSetCurrentValueUsingOffsetMode() {
    $item = new PapayaUiListviewItemPaging_TestProxy(
      'page', 0, 30, PapayaUiListviewItemPaging::MODE_OFFSET
    );
    $item->setCurrentValue(10);
    $this->assertEquals(2, $item->currentPage);
  }

  /**
  * @covers PapayaUiListviewItemPaging::setCurrentPage
  * @covers PapayaUiListviewItemPaging::getCurrentPage
  */
  public function testGetCurrentPageAfterSet() {
    $item = new PapayaUiListviewItemPaging_TestProxy('page', 0, 30);
    $item->currentPage = 2;
    $this->assertEquals(2, $item->currentPage);
  }

  /**
  * @covers PapayaUiListviewItemPaging::getCurrentPage
  */
  public function testGetCurentPageAfterSettingToSmallValue() {
    $item = new PapayaUiListviewItemPaging_TestProxy('page', 0, 30);
    $item->currentPage = -99;
    $this->assertEquals(1, $item->currentPage);
  }

  /**
  * @covers PapayaUiListviewItemPaging::getCurrentPage
  */
  public function testGetCurentPageAfterSettingToLargeValue() {
    $item = new PapayaUiListviewItemPaging_TestProxy('page', 0, 30);
    $item->currentPage = 99;
    $this->assertEquals(3, $item->currentPage);
  }

  /**
  * @covers PapayaUiListviewItemPaging::setCurrentOffset
  * @covers PapayaUiListviewItemPaging::getCurrentOffset
  */
  public function testGetCurrentOffsetAfterSet() {
    $item = new PapayaUiListviewItemPaging_TestProxy('page', 0, 30);
    $item->currentOffset = 10;
    $this->assertEquals(10, $item->currentOffset);
  }

  /**
  * @covers PapayaUiListviewItemPaging::getLastPage
  * @dataProvider provideLastPageCalculationData
  */
  public function testLastPageCalculation($itemsPerPage, $itemsCount, $expectedMaximum) {
    $item = new PapayaUiListviewItemPaging_TestProxy('page', 0, $itemsCount);
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

class PapayaUiListviewItemPaging_TestProxy extends PapayaUiListviewItemPaging {

  public $pageList = array();

  public function getPages() {
    return $this->pageList;
  }

  public function getImagePage() {
    return 23;
  }
}
