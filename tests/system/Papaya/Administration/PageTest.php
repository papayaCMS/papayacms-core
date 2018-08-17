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

namespace Papaya\Administration;

require_once __DIR__.'/../../../bootstrap.php';

class PageTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Administration\Page::__construct
   */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Template $layout */
    $layout = $this->createMock(\Papaya\Template::class);
    $page = new Page_TestProxy($layout);
    $this->assertAttributeSame(
      $layout, '_layout', $page
    );
  }

  /**
   * @covers \Papaya\Administration\Page
   */
  public function testPageWithoutParts() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Template $layout */
    $layout = $this
      ->getMockBuilder(\Papaya\Template::class)
      ->setMethods(array('add', 'addMenu', 'parse'))
      ->getMock();
    $layout
      ->expects($this->never())
      ->method('add');
    $layout
      ->expects($this->once())
      ->method('addMenu')
      ->with('');
    $page = new Page_TestProxy($layout);
    $page->papaya($this->mockPapaya()->application());
    $page->execute();
  }

  /**
   * @covers \Papaya\Administration\Page
   */
  public function testPageWithContentPart() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Template $layout */
    $layout = $this
      ->getMockBuilder(\Papaya\Template::class)
      ->setMethods(array('add', 'addMenu', 'parse'))
      ->getMock();
    $layout
      ->expects($this->once())
      ->method('add')
      ->with(
      /** @lang XML */
        '<foo/>', 'centercol');
    $layout
      ->expects($this->once())
      ->method('addMenu');
    $content = $this->createMock(Page\Part::class);
    $content
      ->expects($this->once())
      ->method('getXml')
      ->willReturn(/** @lang XML */
        '<foo/>');
    $page = new Page_TestProxy($layout);
    $page->papaya($this->mockPapaya()->application());
    $page->parts()->content = $content;
    $page->execute();
  }

  /**
   * @covers \Papaya\Administration\Page::createPart
   */
  public function testCreatePartWithUnknownNameExpectingFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Template $layout */
    $layout = $this->createMock(\Papaya\Template::class);
    $page = new Page_TestProxy($layout);
    $this->assertFalse($page->createPart('NonExistingPart'));
  }

  /**
   * @covers \Papaya\Administration\Page::parts
   */
  public function testPartsGetAfterSet() {
    $parts = $this
      ->getMockBuilder(Page\Parts::class)
      ->disableOriginalConstructor()
      ->getMock();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Template $layout */
    $layout = $this->createMock(\Papaya\Template::class);
    $page = new Page_TestProxy($layout);
    $page->parts($parts);
    $this->assertSame($parts, $page->parts());
  }

  /**
   * @covers \Papaya\Administration\Page::toolbar
   */
  public function testToolbarGetAfterSet() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Template $layout */
    $layout = $this->createMock(\Papaya\Template::class);
    $page = new Page_TestProxy($layout);
    $page->toolbar($toolbar = $this->createMock(\Papaya\UI\Toolbar::class));
    $this->assertSame($toolbar, $page->toolbar());
  }

  /**
   * @covers \Papaya\Administration\Page::toolbar
   */
  public function testToolbarGetImplicitCreate() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Template $layout */
    $layout = $this->createMock(\Papaya\Template::class);
    $page = new Page_TestProxy($layout);
    $this->assertInstanceOf(\Papaya\UI\Toolbar::class, $page->toolbar());
  }
}

class Page_TestProxy extends Page {

}
