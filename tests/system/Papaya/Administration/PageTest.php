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
    $page = new Page_TestProxy($ui = $this->mockPapaya()->administrationUI());
    $this->assertSame($ui, $page->getUI());
  }

  /**
   * @covers \Papaya\Administration\Page
   */
  public function testPageWithoutParts() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Administration\UI $ui */
    $ui =  $this->mockPapaya()->administrationUI();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Template $template */
    $template = $ui->template();
    $template
      ->expects($this->never())
      ->method('add');
    $template
      ->expects($this->once())
      ->method('addMenu')
      ->with('');
    $page = new Page_TestProxy($ui);
    $page->papaya($this->mockPapaya()->application());
    $page->execute();
  }

  /**
   * @covers \Papaya\Administration\Page
   */
  public function testPageWithContentPart() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Administration\UI $ui */
    $ui =  $this->mockPapaya()->administrationUI();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Template $template */
    $template = $ui->template();
    $template
      ->expects($this->once())
      ->method('add')
      ->with(
      /** @lang XML */
        '<foo/>', 'centercol');
    $template
      ->expects($this->once())
      ->method('addMenu');
    $content = $this->createMock(Page\Part::class);
    $content
      ->expects($this->once())
      ->method('getXml')
      ->willReturn(/** @lang XML */
        '<foo/>');
    $page = new Page_TestProxy($ui);
    $page->papaya($this->mockPapaya()->application());
    $page->parts()->content = $content;
    $page->execute();
  }

  /**
   * @covers \Papaya\Administration\Page::createPart
   */
  public function testCreatePartWithUnknownNameExpectingFalse() {
    $page = new Page_TestProxy($this->mockPapaya()->administrationUI());
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
    $page = new Page_TestProxy($this->mockPapaya()->administrationUI());
    $page->parts($parts);
    $this->assertSame($parts, $page->parts());
  }

  /**
   * @covers \Papaya\Administration\Page::toolbar
   */
  public function testToolbarGetAfterSet() {
    $page = new Page_TestProxy($this->mockPapaya()->administrationUI());
    $page->toolbar($toolbar = $this->createMock(\Papaya\UI\Toolbar::class));
    $this->assertSame($toolbar, $page->toolbar());
  }

  /**
   * @covers \Papaya\Administration\Page::toolbar
   */
  public function testToolbarGetImplicitCreate() {
    $page = new Page_TestProxy($this->mockPapaya()->administrationUI());
    $this->assertInstanceOf(\Papaya\UI\Toolbar::class, $page->toolbar());
  }
}

class Page_TestProxy extends Page {

}
