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

class PapayaUiDialogFieldGroupButtonsTest extends \PapayaTestCase {

  /**
  * @covers \PapayaUiDialogFieldGroupButtons::__construct
  */
  public function testConstructor() {
    $group = new \PapayaUiDialogFieldGroupButtons('Group Caption');
    $this->assertEquals(
      'Group Caption', $group->getCaption()
    );
  }

  /**
  * @covers \PapayaUiDialogFieldGroupButtons::buttons
  */
  public function testFieldsGetImplicitCreate() {
    $group = new \PapayaUiDialogFieldGroupButtons('Group Caption');
    $this->assertInstanceOf(
      \PapayaUiDialogButtons::class, $group->buttons()
    );
  }

  /**
  * @covers \PapayaUiDialogFieldGroupButtons::buttons
  */
  public function testFieldsGetImplicitCreateWithDialog() {
    $dialog = $this
      ->getMockBuilder(\PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $group = new \PapayaUiDialogFieldGroupButtons('Group Caption');
    $group->collection($this->getCollectionMock($dialog));
    $this->assertSame(
      $dialog, $group->buttons()->owner()
    );
  }

  /**
  * @covers \PapayaUiDialogFieldGroupButtons::buttons
  */
  public function testFieldsSet() {
    $dialog = $this
      ->getMockBuilder(\PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $group = new \PapayaUiDialogFieldGroupButtons('Group Caption');
    $group->collection($this->getCollectionMock($dialog));
    $buttons = $this->createMock(\PapayaUiDialogButtons::class);
    $buttons
      ->expects($this->once())
      ->method('owner')
      ->with($this->equalTo($dialog));
    $group->buttons($buttons);
    $this->assertAttributeSame(
      $buttons, '_buttons', $group
    );
  }

  /**
  * @covers \PapayaUiDialogFieldGroupButtons::buttons
  */
  public function testFieldsGetAfterSet() {
    $dialog = $this
      ->getMockBuilder(\PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $group = new \PapayaUiDialogFieldGroupButtons('Group Caption');
    $group->collection($this->getCollectionMock($dialog));
    $buttons = $this->createMock(\PapayaUiDialogButtons::class);
    $buttons
      ->expects($this->once())
      ->method('owner')
      ->with($this->equalTo($dialog));
    $this->assertSame(
      $buttons, $group->buttons($buttons)
    );
  }

  /**
  * @covers \PapayaUiDialogFieldGroupButtons::validate
  */
  public function testValidateExpectingTrue() {
    $group = new \PapayaUiDialogFieldGroupButtons('Group Caption');
    $this->assertTrue($group->validate());
  }

  /**
  * @covers \PapayaUiDialogFieldGroupButtons::validate
  */
  public function testValidateWithoutDialogExpectingFalse() {
    $group = new \PapayaUiDialogFieldGroupButtons('Group Caption');
    $this->assertTrue($group->validate());
  }

  /**
  * @covers \PapayaUiDialogFieldGroupButtons::collect
  */
  public function testCollect() {
    $dialog = $this
      ->getMockBuilder(\PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $buttons = $this->createMock(\PapayaUiDialogButtons::class);
    $buttons
      ->expects($this->once())
      ->method('collect')
      ->will($this->returnValue(TRUE));
    $group = new \PapayaUiDialogFieldGroupButtons('Group Caption');
    $group->collection($this->getCollectionMock($dialog));
    $group->buttons($buttons);
    $this->assertTrue($group->collect());
  }

  /**
  * @covers \PapayaUiDialogFieldGroupButtons::collect
  */
  public function testCollectWithoutDialog() {
    $group = new \PapayaUiDialogFieldGroupButtons('Group Caption');
    $group->collection($this->createMock(\PapayaUiDialogButtons::class));
    $this->assertFalse($group->collect());
  }

  /**
  * @covers \PapayaUiDialogFieldGroupButtons::appendTo
  */
  public function testAppendTo() {
    $buttons = $this->createMock(\PapayaUiDialogButtons::class);
    $buttons
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\PapayaXmlElement::class));
    $buttons
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(1));
    $group = new \PapayaUiDialogFieldGroupButtons('Group Caption');
    $group->collection($this->createMock(\PapayaUiDialogButtons::class));
    $group->buttons($buttons);
    $this->assertEquals(
      /** @lang XML */
      '<field-group caption="Group Caption"/>',
      $group->getXml()
    );
  }

  /**
  * @covers \PapayaUiDialogFieldGroupButtons::appendTo
  */
  public function testAppendToWithId() {
    $buttons = $this->createMock(\PapayaUiDialogButtons::class);
    $buttons
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\PapayaXmlElement::class));
    $buttons
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(1));
    $group = new \PapayaUiDialogFieldGroupButtons('Group Caption');
    $group->setId('sampleId');
    $group->collection($this->createMock(\PapayaUiDialogButtons::class));
    $group->buttons($buttons);
    $this->assertEquals(
      /** @lang XML */
      '<field-group caption="Group Caption" id="sampleId"/>',
      $group->getXml()
    );
  }

  /**
  * @covers \PapayaUiDialogFieldGroupButtons::appendTo
  */
  public function testAppendToWithoutFields() {
    $document = new \PapayaXmlDocument();
    $node = $document->createElement('sample');
    $document->appendChild($node);
    $group = new \PapayaUiDialogFieldGroupButtons('Group Caption');
    $group->appendTo($node);
    $this->assertEquals(
      /** @lang XML */
      '<sample/>',
      $document->saveXML($node)
    );
  }

  /**
  * @covers \PapayaUiDialogFieldGroupButtons::collection
  */
  public function testCollectionGetAfterSet() {
    $owner = $this->createMock(\PapayaUiDialog::class);
    $papaya = $this->mockPapaya()->application();
    $collection = $this->createMock(\PapayaUiControlCollection::class);
    $collection
      ->expects($this->once())
      ->method('papaya')
      ->will($this->returnValue($papaya));
    $collection
      ->expects($this->any())
      ->method('hasOwner')
      ->will($this->returnValue(TRUE));
    $collection
      ->expects($this->any())
      ->method('owner')
      ->will($this->returnValue($owner));
    $buttons = $this->createMock(\PapayaUiDialogButtons::class);
    $buttons
      ->expects($this->once())
      ->method('owner')
      ->with($owner);
    $item = new \PapayaUiDialogFieldGroupButtons('Group Caption');
    $item->buttons($buttons);
    $this->assertSame(
      $collection, $item->collection($collection)
    );
    $this->assertEquals(
      $papaya, $item->papaya()
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
    $collection = $this->createMock(\PapayaUiDialogFields::class);
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
