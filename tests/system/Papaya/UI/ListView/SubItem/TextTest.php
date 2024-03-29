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

namespace Papaya\UI\ListView\SubItem;
require_once __DIR__.'/../../../../../bootstrap.php';

class TextTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\UI\ListView\SubItem\Text::__construct
   */
  public function testConstructor() {
    $subitem = new Text('Sample text');
    $this->assertEquals(
      'Sample text', $subitem->text
    );
  }

  /**
   * @covers \Papaya\UI\ListView\SubItem\Text::__construct
   */
  public function testConstructorWithOptionalParameters() {
    $subitem = new Text('Sample text', array('foo' => 'bar'));
    $this->assertEquals(
      array('foo' => 'bar'), $subitem->actionParameters
    );
  }

  /**
   * @covers \Papaya\UI\ListView\SubItem\Text::appendTo
   */
  public function testAppendTo() {
    $document = new \Papaya\XML\Document();
    $document->appendElement('test');
    $subitem = new Text('Sample text');
    $subitem->align = \Papaya\UI\Option\Align::RIGHT;
    $subitem->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<test><subitem align="right">Sample text</subitem></test>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
   * @covers \Papaya\UI\ListView\SubItem\Text::appendTo
   * @covers \Papaya\UI\ListView\SubItem\Text::getURL
   */
  public function testAppendToWithActionParameters() {
    $reference = $this->mockPapaya()->reference('http://www.example.html');
    $reference->expects($this->once())->method('setParameters')->with(array('foo' => 'bar'));
    $document = new \Papaya\XML\Document();
    $document->appendElement('test');
    $subitem = new Text('Sample text');
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
   * @covers \Papaya\UI\ListView\SubItem\Text::reference
   */
  public function testReferenceGetSet() {
    $reference = $this->mockPapaya()->reference();
    $subitem = new Text('Sample Text');
    $subitem->reference($reference);
    $this->assertSame($reference, $subitem->reference());
  }

  /**
   * @covers \Papaya\UI\ListView\SubItem\Text::reference
   */
  public function testReferenceGetFromListView() {
    $reference = $this->createMock(\Papaya\UI\Reference::class);
    $listview = $this->createMock(\Papaya\UI\ListView::class);
    $listview
      ->expects($this->once())
      ->method('reference')
      ->will($this->returnValue($reference));
    $collection = $this
      ->getMockBuilder(\Papaya\UI\ListView\SubItems::class)
      ->disableOriginalConstructor()
      ->getMock();
    $collection
      ->expects($this->once())
      ->method('getListView')
      ->will($this->returnValue($listview));
    $subitem = new Text('quickinfo', array('foo' => 'bar'));
    $subitem->collection($collection);
    $this->assertEquals(
      $reference, $subitem->reference()
    );
  }
}
