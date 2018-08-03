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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaUiListviewTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Ui\Listview::appendTo
  */
  public function testAppendTo() {
    $document = new \Papaya\Xml\Document();
    $document->appendElement('sample');
    $listview = new \Papaya\Ui\Listview();
    $items = $this
      ->getMockBuilder(\Papaya\Ui\Listview\Items::class)
      ->setConstructorArgs(array($listview))
      ->getMock();
    $items
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    $listview->items($items);
    $columns = $this
      ->getMockBuilder(\Papaya\Ui\Listview\Columns::class)
      ->setConstructorArgs(array($listview))
      ->getMock();
    $columns
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    $listview->columns($columns);
    $toolbars = $this->createMock(\PapayaUiToolbars::class);
    $toolbars
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    $listview->toolbars($toolbars);
    $listview->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample><listview/></sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \Papaya\Ui\Listview::appendTo
  */
  public function testAppendToWithCaption() {
    $document = new \Papaya\Xml\Document();
    $document->appendElement('sample');
    $listview = new \Papaya\Ui\Listview();
    $listview->caption = 'test caption';
    $listview->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample><listview title="test caption"/></sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \Papaya\Ui\Listview::appendTo
  */
  public function testAppendToWithMode() {
    $document = new \Papaya\Xml\Document();
    $document->appendElement('sample');
    $listview = new \Papaya\Ui\Listview();
    $listview->mode = \Papaya\Ui\Listview::MODE_THUMBNAILS;
    $listview->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample><listview mode="thumbnails"/></sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \Papaya\Ui\Listview::items
  */
  public function testItemsGetAfterSet() {
    $listview = new \Papaya\Ui\Listview();
    $items = $this
      ->getMockBuilder(\Papaya\Ui\Listview\Items::class)
      ->setConstructorArgs(array($listview))
      ->getMock();
    $this->assertSame($items, $listview->items($items));
  }

  /**
  * @covers \Papaya\Ui\Listview::items
  * @covers \Papaya\Ui\Listview::builder
  */
  public function testItemsGetAfterSettingBuilder() {
    $builder = $this
      ->getMockBuilder(\Papaya\Ui\Listview\Items\Builder::class)
      ->disableOriginalConstructor()
      ->getMock();
    $builder
      ->expects($this->once())
      ->method('fill')
      ->with($this->isInstanceOf(\Papaya\Ui\Listview\Items::class));
    $listview = new \Papaya\Ui\Listview();
    $listview->builder($builder);
    $listview->items();
    $listview->items();
  }

  /**
  * @covers \Papaya\Ui\Listview::items
  */
  public function testItemsGetImplicitCreate() {
    $listview = new \Papaya\Ui\Listview();
    $items = $listview->items();
    $this->assertInstanceOf(\Papaya\Ui\Listview\Items::class, $items);
    $this->assertSame($listview, $items->owner());
  }

  /**
  * @covers \Papaya\Ui\Listview::columns
  */
  public function testColumnsGetAfterSet() {
    $listview = new \Papaya\Ui\Listview();
    $columns = $this
      ->getMockBuilder(\Papaya\Ui\Listview\Columns::class)
      ->setConstructorArgs(array($listview))
      ->getMock();
    $this->assertSame($columns, $listview->columns($columns));
  }

  /**
  * @covers \Papaya\Ui\Listview::columns
  */
  public function testColumnsGetImplicitCreate() {
    $listview = new \Papaya\Ui\Listview();
    $columns = $listview->columns();
    $this->assertInstanceOf(\Papaya\Ui\Listview\Columns::class, $columns);
    $this->assertSame($listview, $columns->owner());
  }

  /**
  * @covers \Papaya\Ui\Listview::toolbars
  */
  public function testToolbarsGetAfterSet() {
    $listview = new \Papaya\Ui\Listview();
    $toolbars = $this->createMock(\PapayaUiToolbars::class);
    $this->assertSame($toolbars, $listview->toolbars($toolbars));
  }

  /**
  * @covers \Papaya\Ui\Listview::toolbars
  */
  public function testToolbarsGetImplicitCreate() {
    $listview = new \Papaya\Ui\Listview();
    $toolbars = $listview->toolbars();
    $this->assertInstanceOf(\PapayaUiToolbars::class, $toolbars);
  }

  /**
  * @covers \Papaya\Ui\Listview::reference
  */
  public function testReferenceGetAfterSet() {
    $reference = $this->createMock(\Papaya\Ui\Reference::class);
    $listview = new \Papaya\Ui\Listview();
    $this->assertSame(
      $reference, $listview->reference($reference)
    );
  }

  /**
  * @covers \Papaya\Ui\Listview::reference
  */
  public function testReferenceGetImplicitCreate() {
    $listview = new \Papaya\Ui\Listview();
    $this->assertInstanceOf(
      \Papaya\Ui\Reference::class, $listview->reference()
    );
  }

  /**
  * @covers \Papaya\Ui\Listview::setMode
  */
  public function testGetModeAfterSet() {
    $listview = new \Papaya\Ui\Listview();
    $listview->mode = \Papaya\Ui\Listview::MODE_THUMBNAILS;
    $this->assertEquals(\Papaya\Ui\Listview::MODE_THUMBNAILS, $listview->mode);
  }

  /**
  * @covers \Papaya\Ui\Listview::setMode
  */
  public function testGetModeAfterSetInvalidMode() {
    $listview = new \Papaya\Ui\Listview();
    $listview->mode = 'invalid mode string';
    $this->assertEquals(\Papaya\Ui\Listview::MODE_DETAILS, $listview->mode);
  }
}
