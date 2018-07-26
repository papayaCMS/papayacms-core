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
  * @covers \PapayaUiListview::appendTo
  */
  public function testAppendTo() {
    $document = new \PapayaXmlDocument();
    $document->appendElement('sample');
    $listview = new \PapayaUiListview();
    $items = $this
      ->getMockBuilder(\PapayaUiListviewItems::class)
      ->setConstructorArgs(array($listview))
      ->getMock();
    $items
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\PapayaXmlElement::class));
    $listview->items($items);
    $columns = $this
      ->getMockBuilder(\PapayaUiListviewColumns::class)
      ->setConstructorArgs(array($listview))
      ->getMock();
    $columns
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\PapayaXmlElement::class));
    $listview->columns($columns);
    $toolbars = $this->createMock(\PapayaUiToolbars::class);
    $toolbars
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\PapayaXmlElement::class));
    $listview->toolbars($toolbars);
    $listview->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample><listview/></sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \PapayaUiListview::appendTo
  */
  public function testAppendToWithCaption() {
    $document = new \PapayaXmlDocument();
    $document->appendElement('sample');
    $listview = new \PapayaUiListview();
    $listview->caption = 'test caption';
    $listview->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample><listview title="test caption"/></sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \PapayaUiListview::appendTo
  */
  public function testAppendToWithMode() {
    $document = new \PapayaXmlDocument();
    $document->appendElement('sample');
    $listview = new \PapayaUiListview();
    $listview->mode = \PapayaUiListview::MODE_THUMBNAILS;
    $listview->appendTo($document->documentElement);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample><listview mode="thumbnails"/></sample>',
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \PapayaUiListview::items
  */
  public function testItemsGetAfterSet() {
    $listview = new \PapayaUiListview();
    $items = $this
      ->getMockBuilder(\PapayaUiListviewItems::class)
      ->setConstructorArgs(array($listview))
      ->getMock();
    $this->assertSame($items, $listview->items($items));
  }

  /**
  * @covers \PapayaUiListview::items
  * @covers \PapayaUiListview::builder
  */
  public function testItemsGetAfterSettingBuilder() {
    $builder = $this
      ->getMockBuilder(\PapayaUiListviewItemsBuilder::class)
      ->disableOriginalConstructor()
      ->getMock();
    $builder
      ->expects($this->once())
      ->method('fill')
      ->with($this->isInstanceOf(\PapayaUiListviewItems::class));
    $listview = new \PapayaUiListview();
    $listview->builder($builder);
    $listview->items();
    $listview->items();
  }

  /**
  * @covers \PapayaUiListview::items
  */
  public function testItemsGetImplicitCreate() {
    $listview = new \PapayaUiListview();
    $items = $listview->items();
    $this->assertInstanceOf(\PapayaUiListviewItems::class, $items);
    $this->assertSame($listview, $items->owner());
  }

  /**
  * @covers \PapayaUiListview::columns
  */
  public function testColumnsGetAfterSet() {
    $listview = new \PapayaUiListview();
    $columns = $this
      ->getMockBuilder(\PapayaUiListviewColumns::class)
      ->setConstructorArgs(array($listview))
      ->getMock();
    $this->assertSame($columns, $listview->columns($columns));
  }

  /**
  * @covers \PapayaUiListview::columns
  */
  public function testColumnsGetImplicitCreate() {
    $listview = new \PapayaUiListview();
    $columns = $listview->columns();
    $this->assertInstanceOf(\PapayaUiListviewColumns::class, $columns);
    $this->assertSame($listview, $columns->owner());
  }

  /**
  * @covers \PapayaUiListview::toolbars
  */
  public function testToolbarsGetAfterSet() {
    $listview = new \PapayaUiListview();
    $toolbars = $this->createMock(\PapayaUiToolbars::class);
    $this->assertSame($toolbars, $listview->toolbars($toolbars));
  }

  /**
  * @covers \PapayaUiListview::toolbars
  */
  public function testToolbarsGetImplicitCreate() {
    $listview = new \PapayaUiListview();
    $toolbars = $listview->toolbars();
    $this->assertInstanceOf(\PapayaUiToolbars::class, $toolbars);
  }

  /**
  * @covers \PapayaUiListview::reference
  */
  public function testReferenceGetAfterSet() {
    $reference = $this->createMock(\PapayaUiReference::class);
    $listview = new \PapayaUiListview();
    $this->assertSame(
      $reference, $listview->reference($reference)
    );
  }

  /**
  * @covers \PapayaUiListview::reference
  */
  public function testReferenceGetImplicitCreate() {
    $listview = new \PapayaUiListview();
    $this->assertInstanceOf(
      \PapayaUiReference::class, $listview->reference()
    );
  }

  /**
  * @covers \PapayaUiListview::setMode
  */
  public function testGetModeAfterSet() {
    $listview = new \PapayaUiListview();
    $listview->mode = \PapayaUiListview::MODE_THUMBNAILS;
    $this->assertEquals(\PapayaUiListview::MODE_THUMBNAILS, $listview->mode);
  }

  /**
  * @covers \PapayaUiListview::setMode
  */
  public function testGetModeAfterSetInvalidMode() {
    $listview = new \PapayaUiListview();
    $listview->mode = 'invalid mode string';
    $this->assertEquals(\PapayaUiListview::MODE_DETAILS, $listview->mode);
  }
}
