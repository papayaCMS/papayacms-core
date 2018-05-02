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

class PapayaUiMenuTest extends PapayaTestCase {

  /**
  * @covers PapayaUiMenu::appendTo
  */
  public function testAppendTo() {
    $document = new PapayaXmlDocument();
    $document->appendElement('sample');
    $menu = new PapayaUiMenu();
    $elements = $this
      ->getMockBuilder(PapayaUiToolbarElements::class)
      ->setConstructorArgs(array($menu))
      ->getMock();
    $elements
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(1));
    $elements
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(PapayaXmlElement::class));
    $menu->elements($elements);
    $menu->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample><menu/></sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers PapayaUiMenu::appendTo
  */
  public function testAppendToWithIdentifier() {
    $document = new PapayaXmlDocument();
    $document->appendElement('sample');
    $menu = new PapayaUiMenu();
    $elements = $this
      ->getMockBuilder(PapayaUiToolbarElements::class)
      ->setConstructorArgs(array($menu))
      ->getMock();
    $elements
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(1));
    $elements
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(PapayaXmlElement::class));
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
  * @covers PapayaUiMenu::appendTo
  */
  public function testAppendToWithoutElements() {
    $document = new PapayaXmlDocument();
    $document->appendElement('sample');
    $menu = new PapayaUiMenu();
    $elements = $this
      ->getMockBuilder(PapayaUiToolbarElements::class)
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
