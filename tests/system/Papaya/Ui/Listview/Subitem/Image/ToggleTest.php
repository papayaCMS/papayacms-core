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

namespace Papaya\UI\Listview\Subitem\Image;
require_once __DIR__.'/../../../../../../bootstrap.php';

class ToggleTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\UI\Listview\Subitem\Image\Toggle::__construct
   * @covers \Papaya\UI\Listview\Subitem\Image\Toggle::setIcons
   */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\Icon\Collection $icons */
    $icons = $this->createMock(\Papaya\UI\Icon\Collection::class);
    $subitem = new Toggle($icons, 'foo');
    $this->assertSame($icons, $subitem->icons);
    $this->assertEquals('foo', $subitem->selection);
  }

  /**
   * @covers \Papaya\UI\Listview\Subitem\Image\Toggle::appendTo
   */
  public function testAppendToWithIcon() {
    $icon = $this
      ->getMockBuilder(\Papaya\UI\Icon::class)
      ->disableOriginalConstructor()
      ->getMock();
    $icon
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\Icon\Collection $icons */
    $icons = $this->createMock(\Papaya\UI\Icon\Collection::class);
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

    $document = new \Papaya\XML\Document();
    $subitem = new Toggle($icons, 'foo');
    $subitem->icons = $icons;
    $subitem->appendTo($document->appendElement('sample'));
    $this->assertEquals(
    /** @lang XML */
      '<sample><subitem align="left"/></sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
   * @covers \Papaya\UI\Listview\Subitem\Image\Toggle::appendTo
   */
  public function testAppendToWithoutIcon() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\Icon\Collection $icons */
    $icons = $this->createMock(\Papaya\UI\Icon\Collection::class);
    $icons
      ->expects($this->once())
      ->method('offsetExists')
      ->with('foo')
      ->will($this->returnValue(FALSE));

    $document = new \Papaya\XML\Document();
    $subitem = new Toggle($icons, 'foo');
    $subitem->icons = $icons;
    $subitem->appendTo($document->appendElement('sample'));
    $this->assertEquals(
    /** @lang XML */
      '<sample><subitem align="left"/></sample>',
      $document->saveXML($document->documentElement)
    );
  }

}
