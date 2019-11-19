<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Administration\UI\Navigation {

  use Papaya\Test\TestCase;
  use Papaya\UI;
  use Papaya\XML\Element as XMLElement;

  /**
   * @covers \Papaya\Administration\UI\Navigation\Main
   */
  class MainTest extends TestCase {

    public function testAppendMockedMenu() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|UI\Menu $menu */
      $menu = $this->createMock(UI\Menu::class);
      $menu
        ->expects($this->once())
        ->method('appendTo')
        ->willReturnCallback(
          static function(XMLElement $parent) {
            $parent->appendElement('menu');
          }
        );
      $navigation = new Main();
      $navigation->menu($menu);
      $this->assertXMLStringEqualsXMLString(
        '<menu/>',
        $navigation->getXML()
      );
    }

    public function testCreateMenuWithoutFavorites() {
      $user = $this->mockPapaya()->user(TRUE);
      $user
        ->method('hasPerm')
        ->willReturn(TRUE);
      $navigation = new Main();
      $navigation->papaya($this->mockPapaya()->application(['administrationUser' => $user]));
      $navigation->favorites(new \EmptyIterator());
      $this->assertInstanceOf(UI\Menu::class, $navigation->menu());
    }
  }

}
