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

use Papaya\Administration\Page\Parts;
use Papaya\Administration\Page\Part;
use Papaya\Administration\PapayaAdministrationPage;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaAdministrationPagePartsTest extends PapayaTestCase {

  /**
   * @covers Parts::__construct
   */
  public function testConstructor() {
    $parts = new Parts(
      $page = $this->getPageFixture()
    );
    $this->assertAttributeEquals(
      $page, '_page', $parts
    );
  }

  /**
   * @covers Parts::__get
   * @covers Parts::__set
   */
  public function testOffsetGetAfterOffsetSet() {
    $parts = new Parts(
      $page = $this->getPageFixture()
    );
    $parts->content = $part = $this->createMock(Part::class);
    $this->assertSame($part, $parts->content);
  }

  /**
   * @covers Parts::get
   * @covers Parts::set
   */
  public function testGetAfterSet() {
    $parts = new Parts(
      $page = $this->getPageFixture()
    );
    $parts->set('content', $part = $this->createMock(Part::class));
    $this->assertSame($part, $parts->get('content'));
  }

  /**
   * @covers Parts::get
   * @covers Parts::create
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
   * @covers Parts::get
   * @covers Parts::create
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
   * @covers Parts::get
   * @covers Parts::create
   */
  public function testGetWithInvalidNameExpectingException() {
    $parts = new Parts($this->getPageFixture());
    $this->expectException(\UnexpectedValueException::class);
    $parts->get('INVALID');
  }

  /**
   * @covers Parts::set
   */
  public function testSetWithInvalidNameExpectingException() {
    $parts = new Parts($this->getPageFixture());
    $this->expectException(\UnexpectedValueException::class);
    $parts->set('INVALID', $this->createMock(Part::class));
  }

  /**
   * @covers Parts::getTarget
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
   * @covers Parts::getTarget
   */
  public function testGetTargetWithInvalidNameExpectingException() {
    $parts = new Parts($this->getPageFixture());
    $this->expectException(\UnexpectedValueException::class);
    $parts->getTarget('INVALID');
  }

  /**
   * @covers Parts::toolbar
   */
  public function testToolbarGetAfterSet() {
    $parts = new Parts($this->getPageFixture());
    $parts->toolbar(
      $toolbar = $this
        ->getMockBuilder(PapayaUiToolbarComposed::class)
        ->disableOriginalConstructor()
        ->getMock()
    );
    $this->assertSame($toolbar, $parts->toolbar());
  }

  /**
   * @covers Parts::toolbar
   */
  public function testToolbarGetImplicitCreate() {
    $parts = new Parts($this->getPageFixture());
    $this->assertInstanceOf(PapayaUiToolbarComposed::class, $parts->toolbar());
  }

  /**
   * @covers Parts::rewind
   * @covers Parts::next
   * @covers Parts::current
   * @covers Parts::key
   * @covers Parts::valid
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
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\Administration\Page
   */
  private function getPageFixture() {
    return $this
      ->getMockBuilder(Papaya\Administration\Page::class)
      ->disableOriginalConstructor()
      ->getMock();
  }

  private function getPartFixture() {
    $part = $this->createMock(Part::class);
    $part
      ->expects($this->at(0))
      ->method('parameters')
      ->with($this->isInstanceOf(PapayaRequestParameters::class));
    $part
      ->expects($this->at(1))
      ->method('parameters')
      ->will($this->returnValue($this->createMock(PapayaRequestParameters::class)));
    return $part;
  }
}
