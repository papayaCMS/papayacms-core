<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaAdministrationPageTest extends PapayaTestCase {

  /**
   * @covers PapayaAdministrationPage::__construct
   */
  public function testConstructor() {
    $page = new PapayaAdministrationPage_TestProxy(
      $layout = $this->getMock('PapayaTemplate')
    );
    $this->assertAttributeSame(
      $layout, '_layout', $page
    );
  }

  /**
   * @covers PapayaAdministrationPage
   */
  public function testPageWithoutParts() {
    $layout = $this->getMock('PapayaTemplate', array('add', 'addMenu', 'parse'));
    $layout
      ->expects($this->never())
      ->method('add');
    $layout
      ->expects($this->once())
      ->method('addMenu')
      ->with('');
    $page = new PapayaAdministrationPage_TestProxy($layout);
    $page->execute();
  }

  /**
   * @covers PapayaAdministrationPage
   */
  public function testPageWithContentPart() {
    $layout = $this->getMock('PapayaTemplate', array('add', 'addMenu', 'parse'));
    $layout
      ->expects($this->once())
      ->method('add')
      ->with('<foo/>', 'centercol');
    $layout
      ->expects($this->once())
      ->method('addMenu');
    $content = $this->getMock('PapayaAdministrationPagePart');
    $content
      ->expects($this->once())
      ->method('getXml')
      ->will($this->returnValue('<foo/>'));
    $page = new PapayaAdministrationPage_TestProxy($layout);
    $page->papaya($this->mockPapaya()->application());
    $page->parts()->content = $content;
    $page->execute();
  }

  /**
   * @covers PapayaAdministrationPage::createPart
   */
  public function testCreatePartWithUnknownNameExpectingFalse() {
    $page = new PapayaAdministrationPage_TestProxy($this->getMock('PapayaTemplate'));
    $this->assertFalse($page->createPart('NonExistingPart'));
  }

  /**
   * @covers PapayaAdministrationPage::parts
   */
  public function testPartsGetAfterSet() {
    $parts = $this
      ->getMockBuilder('PapayaAdministrationPageParts')
      ->disableOriginalConstructor()
      ->getMock();
    $page = new PapayaAdministrationPage_TestProxy($this->getMock('PapayaTemplate'));
    $page->parts($parts);
    $this->assertSame($parts, $page->parts());
  }

  /**
   * @covers PapayaAdministrationPage::toolbar
   */
  public function testToolbarGetAfterSet() {
    $page = new PapayaAdministrationPage_TestProxy($this->getMock('PapayaTemplate'));
    $page->toolbar($toolbar = $this->getMock('PapayaUiToolbar'));
    $this->assertSame($toolbar, $page->toolbar());
  }

  /**
   * @covers PapayaAdministrationPage::toolbar
   */
  public function testToolbarGetImplicitCreate() {
    $page = new PapayaAdministrationPage_TestProxy($this->getMock('PapayaTemplate'));
    $this->assertInstanceOf('PapayaUiToolbar', $page->toolbar());
  }
}

class PapayaAdministrationPage_TestProxy extends PapayaAdministrationPage {

}
