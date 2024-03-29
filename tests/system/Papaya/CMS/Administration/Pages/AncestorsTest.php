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

namespace Papaya\CMS\Administration\Pages;

require_once __DIR__.'/../../../../../bootstrap.php';

class AncestorsTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\CMS\Administration\Pages\Ancestors::appendTo
   */
  public function testAppendTo() {
    $document = new \Papaya\XML\Document();
    $document->appendElement('sample');

    $menu = $this->createMock(\Papaya\UI\Hierarchy\Menu::class);
    $menu
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class))
      ->will($this->returnValue($document->documentElement->appendElement('menu')));
    $ancestors = new Ancestors();
    $ancestors->menu($menu);

    $this->assertInstanceOf(\Papaya\XML\Element::class, $ancestors->appendTo($document->documentElement));
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Ancestors::setIds
   */
  public function testSetIds() {
    $pages = $this->createMock(\Papaya\CMS\Content\Pages::class);
    $pages
      ->expects($this->once())
      ->method('load')
      ->with(array('id' => array(42), 'language_id' => 1));
    $pages
      ->expects($this->any())
      ->method('offsetExists')
      ->will(
        $this->onConsecutiveCalls(TRUE, FALSE)
      );
    $pages
      ->expects($this->once())
      ->method('offsetGet')
      ->with(42)
      ->will(
        $this->returnValue(
          array('title' => 'test')
        )
      );

    $ancestors = new Ancestors();
    $ancestors->papaya(
      $this->mockPapaya()->application(
        array(
          'AdministrationLanguage' => $this->getLanguageSwitchFixture()
        )
      )
    );
    $ancestors->pages($pages);

    $ancestors->setIds(array(42));
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<hierarchy-menu>
        <items>
          <item caption="test" mode="both"
           href="http://www.test.tld/test.html?tt[page_id]=42"/>
        </items>
      </hierarchy-menu>',
      $ancestors->getXML()
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Ancestors::pages
   */
  public function testPagesGetAfterSet() {
    $ancestors = new Ancestors();
    $pages = $this->createMock(\Papaya\CMS\Content\Pages::class);
    $this->assertSame(
      $pages, $ancestors->pages($pages)
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Ancestors::pages
   */
  public function testPagesGetWithImpliciteCreate() {
    $ancestors = new Ancestors();
    $ancestors->papaya($papaya = $this->mockPapaya()->application());
    $this->assertInstanceOf(
      \Papaya\CMS\Content\Pages::class, $ancestors->pages()
    );
    $this->assertSame(
      $papaya, $ancestors->papaya()
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Ancestors::menu
   */
  public function testItemsGetAfterSet() {
    $ancestors = new Ancestors();
    $menu = $this->createMock(\Papaya\UI\Hierarchy\Menu::class);
    $this->assertSame(
      $menu, $ancestors->menu($menu)
    );
  }

  /**
   * @covers \Papaya\CMS\Administration\Pages\Ancestors::menu
   */
  public function testItemsGetWithImpliciteCreate() {
    $ancestors = new Ancestors();
    $ancestors->papaya($papaya = $this->mockPapaya()->application());
    $this->assertInstanceOf(
      \Papaya\UI\Hierarchy\Menu::class, $ancestors->menu()
    );
    $this->assertSame(
      $papaya, $ancestors->papaya()
    );
  }

  /************************************
   * Fixtures
   ************************************/

  private function getLanguageSwitchFixture() {
    $language = new \stdClass();
    $language->id = 1;
    $switch = $this->createMock(\Papaya\CMS\Administration\Languages\Selector::class);
    $switch
      ->expects($this->any())
      ->method('getCurrent')
      ->will($this->returnValue($language));
    return $switch;
  }
}
