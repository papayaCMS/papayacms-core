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

namespace Papaya\CMS\Plugin\Filter\Content;
require_once __DIR__.'/../../../../../../bootstrap.php';

class GroupTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\CMS\Plugin\Filter\Content\Group
   */
  public function testConstructor() {
    $filter = new Group($page = $this->getPageFixture());
    $this->assertSame($page, $filter->getPage());
  }

  /**
   * @covers \Papaya\CMS\Plugin\Filter\Content\Group
   */
  public function testAddAndIterator() {
    $filter = new Group($page = $this->getPageFixture());
    $filter->add(
      $filterOne = $this->createMock(\Papaya\CMS\Plugin\Filter\Content::class)
    );
    $filter->add(
      $filterTwo = $this->createMock(\Papaya\CMS\Plugin\Filter\Content::class)
    );
    $this->assertSame(
      array($filterOne, $filterTwo),
      iterator_to_array($filter, FALSE)
    );
  }

  /**
   * @covers \Papaya\CMS\Plugin\Filter\Content\Group
   */
  public function testPrepare() {
    $filterOne = $this->createMock(\Papaya\CMS\Plugin\Filter\Content::class);
    $filterOne
      ->expects($this->once())
      ->method('prepare')
      ->with('data');

    $filterGroup = new Group($page = $this->getPageFixture());
    $filterGroup->add($filterOne);
    $filterGroup->prepare('data');
  }

  /**
   * @covers \Papaya\CMS\Plugin\Filter\Content\Group
   */
  public function testPrepareBC() {
    $filterOne = $this
      ->getMockBuilder(\stdClass::class)
      ->setMethods(array('initialize', 'prepareFilterData', 'loadFilterData', 'applyFilterData', 'getFilterData'))
      ->getMock();
    $filterOne
      ->expects($this->once())
      ->method('initialize')
      ->with($this->isInstanceOf(\stdClass::class));
    $filterOne
      ->expects($this->once())
      ->method('prepareFilterData')
      ->with(array('text' => 'data'), array('text'));
    $filterOne
      ->expects($this->once())
      ->method('loadFilterData')
      ->with(array('text' => 'data'));

    $filterGroup = new Group($page = $this->getPageFixture());
    $filterGroup->add($filterOne);
    $filterGroup->prepare('data');
  }

  /**
   * @covers \Papaya\CMS\Plugin\Filter\Content\Group
   */
  public function testApplyTo() {
    $filterOne = $this->createMock(\Papaya\CMS\Plugin\Filter\Content::class);
    $filterOne
      ->expects($this->once())
      ->method('applyTo')
      ->with('data')
      ->will($this->returnValue('success'));

    $filterGroup = new Group($page = $this->getPageFixture());
    $filterGroup->add($filterOne);
    $filterGroup->applyTo('data');
  }

  /**
   * @covers \Papaya\CMS\Plugin\Filter\Content\Group
   */
  public function testApplyToBC() {
    $filterOne = $this
      ->getMockBuilder(\stdClass::class)
      ->setMethods(array('initialize', 'prepareFilterData', 'loadFilterData', 'applyFilterData', 'getFilterData'))
      ->getMock();
    $filterOne
      ->expects($this->once())
      ->method('applyFilterData')
      ->with('data')
      ->will($this->returnValue('success'));

    $filterGroup = new Group($page = $this->getPageFixture());
    $filterGroup->add($filterOne);
    $filterGroup->applyTo('data');
  }

  /**
   * @covers \Papaya\CMS\Plugin\Filter\Content\Group
   */
  public function testAppendTo() {
    $document = new \Papaya\XML\Document();
    $node = $document->appendElement('test');
    $filterOne = $this->createMock(\Papaya\CMS\Plugin\Filter\Content::class);
    $filterOne
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));

    $filterGroup = new Group($page = $this->getPageFixture());
    $filterGroup->add($filterOne);
    $filterGroup->appendTo($node);
  }

  /**
   * @covers \Papaya\CMS\Plugin\Filter\Content\Group
   */
  public function testAppendToBC() {
    $document = new \Papaya\XML\Document();
    $node = $document->appendElement('test');
    $filterOne = $this
      ->getMockBuilder(\stdClass::class)
      ->setMethods(array('initialize', 'prepareFilterData', 'loadFilterData', 'applyFilterData', 'getFilterData'))
      ->getMock();
    $filterOne
      ->expects($this->once())
      ->method('getFilterData')
      ->with()
      ->will($this->returnValue('success'));

    $filterGroup = new Group($page = $this->getPageFixture());
    $filterGroup->add($filterOne);
    $filterGroup->appendTo($node);
    $this->assertEquals(/** @lang XML */
      '<test>success</test>', $node->saveXML());
  }

  public function getPageFixture() {
    $page = $this
      ->getMockBuilder(\Papaya\CMS\Output\Page::class)
      ->disableOriginalConstructor()
      ->getMock();
    return $page;
  }

}
