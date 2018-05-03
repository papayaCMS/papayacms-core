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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaAdministrationPageTest extends PapayaTestCase {

  /**
   * @covers PapayaAdministrationPage::__construct
   */
  public function testConstructor() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $layout */
    $layout = $this->createMock(PapayaTemplate::class);
    $page = new PapayaAdministrationPage_TestProxy($layout);
    $this->assertAttributeSame(
      $layout, '_layout', $page
    );
  }

  /**
   * @covers PapayaAdministrationPage
   */
  public function testPageWithoutParts() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $layout */
    $layout = $this
      ->getMockBuilder(PapayaTemplate::class)
      ->setMethods(array('add', 'addMenu', 'parse'))
      ->getMock();
    $layout
      ->expects($this->never())
      ->method('add');
    $layout
      ->expects($this->once())
      ->method('addMenu')
      ->with('');
    $page = new PapayaAdministrationPage_TestProxy($layout);
    $page->papaya($this->mockPapaya()->application());
    $page->execute();
  }

  /**
   * @covers PapayaAdministrationPage
   */
  public function testPageWithContentPart() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $layout */
    $layout = $this
      ->getMockBuilder(PapayaTemplate::class)
      ->setMethods(array('add', 'addMenu', 'parse'))
      ->getMock();
    $layout
      ->expects($this->once())
      ->method('add')
      ->with(
      /** @lang XML */'<foo/>', 'centercol');
    $layout
      ->expects($this->once())
      ->method('addMenu');
    $content = $this->createMock(PapayaAdministrationPagePart::class);
    $content
      ->expects($this->once())
      ->method('getXml')
      ->willReturn(/** @lang XML */'<foo/>');
    $page = new PapayaAdministrationPage_TestProxy($layout);
    $page->papaya($this->mockPapaya()->application());
    $page->parts()->content = $content;
    $page->execute();
  }

  /**
   * @covers PapayaAdministrationPage::createPart
   */
  public function testCreatePartWithUnknownNameExpectingFalse() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $layout */
    $layout = $this->createMock(PapayaTemplate::class);
    $page = new PapayaAdministrationPage_TestProxy($layout);
    $this->assertFalse($page->createPart('NonExistingPart'));
  }

  /**
   * @covers PapayaAdministrationPage::parts
   */
  public function testPartsGetAfterSet() {
    $parts = $this
      ->getMockBuilder(PapayaAdministrationPageParts::class)
      ->disableOriginalConstructor()
      ->getMock();
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $layout */
    $layout = $this->createMock(PapayaTemplate::class);
    $page = new PapayaAdministrationPage_TestProxy($layout);
    $page->parts($parts);
    $this->assertSame($parts, $page->parts());
  }

  /**
   * @covers PapayaAdministrationPage::toolbar
   */
  public function testToolbarGetAfterSet() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $layout */
    $layout = $this->createMock(PapayaTemplate::class);
    $page = new PapayaAdministrationPage_TestProxy($layout);
    $page->toolbar($toolbar = $this->createMock(PapayaUiToolbar::class));
    $this->assertSame($toolbar, $page->toolbar());
  }

  /**
   * @covers PapayaAdministrationPage::toolbar
   */
  public function testToolbarGetImplicitCreate() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $layout */
    $layout = $this->createMock(PapayaTemplate::class);
    $page = new PapayaAdministrationPage_TestProxy($layout);
    $this->assertInstanceOf(PapayaUiToolbar::class, $page->toolbar());
  }
}

class PapayaAdministrationPage_TestProxy extends PapayaAdministrationPage {

}
