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

class PapayaUiDialogFieldGroupTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Ui\Dialog\Field\Group::__construct
  */
  public function testConstructor() {
    $group = new \Papaya\Ui\Dialog\Field\Group('Group Caption');
    $this->assertAttributeEquals(
      'Group Caption', '_caption', $group
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field\Group::fields
  */
  public function testFieldsGetImplicitCreate() {
    $group = new \Papaya\Ui\Dialog\Field\Group('Group Caption');
    $group->collection($this->createMock(\PapayaUiDialogFields::class));
    $this->assertInstanceOf(
      \PapayaUiDialogFields::class, $group->fields()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field\Group::fields
  */
  public function testFieldsGetImplicitCreateWithDialog() {
    $dialog = $this
      ->getMockBuilder(\PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $group = new \Papaya\Ui\Dialog\Field\Group('Group Caption');
    $group->collection($this->getCollectionMock($dialog));
    $this->assertSame(
      $dialog, $group->fields()->owner()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field\Group::fields
  */
  public function testFieldsSet() {
    $dialog = $this
      ->getMockBuilder(\PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $group = new \Papaya\Ui\Dialog\Field\Group('Group Caption');
    $group->collection($this->getCollectionMock($dialog));
    $fields = $this->createMock(\PapayaUiDialogFields::class);
    $fields
      ->expects($this->once())
      ->method('owner')
      ->with($this->equalTo($dialog));
    $group->fields($fields);
    $this->assertAttributeSame(
      $fields, '_fields', $group
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field\Group::fields
  */
  public function testFieldsGetAfterSet() {
    $dialog = $this
      ->getMockBuilder(\PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $group = new \Papaya\Ui\Dialog\Field\Group('Group Caption');
    $group->collection($this->getCollectionMock($dialog));
    $fields = $this->createMock(\PapayaUiDialogFields::class);
    $fields
      ->expects($this->once())
      ->method('owner')
      ->with($this->equalTo($dialog));
    $this->assertSame(
      $fields, $group->fields($fields)
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field\Group::validate
  */
  public function testValidateExpectingTrue() {
    $dialog = $this
      ->getMockBuilder(\PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $fields = $this->createMock(\PapayaUiDialogFields::class);
    $fields
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(TRUE));
    $group = new \Papaya\Ui\Dialog\Field\Group('Group Caption');
    $group->collection($this->getCollectionMock($dialog));
    $group->fields($fields);
    $this->assertTrue($group->validate());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field\Group::validate
  */
  public function testValidateUsingCachedResultExpectingTrue() {
    $dialog = $this
      ->getMockBuilder(\PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $fields = $this->createMock(\PapayaUiDialogFields::class);
    $fields
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(TRUE));
    $group = new \Papaya\Ui\Dialog\Field\Group('Group Caption');
    $group->collection($this->getCollectionMock($dialog));
    $group->fields($fields);
    $group->validate();
    $this->assertTrue($group->validate());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field\Group::validate
  */
  public function testValidateWithoutFieldsExpectingTrue() {
    $dialog = $this
      ->getMockBuilder(\PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $group = new \Papaya\Ui\Dialog\Field\Group('Group Caption');
    $group->collection($this->getCollectionMock($dialog));
    $this->assertTrue($group->validate());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field\Group::validate
  */
  public function testValidateExpectingFalse() {
    $dialog = $this
      ->getMockBuilder(\PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $fields = $this->createMock(\PapayaUiDialogFields::class);
    $fields
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(FALSE));
    $group = new \Papaya\Ui\Dialog\Field\Group('Group Caption');
    $group->collection($this->getCollectionMock($dialog));
    $group->fields($fields);
    $this->assertFalse($group->validate());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field\Group::validate
  */
  public function testValidateWithoutDialogExpectingFalse() {
    $group = new \Papaya\Ui\Dialog\Field\Group('Group Caption');
    $this->assertTrue($group->validate());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field\Group::collect
  */
  public function testCollect() {
    $dialog = $this
      ->getMockBuilder(\PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $fields = $this->createMock(\PapayaUiDialogFields::class);
    $fields
      ->expects($this->once())
      ->method('collect')
      ->will($this->returnValue(TRUE));
    $group = new \Papaya\Ui\Dialog\Field\Group('Group Caption');
    $group->collection($this->getCollectionMock($dialog));
    $group->fields($fields);
    $this->assertTrue($group->collect());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field\Group::collect
  */
  public function testCollectWithoutDialog() {
    $group = new \Papaya\Ui\Dialog\Field\Group('Group Caption');
    $group->collection($this->createMock(\PapayaUiDialogFields::class));
    $this->assertFalse($group->collect());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field\Group::appendTo
  */
  public function testAppendTo() {
    $fields = $this->createMock(\PapayaUiDialogFields::class);
    $fields
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    $fields
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(1));
    $group = new \Papaya\Ui\Dialog\Field\Group('Group Caption');
    $group->collection($this->createMock(\PapayaUiDialogFields::class));
    $group->fields($fields);
    $this->assertEquals(
      /** @lang XML */
      '<field-group caption="Group Caption"/>',
      $group->getXml()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field\Group::appendTo
  */
  public function testAppendToWithId() {
    $fields = $this->createMock(\PapayaUiDialogFields::class);
    $fields
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    $fields
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(1));
    $group = new \Papaya\Ui\Dialog\Field\Group('Group Caption');
    $group->setId('sampleId');
    $group->collection($this->createMock(\PapayaUiDialogFields::class));
    $group->fields($fields);
    $this->assertEquals(
      /** @lang XML */
      '<field-group caption="Group Caption" id="sampleId"/>',
      $group->getXml()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field\Group::appendTo
  */
  public function testAppendToWithoutFields() {
    $document = new \Papaya\Xml\Document();
    $node = $document->createElement('sample');
    $document->appendChild($node);
    $group = new \Papaya\Ui\Dialog\Field\Group('Group Caption');
    $group->appendTo($node);
    $this->assertEquals(
      /** @lang XML */
      '<sample/>',
      $document->saveXML($node)
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field\Group::collection
  */
  public function testCollectionGetAfterSet() {
    $owner = $this->createMock(\PapayaUiDialog::class);
    $papaya = $this->mockPapaya()->application();
    $collection = $this->createMock(\Papaya\Ui\Control\Collection::class);
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
    $fields = $this->createMock(\PapayaUiDialogFields::class);
    $fields
      ->expects($this->once())
      ->method('owner')
      ->with($owner);
    $item = new \Papaya\Ui\Dialog\Field\Group('Group Caption');
    $item->fields($fields);
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
