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

class PapayaUiPagingCountTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\UI\Paging\Count::__construct
  */
  public function testConstructor() {
    $paging = new \Papaya\UI\Paging\Count('page', 2, 42);
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
  * @covers \Papaya\UI\Paging\Count::appendTo
  * @covers \Papaya\UI\Paging\Count::appendListElement
  * @covers \Papaya\UI\Paging\Count::appendPageElement
  * @covers \Papaya\UI\Paging\Count::calculate
  */
  public function testAppendToWithAdditionalParameters() {
    $paging = new \Papaya\UI\Paging\Count('foo/page', 0, 30);
    $paging->papaya($this->mockPapaya()->application());
    $paging->reference()->setParameters(array('foo' => array('size' => 10)));
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<paging count="3">
        <page href="http://www.test.tld/test.html?foo[page]=1&amp;foo[size]=10"
         number="1" selected="selected"/>
        <page href="http://www.test.tld/test.html?foo[page]=2&amp;foo[size]=10"
         number="2"/>
        <page href="http://www.test.tld/test.html?foo[page]=3&amp;foo[size]=10"
         number="3"/>
        <page href="http://www.test.tld/test.html?foo[page]=2&amp;foo[size]=10"
         number="2" type="next"/>
        <page href="http://www.test.tld/test.html?foo[page]=3&amp;foo[size]=10"
         number="3" type="last"/>
      </paging>',
      $paging->getXML()
    );
  }

  /**
  * @covers \Papaya\UI\Paging\Count::appendTo
  * @covers \Papaya\UI\Paging\Count::appendListElement
  * @covers \Papaya\UI\Paging\Count::appendPageElement
  * @covers \Papaya\UI\Paging\Count::calculate
  */
  public function testAppendToWithCurrentPageEqualsTwo() {
    $paging = new \Papaya\UI\Paging\Count('foo/page', 2, 30);
    $paging->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<paging count="3">
        <page href="http://www.test.tld/test.html?foo[page]=1" number="1" type="previous"/>
        <page href="http://www.test.tld/test.html?foo[page]=1" number="1"/>
        <page href="http://www.test.tld/test.html?foo[page]=2" number="2" selected="selected"/>
        <page href="http://www.test.tld/test.html?foo[page]=3" number="3"/>
        <page href="http://www.test.tld/test.html?foo[page]=3" number="3" type="next"/>
      </paging>',
      $paging->getXML()
    );
  }

  /**
  * @covers \Papaya\UI\Paging\Count::appendTo
  * @covers \Papaya\UI\Paging\Count::appendListElement
  * @covers \Papaya\UI\Paging\Count::appendPageElement
  * @covers \Papaya\UI\Paging\Count::calculate
  */
  public function testAppendToWithCurrentPageGreaterLastPage() {
    $paging = new \Papaya\UI\Paging\Count('foo/page', 99, 30);
    $paging->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<paging count="3">
        <page href="http://www.test.tld/test.html?foo[page]=1" number="1" type="first"/>
        <page href="http://www.test.tld/test.html?foo[page]=2" number="2" type="previous"/>
        <page href="http://www.test.tld/test.html?foo[page]=1" number="1"/>
        <page href="http://www.test.tld/test.html?foo[page]=2" number="2"/>
        <page href="http://www.test.tld/test.html?foo[page]=3" number="3" selected="selected"/>
      </paging>',
      $paging->getXML()
    );
  }

  /**
  * @covers \Papaya\UI\Paging\Count::appendTo
  * @covers \Papaya\UI\Paging\Count::appendListElement
  * @covers \Papaya\UI\Paging\Count::appendPageElement
  * @covers \Papaya\UI\Paging\Count::calculate
  */
  public function testAppendToWithLimitedPages() {
    $paging = new \Papaya\UI\Paging\Count('foo/page', 2, 300);
    $paging->papaya($this->mockPapaya()->application());
    $paging->pageLimit = 3;
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<paging count="30">
        <page href="http://www.test.tld/test.html?foo[page]=1" number="1" type="previous"/>
        <page href="http://www.test.tld/test.html?foo[page]=1" number="1"/>
        <page href="http://www.test.tld/test.html?foo[page]=2" number="2" selected="selected"/>
        <page href="http://www.test.tld/test.html?foo[page]=3" number="3"/>
        <page href="http://www.test.tld/test.html?foo[page]=3" number="3" type="next"/>
        <page href="http://www.test.tld/test.html?foo[page]=30" number="30" type="last"/>
      </paging>',
      $paging->getXML()
    );
  }

  /**
  * @covers \Papaya\UI\Paging\Count::setXMLNames
  * @covers \Papaya\UI\Paging\Count::appendListElement
  * @covers \Papaya\UI\Paging\Count::appendPageElement
  */
  public function testAppendToWithDifferentXml() {
    $paging = new \Papaya\UI\Paging\Count('foo/page', 2, 30);
    $paging->setXMLNames(
      array(
        'list' => 'PagingLinks',
        'item' => 'Page'
      )
    );
    $paging->papaya($this->mockPapaya()->application());
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<PagingLinks count="3">
        <Page href="http://www.test.tld/test.html?foo[page]=1" number="1" type="previous"/>
        <Page href="http://www.test.tld/test.html?foo[page]=1" number="1"/>
        <Page href="http://www.test.tld/test.html?foo[page]=2" number="2" selected="selected"/>
        <Page href="http://www.test.tld/test.html?foo[page]=3" number="3"/>
        <Page href="http://www.test.tld/test.html?foo[page]=3" number="3" type="next"/>
      </PagingLinks>',
      $paging->getXML()
    );
  }

  /**
  * @covers \Papaya\UI\Paging\Count::setXMLNames
  */
  public function testSetXmlWithInvalidElement() {
    $paging = new \Papaya\UI\Paging\Count('foo/page', 2, 30);
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage('Invalid/unknown xml name element "invalid" with value "PagingLinks".');
    $paging->setXMLNames(
      array(
        'invalid' => 'PagingLinks'
      )
    );
  }

  /**
  * @covers \Papaya\UI\Paging\Count::setXMLNames
  */
  public function testSetXmlWithInvalidElementName() {
    $paging = new \Papaya\UI\Paging\Count('foo/page', 2, 30);
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage('Invalid/unknown xml name element "list" with value "23Invalid".');
    $paging->setXMLNames(
      array(
        'list' => '23Invalid'
      )
    );
  }

  /**
  * @covers \Papaya\UI\Paging\Count::setItemsCount
  * @covers \Papaya\UI\Paging\Count::reset
  */
  public function testSetItemsCountResetsCalculations() {
    $paging = new \Papaya\UI\Paging\Count('page', 0, 30);
    $paging->papaya($this->mockPapaya()->application());
    //trigger calculation
    $paging->currentPage;
    $paging->itemsCount = 100;
    $this->assertAttributeEquals(
      100, '_itemsCount', $paging
    );
    $this->assertAttributeEquals(
      FALSE, '_calculated', $paging
    );
  }

  /**
  * @covers \Papaya\UI\Paging\Count::setItemsCount
  */
  public function testSetItemsCountExpectingException() {
    $paging = new \Papaya\UI\Paging\Count('page', 0, 30);
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage('UnexpectedValueException: Item count can not be negative.');
    $paging->itemsCount = -42;
  }

  /**
  * @covers \Papaya\UI\Paging\Count::setItemsPerPage
  */
  public function testSetItemsPerPageResetsCalculations() {
    $paging = new \Papaya\UI\Paging\Count('page', 0, 30);
    $paging->papaya($this->mockPapaya()->application());
    //trigger calculation
    $paging->currentPage;
    $paging->itemsPerPage = 15;
    $this->assertAttributeEquals(
      15, '_itemsPerPage', $paging
    );
    $this->assertAttributeSame(
      FALSE, '_calculated', $paging
    );
  }

  /**
  * @covers \Papaya\UI\Paging\Count::setItemsPerPage
  */
  public function testSetItemsPerPageExpectingException() {
    $paging = new \Papaya\UI\Paging\Count('page', 0, 30);
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage('UnexpectedValueException: Item page limit can not be less than 1.');
    $paging->itemsPerPage = 0;
  }

  /**
  * @covers \Papaya\UI\Paging\Count::setPageLimit
  */
  public function testSetPageLimitResetsCalculations() {
    $paging = new \Papaya\UI\Paging\Count('page', 0, 30);
    $paging->papaya($this->mockPapaya()->application());
    //trigger calculation
    $paging->currentPage;
    $paging->pageLimit = 15;
    $this->assertAttributeEquals(
      15, '_pageLimit', $paging
    );
    $this->assertAttributeSame(
      FALSE, '_calculated', $paging
    );
  }

  /**
  * @covers \Papaya\UI\Paging\Count::setPageLimit
  */
  public function testSetPageLimitExpectingException() {
    $paging = new \Papaya\UI\Paging\Count('page', 0, 30);
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage('UnexpectedValueException: Page limit can not be less than 3.');
    $paging->pageLimit = 2;
  }

  /**
  * @covers \Papaya\UI\Paging\Count::setCurrentPage
  */
  public function testSetCurrentPageResetsCalculations() {
    $paging = new \Papaya\UI\Paging\Count('page', 0, 30);
    $paging->papaya($this->mockPapaya()->application());
    //trigger calculation
    $paging->currentPage;
    $paging->currentPage = 15;
    $this->assertAttributeEquals(
      15, '_currentPage', $paging
    );
    $this->assertAttributeSame(
      FALSE, '_calculated', $paging
    );
  }

  /**
  * @covers \Papaya\UI\Paging\Count::getCurrentPage
  */
  public function testGetCurrentPageTriggersCalculation() {
    $paging = new \Papaya\UI\Paging\Count('page', 100, 30);
    $paging->papaya($this->mockPapaya()->application());
    $this->assertEquals(
      3, $paging->currentPage
    );
    $this->assertAttributeSame(
      TRUE, '_calculated', $paging
    );
  }

  /**
   * @covers \Papaya\UI\Paging\Count::getLastPage
   * @dataProvider provideLastPageCalculationData
   * @param int $itemsPerPage
   * @param int $itemsCount
   * @param int $expectedMaximum
   */
  public function testLastPageCalculation($itemsPerPage, $itemsCount, $expectedMaximum) {
    $paging = new \Papaya\UI\Paging\Count('page', 0, $itemsCount);
    $paging->itemsPerPage = $itemsPerPage;
    $this->assertEquals(
      $expectedMaximum, $paging->lastPage
    );
  }

  /**
  * @covers \Papaya\UI\Paging\Count::reference
  */
  public function testReferenceGetAfterSet() {
    $reference = $this->createMock(\Papaya\UI\Reference::class);
    $paging = new \Papaya\UI\Paging\Count('page', 0, 30);
    $paging->reference($reference);
    $this->assertSame(
      $reference, $paging->reference()
    );
  }

  /**
  * @covers \Papaya\UI\Paging\Count::reference
  */
  public function testReferenceGetImplicitCreate() {
    $paging = new \Papaya\UI\Paging\Count('page', 0, 30);
    $paging->papaya(
      $application = $this->mockPapaya()->application()
    );
    $this->assertInstanceOf(
      \Papaya\UI\Reference::class, $paging->reference()
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
