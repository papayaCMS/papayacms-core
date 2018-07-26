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

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaPluginFilterContentGroupTest extends \PapayaTestCase {

  /**
   * @covers \PapayaPluginFilterContentGroup
   */
  public function testConstructor() {
    $filter = new \PapayaPluginFilterContentGroup($page = $this->getPageFixture());
    $this->assertSame($page, $filter->getPage());
  }

  /**
   * @covers \PapayaPluginFilterContentGroup
   */
  public function testAddAndIterator() {
    $filter = new \PapayaPluginFilterContentGroup($page = $this->getPageFixture());
    $filter->add(
      $filterOne = $this->createMock(\PapayaPluginFilterContent::class)
    );
    $filter->add(
      $filterTwo = $this->createMock(\PapayaPluginFilterContent::class)
    );
    $this->assertSame(
      array($filterOne, $filterTwo),
      iterator_to_array($filter, FALSE)
    );
  }

  /**
   * @covers \PapayaPluginFilterContentGroup
   */
  public function testPrepare() {
    $filterOne = $this->createMock(\PapayaPluginFilterContent::class);
    $filterOne
      ->expects($this->once())
      ->method('prepare')
      ->with('data');

    $filterGroup = new \PapayaPluginFilterContentGroup($page = $this->getPageFixture());
    $filterGroup->add($filterOne);
    $filterGroup->prepare('data');
  }

  /**
   * @covers \PapayaPluginFilterContentGroup
   */
  public function testPrepareBC() {
    $filterOne = $this
      ->getMockBuilder(stdClass::class)
      ->setMethods(array('initialize', 'prepareFilterData', 'loadFilterData', 'applyFilterData', 'getFilterData'))
      ->getMock();
    $filterOne
      ->expects($this->once())
      ->method('initialize')
      ->with($this->isInstanceOf(stdClass::class));
    $filterOne
      ->expects($this->once())
      ->method('prepareFilterData')
      ->with(array('text' => 'data'), array('text'));
    $filterOne
      ->expects($this->once())
      ->method('loadFilterData')
      ->with(array('text' => 'data'));

    $filterGroup = new \PapayaPluginFilterContentGroup($page = $this->getPageFixture());
    $filterGroup->add($filterOne);
    $filterGroup->prepare('data');
  }

  /**
   * @covers \PapayaPluginFilterContentGroup
   */
  public function testApplyTo() {
    $filterOne = $this->createMock(\PapayaPluginFilterContent::class);
    $filterOne
      ->expects($this->once())
      ->method('applyTo')
      ->with('data')
      ->will($this->returnValue('success'));

    $filterGroup = new \PapayaPluginFilterContentGroup($page = $this->getPageFixture());
    $filterGroup->add($filterOne);
    $filterGroup->applyTo('data');
  }

  /**
   * @covers \PapayaPluginFilterContentGroup
   */
  public function testApplyToBC() {
    $filterOne = $this
      ->getMockBuilder(stdClass::class)
      ->setMethods(array('initialize', 'prepareFilterData', 'loadFilterData', 'applyFilterData', 'getFilterData'))
      ->getMock();
    $filterOne
      ->expects($this->once())
      ->method('applyFilterData')
      ->with('data')
      ->will($this->returnValue('success'));

    $filterGroup = new \PapayaPluginFilterContentGroup($page = $this->getPageFixture());
    $filterGroup->add($filterOne);
    $filterGroup->applyTo('data');
  }

  /**
   * @covers \PapayaPluginFilterContentGroup
   */
  public function testAppendTo() {
    $document = new \PapayaXmlDocument();
    $node = $document->appendElement('test');
    $filterOne = $this->createMock(\PapayaPluginFilterContent::class);
    $filterOne
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\PapayaXmlElement::class));

    $filterGroup = new \PapayaPluginFilterContentGroup($page = $this->getPageFixture());
    $filterGroup->add($filterOne);
    $filterGroup->appendTo($node);
  }

  /**
   * @covers \PapayaPluginFilterContentGroup
   */
  public function testAppendToBC() {
    $document = new \PapayaXmlDocument();
    $node = $document->appendElement('test');
    $filterOne = $this
      ->getMockBuilder(stdClass::class)
      ->setMethods(array('initialize', 'prepareFilterData', 'loadFilterData', 'applyFilterData', 'getFilterData'))
      ->getMock();
    $filterOne
      ->expects($this->once())
      ->method('getFilterData')
      ->with()
      ->will($this->returnValue('success'));

    $filterGroup = new \PapayaPluginFilterContentGroup($page = $this->getPageFixture());
    $filterGroup->add($filterOne);
    $filterGroup->appendTo($node);
    $this->assertEquals(/** @lang XML */'<test>success</test>', $node->saveXml());
  }

  public function getPageFixture() {
    $page = $this
      ->getMockBuilder(\PapayaUiContentPage::class)
      ->disableOriginalConstructor()
      ->getMock();
    return $page;
  }

}
