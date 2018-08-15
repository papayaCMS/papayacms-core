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

class PapayaUiListviewSubitemImageListTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\UI\Listview\Subitem\Images::__construct
  */
  public function testConstructor() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\Icon\Collection $icons */
    $icons = $this->createMock(\Papaya\UI\Icon\Collection::class);
    $subitem = new \Papaya\UI\Listview\Subitem\Images(
      $icons, 'foo', \Papaya\UI\Listview\Subitem\Images::VALIDATE_BITMASK
    );
    $this->assertEquals(
      \Papaya\UI\Listview\Subitem\Images::VALIDATE_BITMASK, $subitem->selectionMode
    );
  }

  /**
  * @covers \Papaya\UI\Listview\Subitem\Images::appendTo
  * @covers \Papaya\UI\Listview\Subitem\Images::validateSelection
  */
  public function testAppendToUseValues() {
    $iconValid = $this
      ->getMockBuilder(\Papaya\UI\Icon::class)
      ->disableOriginalConstructor()
      ->getMock();
    $iconValid
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));
    $iconInvalid = $this
      ->getMockBuilder(\Papaya\UI\Icon::class)
      ->disableOriginalConstructor()
      ->getMock();
    $iconInvalid
      ->expects($this->once())
      ->method('__set')
      ->with('visible', FALSE);
    $iconInvalid
      ->expects($this->once())
      ->method('appendTo')
      ->withAnyParameters();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\Icon\Collection $icons */
    $icons = $this->createMock(\Papaya\UI\Icon\Collection::class);
    $icons
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(
          new \ArrayIterator(
            array(
              'foo' => $iconValid,
              'bar' => $iconInvalid
            )
          )
        )
      );

    $document = new \Papaya\XML\Document();
    $subitem = new \Papaya\UI\Listview\Subitem\Images($icons, 'foo');
    $subitem->icons = $icons;
    $subitem->appendTo($document->appendElement('sample'));
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample><subitem align="left"><glyphs/></subitem></sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \Papaya\UI\Listview\Subitem\Images::appendTo
  * @covers \Papaya\UI\Listview\Subitem\Images::validateSelection
  */
  public function testAppendToUseKeys() {
    $iconValid = $this
      ->getMockBuilder(\Papaya\UI\Icon::class)
      ->disableOriginalConstructor()
      ->getMock();
    $iconValid
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));
    $iconInvalid = $this
      ->getMockBuilder(\Papaya\UI\Icon::class)
      ->disableOriginalConstructor()
      ->getMock();
    $iconInvalid
      ->expects($this->once())
      ->method('__set')
      ->with('visible', FALSE);
    $iconInvalid
      ->expects($this->once())
      ->method('appendTo')
      ->withAnyParameters();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\Icon\Collection $icons */
    $icons = $this->createMock(\Papaya\UI\Icon\Collection::class);
    $icons
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(
          new \ArrayIterator(
            array(
              'foo' => $iconValid,
              'bar' => $iconInvalid
            )
          )
        )
      );

    $document = new \Papaya\XML\Document();
    $subitem = new \Papaya\UI\Listview\Subitem\Images(
      $icons,
      array('foo' => TRUE),
      \Papaya\UI\Listview\Subitem\Images::VALIDATE_KEYS
    );
    $subitem->icons = $icons;
    $subitem->appendTo($document->appendElement('sample'));
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample><subitem align="left"><glyphs/></subitem></sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \Papaya\UI\Listview\Subitem\Images::appendTo
  * @covers \Papaya\UI\Listview\Subitem\Images::validateSelection
  */
  public function testAppendToUseBitmask() {
    $iconValid = $this
      ->getMockBuilder(\Papaya\UI\Icon::class)
      ->disableOriginalConstructor()
      ->getMock();
    $iconValid
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));
    $iconInvalid = $this
      ->getMockBuilder(\Papaya\UI\Icon::class)
      ->disableOriginalConstructor()
      ->getMock();
    $iconInvalid
      ->expects($this->once())
      ->method('__set')
      ->with('visible', FALSE);
    $iconInvalid
      ->expects($this->once())
      ->method('appendTo')
      ->withAnyParameters();
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\UI\Icon\Collection $icons */
    $icons = $this->createMock(\Papaya\UI\Icon\Collection::class);
    $icons
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(
          new \ArrayIterator(
            array(
              1 => $iconValid,
              2 => $iconInvalid
            )
          )
        )
      );

    $document = new \Papaya\XML\Document();
    $subitem = new \Papaya\UI\Listview\Subitem\Images(
      $icons,
      5,
      \Papaya\UI\Listview\Subitem\Images::VALIDATE_BITMASK
    );
    $subitem->icons = $icons;
    $subitem->appendTo($document->appendElement('sample'));
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample><subitem align="left"><glyphs/></subitem></sample>',
      $document->saveXML($document->documentElement)
    );
  }
}
