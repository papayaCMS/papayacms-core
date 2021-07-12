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

namespace Papaya\CMS\Administration\Page;

require_once __DIR__.'/../../../../../bootstrap.php';

class PartsTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\CMS\Administration\Page\Parts::__construct
   */
  public function testConstructor() {
    $parts = new Parts(
      $page = $this->getPageFixture()
    );
    $this->assertSame(
      $page, $parts->getPage()
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Page\Parts::__get
   * @covers \Papaya\CMS\Administration\Page\Parts::__set
   */
  public function testOffsetGetAfterOffsetSet() {
    $parts = new Parts(
      $page = $this->getPageFixture()
    );
    $parts->content = $part = $this->createMock(Part::class);
    $this->assertSame($part, $parts->content);
  }

  /**
   * @covers \Papaya\CMS\Administration\Page\Parts::get
   * @covers \Papaya\CMS\Administration\Page\Parts::set
   */
  public function testGetAfterSet() {
    $parts = new Parts(
      $page = $this->getPageFixture()
    );
    $parts->set('content', $part = $this->createMock(Part::class));
    $this->assertSame($part, $parts->get('content'));
  }

  /**
   * @covers \Papaya\CMS\Administration\Page\Parts::get
   * @covers \Papaya\CMS\Administration\Page\Parts::create
   */
  public function testGetImplicitCreate() {
    $page = $this->getPageFixture();
    $page
      ->expects($this->once())
      ->method('createPart')
      ->with('content')
      ->will($this->returnValue($this->createMock(Part::class)));
    $parts = new Parts($page);
    $this->assertInstanceOf(Part::class, $parts->get('content'));
  }

  /**
   * @covers \Papaya\CMS\Administration\Page\Parts::get
   * @covers \Papaya\CMS\Administration\Page\Parts::create
   */
  public function testGetCreateReturnsFalse() {
    $page = $this->getPageFixture();
    $page
      ->expects($this->once())
      ->method('createPart')
      ->with('content')
      ->will($this->returnValue(FALSE));
    $parts = new Parts($page);
    $this->assertFalse($parts->get('content'));
  }

  /**
   * @covers \Papaya\CMS\Administration\Page\Parts::get
   * @covers \Papaya\CMS\Administration\Page\Parts::create
   */
  public function testGetWithInvalidNameExpectingException() {
    $parts = new Parts($this->getPageFixture());
    $this->expectException(\UnexpectedValueException::class);
    $parts->get('INVALID');
  }

  /**
   * @covers \Papaya\CMS\Administration\Page\Parts::set
   */
  public function testSetWithInvalidNameExpectingException() {
    $parts = new Parts($this->getPageFixture());
    $this->expectException(\UnexpectedValueException::class);
    $parts->set('INVALID', $this->createMock(Part::class));
  }

  /**
   * @covers \Papaya\CMS\Administration\Page\Parts::getTarget
   * @dataProvider providePartsAndTargets
   * @param string $expected
   * @param string $partName
   */
  public function testGetTarget($expected, $partName) {
    $parts = new Parts($this->getPageFixture());
    $this->assertEquals($expected, $parts->getTarget($partName));
  }

  public static function providePartsAndTargets() {
    return array(
      array('leftcol', 'navigation'),
      array('centercol', 'content'),
      array('rightcol', 'information')
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Page\Parts::getTarget
   */
  public function testGetTargetWithInvalidNameExpectingException() {
    $parts = new Parts($this->getPageFixture());
    $this->expectException(\UnexpectedValueException::class);
    $parts->getTarget('INVALID');
  }

  /**
   * @covers \Papaya\CMS\Administration\Page\Parts::toolbar
   */
  public function testToolbarGetAfterSet() {
    $parts = new Parts($this->getPageFixture());
    $parts->toolbar(
      $toolbar = $this
        ->getMockBuilder(\Papaya\UI\Toolbar\Composed::class)
        ->disableOriginalConstructor()
        ->getMock()
    );
    $this->assertSame($toolbar, $parts->toolbar());
  }

  /**
   * @covers \Papaya\CMS\Administration\Page\Parts::toolbar
   */
  public function testToolbarGetImplicitCreate() {
    $parts = new Parts($this->getPageFixture());
    $this->assertInstanceOf(\Papaya\UI\Toolbar\Composed::class, $parts->toolbar());
  }

  /**
   * @covers \Papaya\CMS\Administration\Page\Parts::rewind
   * @covers \Papaya\CMS\Administration\Page\Parts::next
   * @covers \Papaya\CMS\Administration\Page\Parts::current
   * @covers \Papaya\CMS\Administration\Page\Parts::key
   * @covers \Papaya\CMS\Administration\Page\Parts::valid
   */
  public function testIteration() {
    $parts = new Parts($this->getPageFixture());
    $parts->papaya($this->mockPapaya()->application());
    $parts->content = $partOne = $this->getPartFixture();
    $parts->navigation = $partTwo = $this->getPartFixture();
    $this->assertEquals(
      array(
        'content' => $partOne,
        'navigation' => $partTwo,
        'information' => FALSE
      ),
      iterator_to_array($parts)
    );
  }

  /**
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\CMS\Administration\Page
   */
  private function getPageFixture() {
    return $this
      ->getMockBuilder(\Papaya\CMS\Administration\Page::class)
      ->disableOriginalConstructor()
      ->getMock();
  }

  private function getPartFixture() {
    $part = $this->createMock(Part::class);
    $part
      ->expects($this->at(0))
      ->method('parameters')
      ->with($this->isInstanceOf(\Papaya\Request\Parameters::class));
    $part
      ->expects($this->at(1))
      ->method('parameters')
      ->will($this->returnValue($this->createMock(\Papaya\Request\Parameters::class)));
    return $part;
  }
}
