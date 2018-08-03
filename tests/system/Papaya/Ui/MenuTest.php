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

class PapayaUiMenuTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Ui\Menu::appendTo
  */
  public function testAppendTo() {
    $document = new \Papaya\Xml\Document();
    $document->appendElement('sample');
    $menu = new \Papaya\Ui\Menu();
    $elements = $this
      ->getMockBuilder(\Papaya\Ui\Toolbar\Elements::class)
      ->setConstructorArgs(array($menu))
      ->getMock();
    $elements
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(1));
    $elements
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    $menu->elements($elements);
    $menu->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample><menu/></sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \Papaya\Ui\Menu::appendTo
  */
  public function testAppendToWithIdentifier() {
    $document = new \Papaya\Xml\Document();
    $document->appendElement('sample');
    $menu = new \Papaya\Ui\Menu();
    $elements = $this
      ->getMockBuilder(\Papaya\Ui\Toolbar\Elements::class)
      ->setConstructorArgs(array($menu))
      ->getMock();
    $elements
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(1));
    $elements
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    $menu->elements($elements);
    $menu->identifier = 'sample_id';
    $menu->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample><menu ident="sample_id"/></sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \Papaya\Ui\Menu::appendTo
  */
  public function testAppendToWithoutElements() {
    $document = new \Papaya\Xml\Document();
    $document->appendElement('sample');
    $menu = new \Papaya\Ui\Menu();
    $elements = $this
      ->getMockBuilder(\Papaya\Ui\Toolbar\Elements::class)
      ->setConstructorArgs(array($menu))
      ->getMock();
    $elements
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(0));
    $menu->elements($elements);
    $menu->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample/>',
      $document->saveXML($document->documentElement)
    );
  }
}
