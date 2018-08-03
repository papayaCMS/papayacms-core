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

class PapayaUiToolbarGroupTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Ui\Toolbar\Group::__construct
  */
  public function testConstructor() {
    $group = new \Papaya\Ui\Toolbar\Group('group caption');
    $this->assertEquals(
      'group caption', $group->caption
    );
  }

  /**
  * @covers \Papaya\Ui\Toolbar\Group::appendTo
  */
  public function testAppendTo() {
    $document = new \Papaya\Xml\Document();
    $document->appendElement('sample');
    $group = new \Papaya\Ui\Toolbar\Group('group caption');
    $elements = $this
      ->getMockBuilder(\Papaya\Ui\Toolbar\Elements::class)
      ->setConstructorArgs(array($group))
      ->getMock();
    $elements
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(1));
    $elements
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    $group->elements($elements);
    $this->assertInstanceOf(\Papaya\Xml\Element::class, $group->appendTo($document->documentElement));
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<group title="group caption"/>',
      $document->documentElement->saveFragment()
    );
  }

  /**
  * @covers \Papaya\Ui\Toolbar\Group::appendTo
  */
  public function testAppendToWithoutElements() {
    $document = new \Papaya\Xml\Document();
    $document->appendElement('sample');
    $group = new \Papaya\Ui\Toolbar\Group('group caption');
    $elements = $this
      ->getMockBuilder(\Papaya\Ui\Toolbar\Elements::class)
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
