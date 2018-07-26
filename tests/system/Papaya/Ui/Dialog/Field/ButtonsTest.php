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

class PapayaUiDialogFieldButtonsTest extends PapayaTestCase {

  /**
  * @covers \PapayaUiDialogFieldButtons::buttons
  */
  public function testFieldsGetImplicitCreate() {
    $field = new \PapayaUiDialogFieldButtons();
    $this->assertInstanceOf(
      \PapayaUiDialogButtons::class, $field->buttons()
    );
  }

  /**
  * @covers \PapayaUiDialogFieldButtons::buttons
  */
  public function testFieldsGetImplicitCreateWithDialog() {
    $dialog = $this
      ->getMockBuilder(PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $field = new \PapayaUiDialogFieldButtons();
    $field->collection($this->getCollectionMock($dialog));
    $this->assertSame(
      $dialog, $field->buttons()->owner()
    );
  }

  /**
  * @covers \PapayaUiDialogFieldButtons::buttons
  */
  public function testFieldsSet() {
    $dialog = $this
      ->getMockBuilder(PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $field = new \PapayaUiDialogFieldButtons();
    $field->collection($this->getCollectionMock($dialog));
    $buttons = $this->createMock(PapayaUiDialogButtons::class);
    $buttons
      ->expects($this->once())
      ->method('owner')
      ->with($this->equalTo($dialog));
    $field->buttons($buttons);
    $this->assertAttributeSame(
      $buttons, '_buttons', $field
    );
  }

  /**
  * @covers \PapayaUiDialogFieldButtons::buttons
  */
  public function testFieldsGetAfterSet() {
    $dialog = $this
      ->getMockBuilder(PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $field = new \PapayaUiDialogFieldButtons();
    $field->collection($this->getCollectionMock($dialog));
    $buttons = $this->createMock(PapayaUiDialogButtons::class);
    $buttons
      ->expects($this->once())
      ->method('owner')
      ->with($this->equalTo($dialog));
    $this->assertSame(
      $buttons, $field->buttons($buttons)
    );
  }

  /**
  * @covers \PapayaUiDialogFieldButtons::validate
  */
  public function testValidateExpectingTrue() {
    $field = new \PapayaUiDialogFieldButtons();
    $this->assertTrue($field->validate());
  }

  /**
  * @covers \PapayaUiDialogFieldButtons::collect
  */
  public function testCollect() {
    $dialog = $this
      ->getMockBuilder(PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $buttons = $this->createMock(PapayaUiDialogButtons::class);
    $buttons
      ->expects($this->once())
      ->method('collect')
      ->will($this->returnValue(TRUE));
    $field = new \PapayaUiDialogFieldButtons();
    $field->collection($this->getCollectionMock($dialog));
    $field->buttons($buttons);
    $this->assertTrue($field->collect());
  }

  /**
  * @covers \PapayaUiDialogFieldButtons::collect
  */
  public function testCollectWithoutDialog() {
    $field = new \PapayaUiDialogFieldButtons();
    $field->collection($this->createMock(PapayaUiDialogButtons::class));
    $this->assertFalse($field->collect());
  }

  /**
  * @covers \PapayaUiDialogFieldButtons::appendTo
  */
  public function testAppendTo() {
    $buttons = $this->createMock(PapayaUiDialogButtons::class);
    $buttons
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(PapayaXmlElement::class));
    $buttons
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(1));
    $field = new \PapayaUiDialogFieldButtons();
    $field->collection($this->createMock(PapayaUiDialogButtons::class));
    $field->buttons($buttons);
    $this->assertEquals(
      /** @lang XML */
      '<field class="DialogFieldButtons" error="no"><buttons/></field>',
      $field->getXml()
    );
  }

  /**
  * @covers \PapayaUiDialogFieldButtons::appendTo
  */
  public function testAppendToWithId() {
    $buttons = $this->createMock(PapayaUiDialogButtons::class);
    $buttons
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(PapayaXmlElement::class));
    $buttons
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(1));
    $field = new \PapayaUiDialogFieldButtons();
    $field->setId('sampleId');
    $field->collection($this->createMock(PapayaUiDialogButtons::class));
    $field->buttons($buttons);
    $this->assertEquals(
      /** @lang XML */
      '<field class="DialogFieldButtons" error="no" id="sampleId"><buttons/></field>',
      $field->getXml()
    );
  }

  /**
  * @covers \PapayaUiDialogFieldButtons::appendTo
  */
  public function testAppendToWithoutFields() {
    $field = new \PapayaUiDialogFieldButtons();
    $this->assertEquals(
      '',
      $field->getXml()
    );
  }

  /*************************
  * Mocks
  *************************/

  /**
   * @param object|NULL $owner
   * @return \PHPUnit_Framework_MockObject_MockObject|\PapayaUiDialogFields
   */
  public function getCollectionMock($owner = NULL) {
    $collection = $this->createMock(PapayaUiDialogFields::class);
    if ($owner) {
      $collection
        ->expects($this->any())
        ->method('hasOwner')
        ->will($this->returnValue(TRUE));
      $collection
        ->expects($this->any())
        ->method('owner')
        ->will($this->returnValue($owner));
    } else {
      $collection
        ->expects($this->any())
        ->method('hasOwner')
        ->will($this->returnValue(FALSE));
    }
    return $collection;
  }
}
