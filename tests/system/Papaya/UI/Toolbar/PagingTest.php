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

namespace Papaya\UI\Toolbar;
require_once __DIR__.'/../../../../bootstrap.php';

/**
 * @covers \Papaya\UI\Toolbar\Paging
 */
class PagingTest extends \Papaya\TestFramework\TestCase {

  public function testSetItemsCountResetsCalculations() {
    $paging = new Paging('foo/page', 30);
    $paging->papaya($this->mockPapaya()->application());
    //trigger calculation
    $paging->currentPage;
    $paging->itemsCount = 100;
    $this->assertFalse($paging->isCalculated());
    $this->assertEquals(
      100, $paging->itemsCount
    );
  }

  public function testSetItemsCountExpectingException() {
    $paging = new Paging('foo/page', 30);
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage('UnexpectedValueException: Item count can not be negative.');
    $paging->itemsCount = -42;
  }

  public function testSetItemsPerPageResetsCalculations() {
    $paging = new Paging('foo/page', 30);
    $paging->papaya($this->mockPapaya()->application());
    //trigger calculation
    $paging->currentPage;
    $paging->itemsPerPage = 15;
    $this->assertFalse($paging->isCalculated());
    $this->assertEquals(
      15, $paging->itemsPerPage
    );
  }

  public function testSetItemsPerPageExpectingException() {
    $paging = new Paging('foo/page', 30);
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage('UnexpectedValueException: Item page limit can not be less than 1.');
    $paging->itemsPerPage = 0;
  }

  public function testSetButtonLimitResetsCalculations() {
    $paging = new Paging('foo/page', 30);
    $paging->papaya($this->mockPapaya()->application());
    //trigger calculation
    $paging->currentPage;
    $paging->buttonLimit = 15;
    $this->assertFalse($paging->isCalculated());
    $this->assertEquals(
      15, $paging->buttonLimit
    );
  }

  public function testSetButtonLimitExpectingException() {
    $paging = new Paging('foo/page', 30);
    $this->expectException(\UnexpectedValueException::class);
    $this->expectExceptionMessage('UnexpectedValueException: Button limit can not be less than 3.');
    $paging->buttonLimit = 2;
  }

  public function testGetCurrentPageAfterSet() {
    $paging = new Paging('foo/page', 30);
    $paging->currentPage = 3;
    $this->assertEquals(3, $paging->currentPage);
  }

  public function testGetCurrentPageFromRequest() {
    $paging = new Paging('page', 30);
    $paging->papaya(
      $this->mockPapaya()->application(
        array(
          'Request' => $this->mockPapaya()->request(array('page' => 3), 'http://www.test.tld/')
        )
      )
    );
    $this->assertEquals(3, $paging->currentPage);
  }

  public function testGetCurrentPageFromRequestUsingOffset() {
    $paging = new Paging('offset', 30, Paging::MODE_OFFSET);
    $paging->papaya(
      $this->mockPapaya()->application(
        array(
          'Request' => $this->mockPapaya()->request(array('offset' => 20), 'http://www.test.tld/')
        )
      )
    );
    $this->assertEquals(3, $paging->currentPage);
  }

  public function testGetCurrentPageFromRequestValidatedAndSetToMaximum() {
    $paging = new Paging('page', 30);
    $paging->papaya(
      $this->mockPapaya()->application(
        array(
          'Request' => $this->mockPapaya()->request(array('page' => 99), 'http://www.test.tld/')
        )
      )
    );
    $this->assertEquals(3, $paging->currentPage);
  }

  public function testGetCurrentPageExpectingOneAsDefaultValue() {
    $paging = new Paging('page', 30);
    $paging->papaya(
      $this->mockPapaya()->application()
    );
    $this->assertEquals(1, $paging->currentPage);
  }

  public function testGetCurrentPageValidatedAndSetToLastPage() {
    $paging = new Paging('page', 30);
    $paging->currentPage = 99;
    $this->assertEquals(3, $paging->currentPage);
  }

  /**
   * @dataProvider providePageToOffsetPairs
   * @param int $page
   * @param int $offset
   */
  public function testGetCurrentOffset($page, $offset) {
    $paging = new Paging('page', 30);
    $paging->currentPage = $page;
    $this->assertEquals($offset, $paging->currentOffset);
  }

  /**
   * @dataProvider provideOffsetToPagePairs
   * @param int $offset
   * @param int $page
   */
  public function testSetCurrentOffset($offset, $page) {
    $paging = new Paging('page', 30);
    $paging->currentOffset = $offset;
    $this->assertEquals($page, $paging->currentPage);
  }

  /**
   * @dataProvider provideLastPageCalculationData
   * @param int $itemsPerPage
   * @param int $itemsCount
   * @param int $expectedMaximum
   */
  public function testLastPageCalculation($itemsPerPage, $itemsCount, $expectedMaximum) {
    $paging = new Paging('page', $itemsCount);
    $paging->itemsPerPage = $itemsPerPage;
    $this->assertEquals(
      $expectedMaximum, $paging->lastPage
    );
  }

