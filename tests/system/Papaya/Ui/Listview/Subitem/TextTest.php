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

class PapayaUiListviewSubitemTextTest extends PapayaTestCase {

  /**
  * @covers PapayaUiListviewSubitemText::__construct
  */
  public function testConstructor() {
    $subitem = new PapayaUiListviewSubitemText('Sample text');
    $this->assertEquals(
      'Sample text', $subitem->text
    );
  }

  /**
  * @covers PapayaUiListviewSubitemText::__construct
  */
  public function testConstructorWithOptionalParameters() {
    $subitem = new PapayaUiListviewSubitemText('Sample text', array('foo' => 'bar'));
    $this->assertEquals(
      array('foo' => 'bar'), $subitem->actionParameters
    );
  }

  /**
  * @covers PapayaUiListviewSubitemText::appendTo
  */
  public function testAppendTo() {
    $document = new PapayaXmlDocument();
    $document->appendElement('test');
    $subitem = new PapayaUiListviewSubitemText('Sample text');
    $subitem->align = PapayaUiOptionAlign::RIGHT;
    $subitem->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<test><subitem align="right">Sample text</subitem></test>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
   * @covers PapayaUiListviewSubitemText::appendTo
   * @covers PapayaUiListviewSubitemText::getUrl
   */
  public function testAppendToWithActionParameters() {
    $reference = $this->mockPapaya()->reference('http://www.example.html');
    $reference->expects($this->once())->method('setParameters')->with(array('foo' => 'bar'));
    $document = new PapayaXmlDocument();
    $document->appendElement('test');
    $subitem = new PapayaUiListviewSubitemText('Sample text');
    $subitem->reference($reference);
    $subitem->setActionParameters(array('foo' => 'bar'));
    $subitem->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<test><subitem align="left"><a href="http://www.example.html">Sample text</a></subitem></test>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers PapayaUiListviewSubitemText::reference
  */
  public function testReferenceGetSet() {
    $reference = $this->mockPapaya()->reference();
    $subitem = new PapayaUiListviewSubitemText('Sample Text');
    $subitem->reference($reference);
    $this->assertSame($reference, $subitem->reference());
  }

  /**
  * @covers PapayaUiListviewSubitemText::reference
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
    $subitem = new PapayaUiListviewSubitemText('quickinfo', array('foo' => 'bar'));
    $subitem->collection($collection);
    $this->assertEquals(
      $reference, $subitem->reference()
    );
  }
}
