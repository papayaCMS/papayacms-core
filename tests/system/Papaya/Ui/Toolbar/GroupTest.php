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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiToolbarGroupTest extends PapayaTestCase {

  /**
  * @covers PapayaUiToolbarGroup::__construct
  */
  public function testConstructor() {
    $group = new PapayaUiToolbarGroup('group caption');
    $this->assertEquals(
      'group caption', $group->caption
    );
  }

  /**
  * @covers PapayaUiToolbarGroup::appendTo
  */
  public function testAppendTo() {
    $document = new PapayaXmlDocument();
    $document->appendElement('sample');
    $group = new PapayaUiToolbarGroup('group caption');
    $elements = $this
      ->getMockBuilder(PapayaUiToolbarElements::class)
      ->setConstructorArgs(array($group))
      ->getMock();
    $elements
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(1));
    $elements
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(PapayaXmlElement::class));
    $group->elements($elements);
    $this->assertInstanceOf(PapayaXmlElement::class, $group->appendTo($document->documentElement));
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<group title="group caption"/>',
      $document->documentElement->saveFragment()
    );
  }

  /**
  * @covers PapayaUiToolbarGroup::appendTo
  */
  public function testAppendToWithoutElements() {
    $document = new PapayaXmlDocument();
    $document->appendElement('sample');
    $group = new PapayaUiToolbarGroup('group caption');
    $elements = $this
      ->getMockBuilder(PapayaUiToolbarElements::class)
      ->setConstructorArgs(array($group))
      ->getMock();
    $elements
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(0));
    $group->elements($elements);
    $this->assertNull($group->appendTo($document->documentElement));
    $this->assertXmlStringEqualsXmlString(
       /** @lang XML */
      '<sample/>',
      $document->documentElement->saveXml()
    );
  }
}