  public function testAppendToWithAdditionalParameters() {
    $document = new \Papaya\XML\Document;
    $document->appendElement('sample');
    $paging = new Paging('foo/page', 30);
    $paging->papaya(
      $this->mockPapaya()->application(
        array(
          'Images' => $this->getImagesFixture()
        )
      )
    );
    $paging->currentPage = 0;
    $paging->reference()->setParameters(array('foo' => array('size' => 10)));
    $paging->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<sample>
        <button title="1" href="http://www.test.tld/test.html?foo[page]=1&amp;foo[size]=10"
         down="down"/>
        <button title="2" href="http://www.test.tld/test.html?foo[page]=2&amp;foo[size]=10"/>
        <button title="3" href="http://www.test.tld/test.html?foo[page]=3&amp;foo[size]=10"/>
        <button glyph="next.png" hint="Next page"
         href="http://www.test.tld/test.html?foo[page]=2&amp;foo[size]=10"/>
        <button glyph="last.png" hint="Last page"
         href="http://www.test.tld/test.html?foo[page]=3&amp;foo[size]=10"/>
        </sample>',
      $document->saveXML($document->documentElement)
    );
  }

  public function testAppendToWithCurrentPageEqualsTwo() {
    $document = new \Papaya\XML\Document;
    $document->appendElement('sample');
    $paging = new Paging('foo/page', 30);
    $paging->papaya(
      $this->mockPapaya()->application(
        array(
          'Images' => $this->getImagesFixture()
        )
      )
    );
    $paging->currentPage = 2;
    $paging->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<sample>
        <button glyph="previous.png" hint="Previous page"
         href="http://www.test.tld/test.html?foo[page]=1"/>
        <button title="1" href="http://www.test.tld/test.html?foo[page]=1"/>
        <button title="2" href="http://www.test.tld/test.html?foo[page]=2"
         down="down"/>
        <button title="3" href="http://www.test.tld/test.html?foo[page]=3"/>
        <button glyph="next.png" hint="Next page"
         href="http://www.test.tld/test.html?foo[page]=3"/>
        </sample>',
      $document->saveXML($document->documentElement)
    );
  }

  public function testAppendToWithLimitedButton() {
    $document = new \Papaya\XML\Document;
    $document->appendElement('sample');
    $paging = new Paging('foo/page', 300);
    $paging->papaya(
      $this->mockPapaya()->application(
        array(
          'Images' => $this->getImagesFixture()
        )
      )
    );
    $paging->currentOffset = 400;
    $paging->buttonLimit = 3;
    $paging->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<sample>
        <button glyph="first.png" hint="First page"
         href="http://www.test.tld/test.html?foo[page]=1"/>
        <button glyph="previous.png" hint="Previous page"
         href="http://www.test.tld/test.html?foo[page]=29"/>
        <button title="28" href="http://www.test.tld/test.html?foo[page]=28"/>
        <button title="29" href="http://www.test.tld/test.html?foo[page]=29"/>
        <button title="30" href="http://www.test.tld/test.html?foo[page]=30" down="down"/>
        </sample>',
      $document->saveXML($document->documentElement)
    );
  }

  public function testAppendToWithCurrentOffsetEquals10() {
    $document = new \Papaya\XML\Document;
    $document->appendElement('sample');
    $paging = new Paging('foo/offset', 30, Paging::MODE_OFFSET);
    $paging->papaya(
      $this->mockPapaya()->application(
        array(
          'Images' => $this->getImagesFixture()
        )
      )
    );
    $paging->currentOffset = 10;
    $paging->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<sample>
        <button glyph="previous.png" hint="Previous page"
         href="http://www.test.tld/test.html?foo[offset]=0"/>
        <button title="1" href="http://www.test.tld/test.html?foo[offset]=0"/>
        <button title="2" href="http://www.test.tld/test.html?foo[offset]=10"
         down="down"/>
        <button title="3" href="http://www.test.tld/test.html?foo[offset]=20"/>
        <button glyph="next.png" hint="Next page"
         href="http://www.test.tld/test.html?foo[offset]=20"/>
        </sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /******************
   * Fixtures
   ******************/

  private function getImagesFixture() {
    return array(
      'actions-go-first' => 'first.png',
      'actions-go-previous' => 'previous.png',
      'actions-go-next' => 'next.png',
      'actions-go-last' => 'last.png'
    );
  }

  /******************
   * Data Provider
   ******************/

  public static function providePageToOffsetPairs() {
    return array(
      array(0, 0),
      array(1, 0),
      array(2, 10),
      array(3, 20),
      array(4, 20)
    );
  }

  public static function provideOffsetToPagePairs() {
    return array(
      array(0, 1),
      array(10, 2),
      array(20, 3),
      array(30, 3)
    );
  }

  public static function provideLastPageCalculationData() {
    return array(
      array(1, 1, 1),
      array(10, 100, 10),
      array(10, 99, 10),
      array(10, 101, 11)
    );
  }
}
