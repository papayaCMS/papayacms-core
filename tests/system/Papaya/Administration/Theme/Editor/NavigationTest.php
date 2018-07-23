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

use Papaya\Administration\Theme\Editor\Navigation;

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaAdministrationThemeEditorNavigationTest extends PapayaTestCase {

  /**
   * @covers Navigation::appendTo
   */
  public function testAppendTo() {
    $listview = $this->createMock(PapayaUiListview::class);
    $listview
      ->expects($this->once())
      ->method('appendTo');
    $navigation = new Navigation();
    $navigation->papaya($this->mockPapaya()->application());
    $navigation->listview($listview);
    $navigation->getXml();
  }

  /**
   * @covers Navigation::appendTo
   */
  public function testToolbarButtonsWithSelectedTheme() {
    $navigation = new Navigation();
    $navigation->papaya($this->mockPapaya()->application());
    $navigation->listview($this->createMock(PapayaUiListview::class));
    $navigation->parameters(new PapayaRequestParameters(array('theme' => 'default')));
    $navigation->getXml();
    $this->assertXmlFragmentEqualsXmlFragment(
       /* language=xml prefix=<fragment> suffix=</fragment> */
      '<button
         href="http://www.test.tld/test.html?cmd=set_edit&amp;set_id=0&amp;theme=default"
         target="_self"
         title="Add set"/>
      <button
         href="http://www.test.tld/test.html?cmd=set_import&amp;set_id=0&amp;theme=default"
         target="_self"
         title="Import"/>',
      $navigation->toolbar()->getXml()
    );
  }

  /**
   * @covers Navigation::appendTo
   */
  public function testToolbarButtonsWithSelectedSet() {
    $navigation = new Navigation();
    $navigation->papaya($this->mockPapaya()->application());
    $navigation->listview($this->createMock(PapayaUiListview::class));
    $navigation->parameters(
      new PapayaRequestParameters(array('theme' => 'default', 'set_id' => 42))
    );
    $navigation->getXml();
    $this->assertXmlFragmentEqualsXmlFragment(
       /* language=xml prefix=<fragment> suffix=</fragment> */
      '<button
         href="http://www.test.tld/test.html?cmd=set_edit&amp;set_id=0&amp;theme=default"
         target="_self"
         title="Add set"/>
      <button
         href="http://www.test.tld/test.html?cmd=set_delete&amp;set_id=42&amp;theme=default"
         target="_self"
         title="Delete set"/>
      <button href="http://www.test.tld/test.html?cmd=set_import&amp;set_id=42&amp;theme=default"
         target="_self"
         title="Import"/>
      <button href="http://www.test.tld/test.html?cmd=set_export&amp;set_id=42&amp;theme=default"
         target="_self"
         title="Export"/>',
      $navigation->toolbar()->getXml()
    );
  }

  /**
   * @covers Navigation::listview
   */
  public function testListviewGetAfterSet() {
    $navigation = new Navigation();
    $navigation->listview($listview = $this->createMock(PapayaUiListview::class));
    $this->assertSame(
      $listview, $navigation->listview()
    );
  }

  /**
   * @covers Navigation::listview
   * @covers Navigation::createThemeList
   */
  public function testListviewImplicitCreate() {
    $navigation = new Navigation();
    $navigation->parameters(new PapayaRequestParameters());
    $this->assertInstanceOf(
      PapayaUiListview::class, $navigation->listview()
    );
  }

  /**
   * @covers Navigation::listview
   * @covers Navigation::createThemeList
   */
  public function testListviewImplicitCreateWithSelectedTheme() {
    $navigation = new Navigation();
    $navigation->parameters(new PapayaRequestParameters(array('theme' => 'default')));
    $this->assertInstanceOf(
      PapayaUiListview::class, $navigation->listview()
    );
  }

  /**
   * @covers Navigation::listview
   * @covers Navigation::createThemeList
   */
  public function testListviewImplicitCreateWithSelectedSet() {
    $navigation = new Navigation();
    $navigation->parameters(
      new PapayaRequestParameters(array('theme' => 'default', 'set_id' => 23))
    );
    $this->assertInstanceOf(
      PapayaUiListview::class, $navigation->listview()
    );
  }

  /**
   * @covers Navigation::callbackCreateItem
   */
  public function testCallbackCreateItemForInvalidElement() {
    $navigation = new Navigation();
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaUiListviewItems $items */
    $items = $this
      ->getMockBuilder(PapayaUiListviewItems::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->assertNull(
      $navigation->callbackCreateItem($this->getBuilderFixture(99), $items, 'sample', 0)
    );
  }

  /**
   * @covers Navigation::callbackCreateItem
   * @covers Navigation::createThemeItem
   */
  public function testCallbackCreateItemForTheme() {
    $papaya = $this->mockPapaya()->application(
      array('images' => array('items-theme' => 'theme.png'))
    );
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaUiListviewItems $items */
    $items = $this
      ->getMockBuilder(PapayaUiListviewItems::class)
      ->disableOriginalConstructor()
      ->getMock();
    $items
      ->expects($this->once())
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf(PapayaUiListviewItem::class));
    $navigation = new Navigation();
    $navigation->papaya($papaya);
    $item = $navigation->callbackCreateItem($this->getBuilderFixture(), $items, 'sample', 0);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<listitem
         title="sample"
         image="theme.png"
         href="http://www.test.tld/test.html?cmd=theme_show&amp;theme=sample"/>',
      $item->getXml()
    );
  }

  /**
   * @covers Navigation::callbackCreateItem
   * @covers Navigation::createThemeItem
   */
  public function testCallbackCreateItemForSelectedTheme() {
    $papaya = $this->mockPapaya()->application(
      array(
        'request' => $this->mockPapaya()->request(array('theme' => 'sample')),
        'images' => array('items-theme' => 'theme.png')
      )
    );
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaUiListviewItems $items */
    $items = $this
      ->getMockBuilder(PapayaUiListviewItems::class)
      ->disableOriginalConstructor()
      ->getMock();
    $items
      ->expects($this->once())
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf(PapayaUiListviewItem::class));
    $navigation = new Navigation();
    $navigation->papaya($papaya);
    $item = $navigation->callbackCreateItem($this->getBuilderFixture(), $items, 'sample', 0);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<listitem
         title="sample"
         image="theme.png"
         href="http://www.test.tld/test.html?cmd=theme_show&amp;theme=sample"
         selected="selected"/>',
      $item->getXml()
    );
  }

  /**
   * @covers Navigation::callbackCreateItem
   * @covers Navigation::createThemeItem
   */
  public function testCallbackCreateItemForSelectedThemeAndSelectedSet() {
    $papaya = $this->mockPapaya()->application(
      array(
        'request' => $this->mockPapaya()->request(array('theme' => 'sample', 'set_id' => 23)),
        'images' => array('items-theme' => 'theme.png')
      )
    );
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaUiListviewItems $items */
    $items = $this
      ->getMockBuilder(PapayaUiListviewItems::class)
      ->disableOriginalConstructor()
      ->getMock();
    $items
      ->expects($this->once())
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf(PapayaUiListviewItem::class));
    $navigation = new Navigation();
    $navigation->papaya($papaya);
    $item = $navigation->callbackCreateItem($this->getBuilderFixture(), $items, 'sample', 0);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<listitem
         title="sample"
         image="theme.png"
         href="http://www.test.tld/test.html?cmd=theme_show&amp;theme=sample"/>',
      $item->getXml()
    );
  }

  /**
   * @covers Navigation::callbackCreateItem
   * @covers Navigation::createSetItem
   */
  public function testCallbackCreateItemForSet() {
    $papaya = $this->mockPapaya()->application(
      array(
        'images' => array('items-folder' => 'folder.png')
      )
    );
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaUiListviewItems $items */
    $items = $this
      ->getMockBuilder(PapayaUiListviewItems::class)
      ->disableOriginalConstructor()
      ->getMock();
    $items
      ->expects($this->once())
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf(PapayaUiListviewItem::class));
    $navigation = new Navigation();
    $navigation->papaya($papaya);
    $item = $navigation->callbackCreateItem(
      $this->getBuilderFixture(1),
      $items,
      array('id' => 23, 'title' => 'sample title', 'theme' => 'sample'),
      0
    );
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<listitem
         title="sample title"
         image="folder.png"
         href="http://www.test.tld/test.html?cmd=set_edit&amp;set_id=23&amp;theme=sample"
         indent="1"/>',
      $item->getXml()
    );
  }

  /**
   * @covers Navigation::callbackCreateItem
   * @covers Navigation::createSetItem
   */
  public function testCallbackCreateItemForSelectedSet() {
    $papaya = $this->mockPapaya()->application(
      array(
        'request' => $this->mockPapaya()->request(array('theme' => 'sample', 'set_id' => 23)),
        'images' => array('items-folder' => 'folder.png')
      )
    );
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaUiListviewItems $items */
    $items = $this
      ->getMockBuilder(PapayaUiListviewItems::class)
      ->disableOriginalConstructor()
      ->getMock();
    $items
      ->expects($this->once())
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf(PapayaUiListviewItem::class));
    $navigation = new Navigation();
    $navigation->papaya($papaya);
    $item = $navigation->callbackCreateItem(
      $this->getBuilderFixture(1),
      $items,
      array('id' => 23, 'title' => 'sample title', 'theme' => 'sample'),
      0
    );
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<listitem
         title="sample title"
         image="folder.png"
         href="http://www.test.tld/test.html?cmd=set_edit&amp;set_id=23&amp;theme=sample"
         indent="1"
         selected="selected"/>',
      $item->getXml()
    );
  }

  /**
   * @covers Navigation::callbackCreateItem
   * @covers Navigation::createPageItem
   */
  public function testCallbackPageItemForPage() {
    $papaya = $this->mockPapaya()->application(
      array(
        'request' => $this->mockPapaya()->request(array('theme' => 'sample', 'set_id' => 23)),
        'images' => array('items-folder' => 'folder.png')
      )
    );
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaUiListviewItems $items */
    $items = $this
      ->getMockBuilder(PapayaUiListviewItems::class)
      ->disableOriginalConstructor()
      ->getMock();
    $items
      ->expects($this->once())
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf(PapayaUiListviewItem::class));

    $page = new PapayaContentStructurePage();
    $page->title = 'Page title';
    $page->name = 'SAMPLE_PAGE';

    $navigation = new Navigation();
    $navigation->papaya($papaya);
    $item = $navigation->callbackCreateItem(
      $this->getBuilderFixture(2),
      $items,
      $page,
      0
    );
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<listitem
         title="Page title"
         image="folder.png"
         href="http://www.test.tld/test.html?cmd=values_edit&amp;page_identifier=SAMPLE_PAGE&amp;set_id=23&amp;theme=sample"
         indent="2"/>',
      $item->getXml()
    );
  }

  /**
   * @covers Navigation::callbackCreateItem
   * @covers Navigation::createPageItem
   */
  public function testCallbackPageItemForSelectedPage() {
    $papaya = $this->mockPapaya()->application(
      array(
        'request' => $this->mockPapaya()->request(
          array('theme' => 'sample', 'set_id' => 23, 'page_identifier' => 'SAMPLE_PAGE')
         ),
        'images' => array('items-folder' => 'folder.png')
      )
    );
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaUiListviewItems $items */
    $items = $this
      ->getMockBuilder(PapayaUiListviewItems::class)
      ->disableOriginalConstructor()
      ->getMock();
    $items
      ->expects($this->once())
      ->method('offsetSet')
      ->with(NULL, $this->isInstanceOf(PapayaUiListviewItem::class));

    $page = new PapayaContentStructurePage();
    $page->title = 'Page title';
    $page->name = 'SAMPLE_PAGE';

    $navigation = new Navigation();
    $navigation->papaya($papaya);
    $item = $navigation->callbackCreateItem(
      $this->getBuilderFixture(2),
      $items,
      $page,
      0
    );
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<listitem
         title="Page title"
         image="folder.png"
         href="http://www.test.tld/test.html?cmd=values_edit&amp;page_identifier=SAMPLE_PAGE&amp;set_id=23&amp;theme=sample"
         indent="2"
         selected="selected"/>',
      $item->getXml()
    );
  }

  /**
   * @param int $depth
   * @return PHPUnit_Framework_MockObject_MockObject|PapayaUiListviewItemsBuilder
   */
  private function getBuilderFixture($depth = 0) {
    $iterator = $this
      ->getMockBuilder(RecursiveIteratorIterator::class)
      ->setConstructorArgs(array($this->createMock(RecursiveIterator::class)))
      ->getMock();
    $iterator
      ->expects($this->once())
      ->method('getDepth')
      ->will($this->returnValue($depth));
    $builder = $this
      ->getMockBuilder(PapayaUiListviewItemsBuilder::class)
      ->disableOriginalConstructor()
      ->getMock();
    $builder
     ->expects($this->once())
     ->method('getDataSource')
     ->will($this->returnValue($iterator));
    return $builder;
  }
}
