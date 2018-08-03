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

require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiListviewSubitemImageSelectTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Ui\Listview\Subitem\Image\Toggle::__construct
  * @covers \Papaya\Ui\Listview\Subitem\Image\Toggle::setIcons
  */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Ui\Icon\Collection $icons */
    $icons = $this->createMock(\Papaya\Ui\Icon\Collection::class);
    $subitem = new \Papaya\Ui\Listview\Subitem\Image\Toggle($icons, 'foo');
    $this->assertSame($icons, $subitem->icons);
    $this->assertEquals('foo', $subitem->selection);
  }

  /**
  * @covers \Papaya\Ui\Listview\Subitem\Image\Toggle::appendTo
  */
  public function testAppendToWithIcon() {
    $icon = $this
      ->getMockBuilder(\PapayaUiIcon::class)
      ->disableOriginalConstructor()
      ->getMock();
    $icon
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Ui\Icon\Collection $icons */
    $icons = $this->createMock(\Papaya\Ui\Icon\Collection::class);
    $icons
      ->expects($this->once())
      ->method('offsetExists')
      ->with('foo')
      ->will($this->returnValue(TRUE));
    $icons
      ->expects($this->once())
      ->method('offsetGet')
      ->with('foo')
      ->will($this->returnValue($icon));

    $document = new \Papaya\Xml\Document();
    $subitem = new \Papaya\Ui\Listview\Subitem\Image\Toggle($icons, 'foo');
    $subitem->icons = $icons;
    $subitem->appendTo($document->appendElement('sample'));
    $this->assertEquals(
      /** @lang XML */
      '<sample><subitem align="left"/></sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \Papaya\Ui\Listview\Subitem\Image\Toggle::appendTo
  */
  public function testAppendToWithoutIcon() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Ui\Icon\Collection $icons */
    $icons = $this->createMock(\Papaya\Ui\Icon\Collection::class);
    $icons
      ->expects($this->once())
      ->method('offsetExists')
      ->with('foo')
      ->will($this->returnValue(FALSE));

    $document = new \Papaya\Xml\Document();
    $subitem = new \Papaya\Ui\Listview\Subitem\Image\Toggle($icons, 'foo');
    $subitem->icons = $icons;
    $subitem->appendTo($document->appendElement('sample'));
    $this->assertEquals(
      /** @lang XML */
      '<sample><subitem align="left"/></sample>',
      $document->saveXML($document->documentElement)
    );
  }

}
