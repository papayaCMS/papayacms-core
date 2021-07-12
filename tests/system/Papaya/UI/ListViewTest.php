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

namespace Papaya\UI;
require_once __DIR__.'/../../../bootstrap.php';

class ListViewTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\UI\ListView::appendTo
   */
  public function testAppendTo() {
    $document = new \Papaya\XML\Document();
    $document->appendElement('sample');
    $listview = new ListView();
    $items = $this
      ->getMockBuilder(ListView\Items::class)
      ->setConstructorArgs(array($listview))
      ->getMock();
    $items
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));
    $listview->items($items);
    $columns = $this
      ->getMockBuilder(ListView\Columns::class)
      ->setConstructorArgs(array($listview))
      ->getMock();
    $columns
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));
    $listview->columns($columns);
    $toolbars = $this->createMock(Toolbars::class);
    $toolbars
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\XML\Element::class));
    $listview->toolbars($toolbars);
    $listview->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<sample><listview/></sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
   * @covers \Papaya\UI\ListView::appendTo
   */
  public function testAppendToWithCaption() {
    $document = new \Papaya\XML\Document();
    $document->appendElement('sample');
    $listview = new ListView();
    $listview->caption = 'test caption';
    $listview->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<sample><listview title="test caption"/></sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
   * @covers \Papaya\UI\ListView::appendTo
   */
  public function testAppendToWithMode() {
    $document = new \Papaya\XML\Document();
    $document->appendElement('sample');
    $listview = new ListView();
    $listview->mode = ListView::MODE_THUMBNAILS;
    $listview->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */
      '<sample><listview mode="thumbnails"/></sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
   * @covers \Papaya\UI\ListView::items
   */
  public function testItemsGetAfterSet() {
    $listview = new ListView();
    $items = $this
      ->getMockBuilder(ListView\Items::class)
      ->setConstructorArgs(array($listview))
      ->getMock();
    $this->assertSame($items, $listview->items($items));
  }

  /**
   * @covers \Papaya\UI\ListView::items
   * @covers \Papaya\UI\ListView::builder
   */
  public function testItemsGetAfterSettingBuilder() {
    $builder = $this
      ->getMockBuilder(ListView\Items\Builder::class)
      ->disableOriginalConstructor()
      ->getMock();
    $builder
      ->expects($this->once())
      ->method('fill')
      ->with($this->isInstanceOf(ListView\Items::class));
    $listview = new ListView();
    $listview->builder($builder);
    $listview->items();
    $listview->items();
  }

  /**
   * @covers \Papaya\UI\ListView::items
   */
  public function testItemsGetImplicitCreate() {
    $listview = new ListView();
    $items = $listview->items();
    $this->assertInstanceOf(ListView\Items::class, $items);
    $this->assertSame($listview, $items->owner());
  }

  /**
   * @covers \Papaya\UI\ListView::columns
   */
  public function testColumnsGetAfterSet() {
    $listview = new ListView();
    $columns = $this
      ->getMockBuilder(ListView\Columns::class)
      ->setConstructorArgs(array($listview))
      ->getMock();
    $this->assertSame($columns, $listview->columns($columns));
  }

  /**
   * @covers \Papaya\UI\ListView::columns
   */
  public function testColumnsGetImplicitCreate() {
    $listview = new ListView();
    $columns = $listview->columns();
    $this->assertInstanceOf(ListView\Columns::class, $columns);
    $this->assertSame($listview, $columns->owner());
  }

  /**
   * @covers \Papaya\UI\ListView::toolbars
   */
  public function testToolbarsGetAfterSet() {
    $listview = new ListView();
    $toolbars = $this->createMock(Toolbars::class);
    $this->assertSame($toolbars, $listview->toolbars($toolbars));
  }

  /**
   * @covers \Papaya\UI\ListView::toolbars
   */
  public function testToolbarsGetImplicitCreate() {
    $listview = new ListView();
    $toolbars = $listview->toolbars();
    $this->assertInstanceOf(Toolbars::class, $toolbars);
  }

  /**
   * @covers \Papaya\UI\ListView::reference
   */
  public function testReferenceGetAfterSet() {
    $reference = $this->createMock(Reference::class);
    $listview = new ListView();
    $this->assertSame(
      $reference, $listview->reference($reference)
    );
  }

  /**
   * @covers \Papaya\UI\ListView::reference
   */
  public function testReferenceGetImplicitCreate() {
    $listview = new ListView();
    $this->assertInstanceOf(
      Reference::class, $listview->reference()
    );
  }

  /**
   * @covers \Papaya\UI\ListView::setMode
   */
  public function testGetModeAfterSet() {
    $listview = new ListView();
    $listview->mode = ListView::MODE_THUMBNAILS;
    $this->assertEquals(ListView::MODE_THUMBNAILS, $listview->mode);
  }

  /**
   * @covers \Papaya\UI\ListView::setMode
   */
  public function testGetModeAfterSetInvalidMode() {
    $listview = new ListView();
    $listview->mode = 'invalid mode string';
    $this->assertEquals(ListView::MODE_DETAILS, $listview->mode);
  }
}
