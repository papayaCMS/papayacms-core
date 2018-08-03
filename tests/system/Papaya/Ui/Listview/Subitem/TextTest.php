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

class PapayaUiListviewSubitemTextTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Ui\Listview\Subitem\Text::__construct
  */
  public function testConstructor() {
    $subitem = new \Papaya\Ui\Listview\Subitem\Text('Sample text');
    $this->assertEquals(
      'Sample text', $subitem->text
    );
  }

  /**
  * @covers \Papaya\Ui\Listview\Subitem\Text::__construct
  */
  public function testConstructorWithOptionalParameters() {
    $subitem = new \Papaya\Ui\Listview\Subitem\Text('Sample text', array('foo' => 'bar'));
    $this->assertEquals(
      array('foo' => 'bar'), $subitem->actionParameters
    );
  }

  /**
  * @covers \Papaya\Ui\Listview\Subitem\Text::appendTo
  */
  public function testAppendTo() {
    $document = new \Papaya\Xml\Document();
    $document->appendElement('test');
    $subitem = new \Papaya\Ui\Listview\Subitem\Text('Sample text');
    $subitem->align = \Papaya\Ui\Option\Align::RIGHT;
    $subitem->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<test><subitem align="right">Sample text</subitem></test>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
   * @covers \Papaya\Ui\Listview\Subitem\Text::appendTo
   * @covers \Papaya\Ui\Listview\Subitem\Text::getUrl
   */
  public function testAppendToWithActionParameters() {
    $reference = $this->mockPapaya()->reference('http://www.example.html');
    $reference->expects($this->once())->method('setParameters')->with(array('foo' => 'bar'));
    $document = new \Papaya\Xml\Document();
    $document->appendElement('test');
    $subitem = new \Papaya\Ui\Listview\Subitem\Text('Sample text');
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
  * @covers \Papaya\Ui\Listview\Subitem\Text::reference
  */
  public function testReferenceGetSet() {
    $reference = $this->mockPapaya()->reference();
    $subitem = new \Papaya\Ui\Listview\Subitem\Text('Sample Text');
    $subitem->reference($reference);
    $this->assertSame($reference, $subitem->reference());
  }

  /**
  * @covers \Papaya\Ui\Listview\Subitem\Text::reference
  */
  public function testReferenceGetFromListview() {
    $reference = $this->createMock(\Papaya\Ui\Reference::class);
    $listview = $this->createMock(\Papaya\Ui\Listview::class);
    $listview
      ->expects($this->once())
      ->method('reference')
      ->will($this->returnValue($reference));
    $collection = $this
      ->getMockBuilder(\Papaya\Ui\Listview\Subitems::class)
      ->disableOriginalConstructor()
      ->getMock();
    $collection
      ->expects($this->once())
      ->method('getListview')
      ->will($this->returnValue($listview));
    $subitem = new \Papaya\Ui\Listview\Subitem\Text('quickinfo', array('foo' => 'bar'));
    $subitem->collection($collection);
    $this->assertEquals(
      $reference, $subitem->reference()
    );
  }
}
