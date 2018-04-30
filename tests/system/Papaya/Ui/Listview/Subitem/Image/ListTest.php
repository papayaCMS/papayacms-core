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

class PapayaUiListviewSubitemImageListTest extends PapayaTestCase {

  /**
  * @covers PapayaUiListviewSubitemImageList::__construct
  */
  public function testConstructor() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaUiIconList $icons */
    $icons = $this->createMock(PapayaUiIconList::class);
    $subitem = new PapayaUiListviewSubitemImageList(
      $icons, 'foo', PapayaUiListviewSubitemImageList::VALIDATE_BITMASK
    );
    $this->assertEquals(
      PapayaUiListviewSubitemImageList::VALIDATE_BITMASK, $subitem->selectionMode
    );
  }

  /**
  * @covers PapayaUiListviewSubitemImageList::appendTo
  * @covers PapayaUiListviewSubitemImageList::validateSelection
  */
  public function testAppendToUseValues() {
    $iconValid = $this
      ->getMockBuilder(PapayaUiIcon::class)
      ->disableOriginalConstructor()
      ->getMock();
    $iconValid
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(PapayaXmlElement::class));
    $iconInvalid = $this
      ->getMockBuilder(PapayaUiIcon::class)
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
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaUiIconList $icons */
    $icons = $this->createMock(PapayaUiIconList::class);
    $icons
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(
          new ArrayIterator(
            array(
              'foo' => $iconValid,
              'bar' => $iconInvalid
            )
          )
        )
      );

    $document = new PapayaXmlDocument();
    $subitem = new PapayaUiListviewSubitemImageList($icons, 'foo');
    $subitem->icons = $icons;
    $subitem->appendTo($document->appendElement('sample'));
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample><subitem align="left"><glyphs/></subitem></sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers PapayaUiListviewSubitemImageList::appendTo
  * @covers PapayaUiListviewSubitemImageList::validateSelection
  */
  public function testAppendToUseKeys() {
    $iconValid = $this
      ->getMockBuilder(PapayaUiIcon::class)
      ->disableOriginalConstructor()
      ->getMock();
    $iconValid
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(PapayaXmlElement::class));
    $iconInvalid = $this
      ->getMockBuilder(PapayaUiIcon::class)
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
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaUiIconList $icons */
    $icons = $this->createMock(PapayaUiIconList::class);
    $icons
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(
          new ArrayIterator(
            array(
              'foo' => $iconValid,
              'bar' => $iconInvalid
            )
          )
        )
      );

    $document = new PapayaXmlDocument();
    $subitem = new PapayaUiListviewSubitemImageList(
      $icons,
      array('foo' => TRUE),
      PapayaUiListviewSubitemImageList::VALIDATE_KEYS
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
  * @covers PapayaUiListviewSubitemImageList::appendTo
  * @covers PapayaUiListviewSubitemImageList::validateSelection
  */
  public function testAppendToUseBitmask() {
    $iconValid = $this
      ->getMockBuilder(PapayaUiIcon::class)
      ->disableOriginalConstructor()
      ->getMock();
    $iconValid
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(PapayaXmlElement::class));
    $iconInvalid = $this
      ->getMockBuilder(PapayaUiIcon::class)
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
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaUiIconList $icons */
    $icons = $this->createMock(PapayaUiIconList::class);
    $icons
      ->expects($this->once())
      ->method('getIterator')
      ->will(
        $this->returnValue(
          new ArrayIterator(
            array(
              1 => $iconValid,
              2 => $iconInvalid
            )
          )
        )
      );

    $document = new PapayaXmlDocument();
    $subitem = new PapayaUiListviewSubitemImageList(
      $icons,
      5,
      PapayaUiListviewSubitemImageList::VALIDATE_BITMASK
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
