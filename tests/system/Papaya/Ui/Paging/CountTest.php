<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiPagingCountTest extends PapayaTestCase {

  /**
  * @covers PapayaUiPagingCount::__construct
  */
  public function testConstructor() {
    $paging = new PapayaUiPagingCount('page', 2, 42);
    $this->assertAttributeEquals(
      'page', '_parameterName', $paging
    );
    $this->assertAttributeEquals(
      2, '_currentPage', $paging
    );
    $this->assertAttributeEquals(
      42, '_itemsCount', $paging
    );
  }

  /**
  * @covers PapayaUiPagingCount::appendTo
  * @covers PapayaUiPagingCount::appendListElement
  * @covers PapayaUiPagingCount::appendPageElement
  * @covers PapayaUiPagingCount::calculate
  */
  public function testAppendToWithAdditionalParameters() {
    $paging = new PapayaUiPagingCount('foo/page', 0, 30);
    $paging->papaya($this->mockPapaya()->application());
    $paging->reference()->setParameters(array('foo' => array('size' => 10)));
    $this->assertEquals(
      '<paging count="3">'.
        '<page href="http://www.test.tld/test.html?foo[page]=1&amp;foo[size]=10"'.
        ' number="1" selected="selected"/>'.
        '<page href="http://www.test.tld/test.html?foo[page]=2&amp;foo[size]=10"'.
        ' number="2"/>'.
        '<page href="http://www.test.tld/test.html?foo[page]=3&amp;foo[size]=10"'.
        ' number="3"/>'.
        '<page href="http://www.test.tld/test.html?foo[page]=2&amp;foo[size]=10"'.
        ' number="2" type="next"/>'.
        '<page href="http://www.test.tld/test.html?foo[page]=3&amp;foo[size]=10"'.
        ' number="3" type="last"/>'.
      '</paging>',
      $paging->getXml()
    );
  }

  /**
  * @covers PapayaUiPagingCount::appendTo
  * @covers PapayaUiPagingCount::appendListElement
  * @covers PapayaUiPagingCount::appendPageElement
  * @covers PapayaUiPagingCount::calculate
  */
  public function testAppendToWithCurrentPageEqualsTwo() {
    $paging = new PapayaUiPagingCount('foo/page', 2, 30);
    $paging->papaya($this->mockPapaya()->application());
    $this->assertEquals(
      '<paging count="3">'.
        '<page href="http://www.test.tld/test.html?foo[page]=1" number="1" type="previous"/>'.
        '<page href="http://www.test.tld/test.html?foo[page]=1" number="1"/>'.
        '<page href="http://www.test.tld/test.html?foo[page]=2" number="2" selected="selected"/>'.
        '<page href="http://www.test.tld/test.html?foo[page]=3" number="3"/>'.
        '<page href="http://www.test.tld/test.html?foo[page]=3" number="3" type="next"/>'.
      '</paging>',
      $paging->getXml()
    );
  }

  /**
  * @covers PapayaUiPagingCount::appendTo
  * @covers PapayaUiPagingCount::appendListElement
  * @covers PapayaUiPagingCount::appendPageElement
  * @covers PapayaUiPagingCount::calculate
  */
  public function testAppendToWithCurrentPageGreaterLastPage() {
    $paging = new PapayaUiPagingCount('foo/page', 99, 30);
    $paging->papaya($this->mockPapaya()->application());
    $this->assertEquals(
      '<paging count="3">'.
        '<page href="http://www.test.tld/test.html?foo[page]=1" number="1" type="first"/>'.
        '<page href="http://www.test.tld/test.html?foo[page]=2" number="2" type="previous"/>'.
        '<page href="http://www.test.tld/test.html?foo[page]=1" number="1"/>'.
        '<page href="http://www.test.tld/test.html?foo[page]=2" number="2"/>'.
        '<page href="http://www.test.tld/test.html?foo[page]=3" number="3" selected="selected"/>'.
      '</paging>',
      $paging->getXml()
    );
  }

  /**
  * @covers PapayaUiPagingCount::appendTo
  * @covers PapayaUiPagingCount::appendListElement
  * @covers PapayaUiPagingCount::appendPageElement
  * @covers PapayaUiPagingCount::calculate
  */
  public function testAppendToWithLimitedPages() {
    $paging = new PapayaUiPagingCount('foo/page', 2, 300);
    $paging->papaya($this->mockPapaya()->application());
    $paging->pageLimit = 3;
    $this->assertXmlStringEqualsXmlString(
      '<paging count="30">'.
        '<page href="http://www.test.tld/test.html?foo[page]=1" number="1" type="previous"/>'.
        '<page href="http://www.test.tld/test.html?foo[page]=1" number="1"/>'.
        '<page href="http://www.test.tld/test.html?foo[page]=2" number="2" selected="selected"/>'.
        '<page href="http://www.test.tld/test.html?foo[page]=3" number="3"/>'.
        '<page href="http://www.test.tld/test.html?foo[page]=3" number="3" type="next"/>'.
        '<page href="http://www.test.tld/test.html?foo[page]=30" number="30" type="last"/>'.
      '</paging>',
      $paging->getXml()
    );
  }

  /**
  * @covers PapayaUiPagingCount::setXmlNames
  * @covers PapayaUiPagingCount::appendListElement
  * @covers PapayaUiPagingCount::appendPageElement
  */
  public function testAppendToWithDifferentXml() {
    $paging = new PapayaUiPagingCount('foo/page', 2, 30);
    $paging->setXmlNames(
      array(
        'list' => 'PagingLinks',
        'item' => 'Page'
      )
    );
    $paging->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
      '<PagingLinks count="3">'.
        '<Page href="http://www.test.tld/test.html?foo[page]=1" number="1" type="previous"/>'.
        '<Page href="http://www.test.tld/test.html?foo[page]=1" number="1"/>'.
        '<Page href="http://www.test.tld/test.html?foo[page]=2" number="2" selected="selected"/>'.
        '<Page href="http://www.test.tld/test.html?foo[page]=3" number="3"/>'.
        '<Page href="http://www.test.tld/test.html?foo[page]=3" number="3" type="next"/>'.
      '</PagingLinks>',
      $paging->getXml()
    );
  }

  /**
  * @covers PapayaUiPagingCount::setXmlNames
  */
  public function testSetXmlWithInvalidElement() {
    $paging = new PapayaUiPagingCount('foo/page', 2, 30);
    $this->setExpectedException(
      'UnexpectedValueException',
      'Invalid/unknown xml name element "invalid" with value "PagingLinks".'
    );
    $paging->setXmlNames(
      array(
        'invalid' => 'PagingLinks'
      )
    );
  }

  /**
  * @covers PapayaUiPagingCount::setXmlNames
  */
  public function testSetXmlWithInvalidElementName() {
    $paging = new PapayaUiPagingCount('foo/page', 2, 30);
    $this->setExpectedException(
      'UnexpectedValueException',
      'Invalid/unknown xml name element "list" with value "23Invalid".'
    );
    $paging->setXmlNames(
      array(
        'list' => '23Invalid'
      )
    );
  }

  /**
  * @covers PapayaUiPagingCount::setItemsCount
  * @covers PapayaUiPagingCount::reset
  */
  public function testSetItemsCountResetsCalculations() {
    $paging = new PapayaUiPagingCount('page', 0, 30);
    $paging->papaya($this->mockPapaya()->application());
    //trigger calculation
    $dummy = $paging->currentPage;
    $paging->itemsCount = 100;
    $this->assertAttributeEquals(
      100, '_itemsCount', $paging
    );
    $this->assertAttributeEquals(
      FALSE, '_calculated', $paging
    );
  }

  /**
  * @covers PapayaUiPagingCount::setItemsCount
  */
  public function testSetItemsCountExpectingException() {
    $paging = new PapayaUiPagingCount('page', 0, 30);
    $this->setExpectedException(
      'UnexpectedValueException',
      'UnexpectedValueException: Item count can not be negative.'
    );
    $paging->itemsCount = -42;
  }

  /**
  * @covers PapayaUiPagingCount::setItemsPerPage
  */
  public function testSetItemsPerPageResetsCalculations() {
    $paging = new PapayaUiPagingCount('page', 0, 30);
    $paging->papaya($this->mockPapaya()->application());
    //trigger calculation
    $dummy = $paging->currentPage;
    $paging->itemsPerPage = 15;
    $this->assertAttributeEquals(
      15, '_itemsPerPage', $paging
    );
    $this->assertAttributeSame(
      FALSE, '_calculated', $paging
    );
  }

  /**
  * @covers PapayaUiPagingCount::setItemsPerPage
  */
  public function testSetItemsPerPageExpectingException() {
    $paging = new PapayaUiPagingCount('page', 0, 30);
    $this->setExpectedException(
      'UnexpectedValueException',
      'UnexpectedValueException: Item page limit can not be less than 1.'
    );
    $paging->itemsPerPage = 0;
  }

  /**
  * @covers PapayaUiPagingCount::setPageLimit
  */
  public function testSetPageLimitResetsCalculations() {
    $paging = new PapayaUiPagingCount('page', 0, 30);
    $paging->papaya($this->mockPapaya()->application());
    //trigger calculation
    $dummy = $paging->currentPage;
    $paging->pageLimit = 15;
    $this->assertAttributeEquals(
      15, '_pageLimit', $paging
    );
    $this->assertAttributeSame(
      FALSE, '_calculated', $paging
    );
  }

  /**
  * @covers PapayaUiPagingCount::setPageLimit
  */
  public function testSetPageLimitExpectingException() {
    $paging = new PapayaUiPagingCount('page', 0, 30);
    $this->setExpectedException(
      'UnexpectedValueException',
      'UnexpectedValueException: Page limit can not be less than 3.'
    );
    $paging->pageLimit = 2;
  }

  /**
  * @covers PapayaUiPagingCount::setCurrentPage
  */
  public function testSetCurrentPageResetsCalculations() {
    $paging = new PapayaUiPagingCount('page', 0, 30);
    $paging->papaya($this->mockPapaya()->application());
    //trigger calculation
    $dummy = $paging->currentPage;
    $paging->currentPage = 15;
    $this->assertAttributeEquals(
      15, '_currentPage', $paging
    );
    $this->assertAttributeSame(
      FALSE, '_calculated', $paging
    );
  }

  /**
  * @covers PapayaUiPagingCount::getCurrentPage
  */
  public function testGetCurrentPageTriggersCalculation() {
    $paging = new PapayaUiPagingCount('page', 100, 30);
    $paging->papaya($this->mockPapaya()->application());
    $this->assertEquals(
      3, $paging->currentPage
    );
    $this->assertAttributeSame(
      TRUE, '_calculated', $paging
    );
  }

  /**
  * @covers PapayaUiPagingCount::getLastPage
  * @dataProvider provideLastPageCalculationData
  */
  public function testLastPageCalculation($itemsPerPage, $itemsCount, $expectedMaximum) {
    $paging = new PapayaUiPagingCount('page', 0, $itemsCount);
    $paging->itemsPerPage = $itemsPerPage;
    $this->assertEquals(
      $expectedMaximum, $paging->lastPage
    );
  }

  /**
  * @covers PapayaUiPagingCount::reference
  */
  public function testReferenceGetAfterSet() {
    $reference = $this->createMock(PapayaUiReference::class);
    $paging = new PapayaUiPagingCount('page', 0, 30);
    $paging->reference($reference);
    $this->assertSame(
      $reference, $paging->reference()
    );
  }

  /**
  * @covers PapayaUiPagingCount::reference
  */
  public function testReferenceGetImplicitCreate() {
    $paging = new PapayaUiPagingCount('page', 0, 30);
    $paging->papaya(
      $application = $this->mockPapaya()->application()
    );
    $this->assertInstanceOf(
      'PapayaUiReference', $paging->reference()
    );
    $this->assertSame(
      $application, $paging->reference()->papaya()
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
