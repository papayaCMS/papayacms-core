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

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiListviewSubitemImageTest extends PapayaTestCase {

  /**
  * @covers PapayaUiListviewSubitemImage::__construct
  */
  public function testConstructor() {
    $subitem = new PapayaUiListviewSubitemImage('sample.png');
    $this->assertEquals(
      'sample.png', $subitem->image
    );
  }

  /**
  * @covers PapayaUiListviewSubitemImage::__construct
  */
  public function testConstructorWithOptionalParameters() {
    $subitem = new PapayaUiListviewSubitemImage('sample.png', 'quickinfo', array('foo' => 'bar'));
    $this->assertEquals(
      'quickinfo', $subitem->hint
    );
    $this->assertEquals(
      array('foo' => 'bar'), $subitem->actionParameters
    );
  }

  /**
  * @covers PapayaUiListviewSubitemImage::reference
  */
  public function testReferenceGetAfterSet() {
    $subitem = new PapayaUiListviewSubitemImage('sample.png', 'quickinfo', array('foo' => 'bar'));
    $subitem->reference($reference = $this->createMock(PapayaUiReference::class));
    $this->assertSame(
      $reference, $subitem->reference()
    );
  }

  /**
  * @covers PapayaUiListviewSubitemImage::reference
  */
  public function testReferenceGetFromListview() {
    $reference = $this->createMock(PapayaUiReference::class);
    $listview = $this->createMock(PapayaUiListview::class);
    $listview
      ->expects($this->once())
      ->method('reference')
      ->will($this->returnValue($reference));
    $collection = $this
      ->getMockBuilder(PapayaUiListviewSubitems::class)
      ->disableOriginalConstructor()
      ->getMock();
    $collection
      ->expects($this->once())
      ->method('getListview')
      ->will($this->returnValue($listview));

    $subitem = new PapayaUiListviewSubitemImage('sample.png', 'quickinfo', array('foo' => 'bar'));
    $subitem->collection($collection);
    $this->assertEquals(
      $reference, $subitem->reference()
    );
  }

  /**
  * @covers PapayaUiListviewSubitemImage::appendTo
  */
  public function testAppendTo() {
    $document = new PapayaXmlDocument();
    $document->appendElement('test');
    $subitem = new PapayaUiListviewSubitemImage('image');
    $subitem->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'sample.png')))
    );
    $subitem->align = PapayaUiOptionAlign::CENTER;
    $subitem->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<test><subitem align="center"><glyph src="sample.png"/></subitem></test>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers PapayaUiListviewSubitemImage::appendTo
  */
  public function testAppendToWithHint() {
    $document = new PapayaXmlDocument();
    $document->appendElement('test');
    $subitem = new PapayaUiListviewSubitemImage('image', 'quickinfo');
    $subitem->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'sample.png')))
    );
    $subitem->align = PapayaUiOptionAlign::CENTER;
    $subitem->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<test><subitem align="center"><glyph src="sample.png" hint="quickinfo"/></subitem></test>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers PapayaUiListviewSubitemImage::appendTo
  * @covers PapayaUiListviewSubitemImage::getUrl
  */
  public function testAppendToWithReference() {
    $document = new PapayaXmlDocument();
    $document->appendElement('test');
    $reference = $this->createMock(PapayaUiReference::class);
    $reference
      ->expects($this->once())
      ->method('setParameters')
      ->with(array('foo' => 'bar'));
    $reference
      ->expects($this->once())
      ->method('getRelative')
      ->will($this->returnValue('sample.html'));
    $subitem = new PapayaUiListviewSubitemImage('image', '', array('foo' => 'bar'));
    $subitem->reference = $reference;
    $subitem->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'sample.png')))
    );
    $subitem->align = PapayaUiOptionAlign::CENTER;
    $subitem->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<test><subitem align="center"><glyph src="sample.png" href="sample.html"/></subitem></test>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers PapayaUiListviewSubitemImage::appendTo
  * @covers PapayaUiListviewSubitemImage::getUrl
  */
  public function testAppendToWithReferenceFromListview() {
    $document = new PapayaXmlDocument();
    $document->appendElement('test');
    $reference = $this->createMock(PapayaUiReference::class);
    $reference
      ->expects($this->once())
      ->method('setParameters')
      ->with(array('foo' => 'bar'), 'group');
    $reference
      ->expects($this->once())
      ->method('getRelative')
      ->will($this->returnValue('sample.html'));
    $listview = $this->createMock(PapayaUiListview::class);
    $listview
      ->expects($this->once())
      ->method('reference')
      ->will($this->returnValue($reference));
    $listview
      ->expects($this->once())
      ->method('parameterGroup')
      ->will($this->returnValue('group'));
    $collection = $this->getMock(
      PapayaUiListviewSubitems::class,
      array(),
      array($this->getMock(PapayaUiListviewItem::class, array(), array('', '')))
    );
    $collection
      ->expects($this->exactly(2))
      ->method('getListview')
      ->will($this->returnValue($listview));
    $subitem = new PapayaUiListviewSubitemImage('image', '', array('foo' => 'bar'));
    $subitem->collection($collection);
    $subitem->papaya(
      $this->mockPapaya()->application(array('Images' => array('image' => 'sample.png')))
    );
    $subitem->align = PapayaUiOptionAlign::CENTER;
    $subitem->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<test><subitem align="center"><glyph src="sample.png" href="sample.html"/></subitem></test>',
      $document->saveXML($document->documentElement)
    );
  }
}
