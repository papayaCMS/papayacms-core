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

namespace Papaya\UI;
require_once __DIR__.'/../../../bootstrap.php';

class ToolbarTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\UI\Toolbar::elements
   */
  public function testElementsGetAfterSet() {
    $menu = new Toolbar();
    $elements = $this
      ->getMockBuilder(Toolbar\Elements::class)
      ->setConstructorArgs(array($menu))
      ->getMock();
    $elements
      ->expects($this->once())
      ->method('owner')
      ->with($this->isInstanceOf(Toolbar::class));
    $this->assertSame(
      $elements, $menu->elements($elements)
    );
  }

  /**
   * @covers \Papaya\UI\Toolbar::elements
   */
  public function testElementsImplicitCreate() {
    $menu = new Toolbar();
    $this->assertInstanceOf(
      Toolbar\Elements::class, $menu->elements()
    );
    $this->assertSame(
      $menu, $menu->elements()->owner()
    );
  }

  /**
   * @covers \Papaya\UI\Toolbar::appendTo
   */
  public function testAppendTo() {
    $document = new \Papaya\XML\Document();
    $parent = $document->appendElement('sample');
    $menu = new Toolbar();
    $elements = $this
      ->getMockBuilder(Toolbar\Elements::class)
      ->setConstructorArgs(array($menu))
      ->getMock();
    $elements
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(1));
    $elements
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));
    $menu->elements($elements);
    $menu->appendTo($parent);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<sample><toolbar/></sample>',
      $document->saveXML($parent)
    );
  }

  /**
   * @covers \Papaya\UI\Toolbar::appendTo
   */
  public function testAppendToWithoutElements() {
    $document = new \Papaya\XML\Document();
    $parent = $document->appendElement('sample');
    $menu = new Toolbar();
    $elements = $this
      ->getMockBuilder(Toolbar\Elements::class)
      ->setConstructorArgs(array($menu))
      ->getMock();
    $elements
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(0));
    $menu->elements($elements);
    $menu->appendTo($parent);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<sample/>',
      $document->saveXML($parent)
    );
  }
}
