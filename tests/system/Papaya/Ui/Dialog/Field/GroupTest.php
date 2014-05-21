<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaUiDialogFieldGroupTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldGroup::__construct
  */
  public function testConstructor() {
    $group = new PapayaUiDialogFieldGroup('Group Caption');
    $this->assertAttributeEquals(
      'Group Caption', '_caption', $group
    );
  }

  /**
  * @covers PapayaUiDialogFieldGroup::fields
  */
  public function testFieldsGetImplicitCreate() {
    $group = new PapayaUiDialogFieldGroup('Group Caption');
    $group->collection($this->getMock('PapayaUiDialogFields'));
    $this->assertInstanceOf(
      'PapayaUiDialogFields', $group->fields()
    );
  }

  /**
  * @covers PapayaUiDialogFieldGroup::fields
  */
  public function testFieldsGetImplicitCreateWithDialog() {
    $dialog = $this->getMock(
      'PapayaUiDialog', array('isSubmitted', 'appendTo', 'execute'), array(new stdClass())
    );
    $group = new PapayaUiDialogFieldGroup('Group Caption');
    $group->collection($this->getCollectionMock($dialog));
    $this->assertSame(
      $dialog, $group->fields()->owner()
    );
  }

  /**
  * @covers PapayaUiDialogFieldGroup::fields
  */
  public function testFieldsSet() {
    $dialog = $this->getMock(
      'PapayaUiDialog', array('isSubmitted', 'appendTo', 'execute'), array(new stdClass())
    );
    $group = new PapayaUiDialogFieldGroup('Group Caption');
    $group->collection($this->getCollectionMock($dialog));
    $fields = $this->getMock('PapayaUiDialogFields', array('owner'));
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
  * @covers PapayaUiDialogFieldGroup::fields
  */
  public function testFieldsGetAfterSet() {
    $dialog = $this->getMock(
      'PapayaUiDialog', array('isSubmitted', 'appendTo', 'execute'), array(new stdClass())
    );
    $group = new PapayaUiDialogFieldGroup('Group Caption');
    $group->collection($this->getCollectionMock($dialog));
    $fields = $this->getMock('PapayaUiDialogFields', array('owner'));
    $fields
      ->expects($this->once())
      ->method('owner')
      ->with($this->equalTo($dialog));
    $this->assertSame(
      $fields, $group->fields($fields)
    );
  }

  /**
  * @covers PapayaUiDialogFieldGroup::validate
  */
  public function testValidateExpectingTrue() {
    $dialog = $this->getMock(
      'PapayaUiDialog', array('isSubmitted', 'appendTo', 'execute'), array(new stdClass())
    );
    $fields = $this->getMock('PapayaUiDialogFields', array('validate'));
    $fields
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(TRUE));
    $group = new PapayaUiDialogFieldGroup('Group Caption');
    $group->collection($this->getCollectionMock($dialog));
    $group->fields($fields);
    $this->assertTrue($group->validate());
  }

  /**
  * @covers PapayaUiDialogFieldGroup::validate
  */
  public function testValidateUsingCachedResultExpectingTrue() {
    $dialog = $this->getMock(
      'PapayaUiDialog', array('isSubmitted', 'appendTo', 'execute'), array(new stdClass())
    );
    $fields = $this->getMock('PapayaUiDialogFields', array('validate'));
    $fields
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(TRUE));
    $group = new PapayaUiDialogFieldGroup('Group Caption');
    $group->collection($this->getCollectionMock($dialog));
    $group->fields($fields);
    $group->validate();
    $this->assertTrue($group->validate());
  }

  /**
  * @covers PapayaUiDialogFieldGroup::validate
  */
  public function testValidateWithoutFieldsExpectingTrue() {
    $dialog = $this->getMock(
      'PapayaUiDialog', array('isSubmitted', 'appendTo', 'execute'), array(new stdClass())
    );
    $group = new PapayaUiDialogFieldGroup('Group Caption');
    $group->collection($this->getCollectionMock($dialog));
    $this->assertTrue($group->validate());
  }

  /**
  * @covers PapayaUiDialogFieldGroup::validate
  */
  public function testValidateExpectingFalse() {
    $dialog = $this->getMock(
      'PapayaUiDialog', array('isSubmitted', 'appendTo', 'execute'), array(new stdClass())
    );
    $fields = $this->getMock('PapayaUiDialogFields', array('validate'));
    $fields
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(FALSE));
    $group = new PapayaUiDialogFieldGroup('Group Caption');
    $group->collection($this->getCollectionMock($dialog));
    $group->fields($fields);
    $this->assertFalse($group->validate());
  }

  /**
  * @covers PapayaUiDialogFieldGroup::validate
  */
  public function testValidateWithoutDialogExpectingFalse() {
    $group = new PapayaUiDialogFieldGroup('Group Caption');
    $this->assertTrue($group->validate());
  }

  /**
  * @covers PapayaUiDialogFieldGroup::collect
  */
  public function testCollect() {
    $dialog = $this->getMock(
      'PapayaUiDialog', array('isSubmitted', 'appendTo', 'execute'), array(new stdClass())
    );
    $fields = $this->getMock('PapayaUiDialogFields', array('collect'));
    $fields
      ->expects($this->once())
      ->method('collect')
      ->will($this->returnValue(TRUE));
    $group = new PapayaUiDialogFieldGroup('Group Caption');
    $group->collection($this->getCollectionMock($dialog));
    $group->fields($fields);
    $this->assertTrue($group->collect());
  }

  /**
  * @covers PapayaUiDialogFieldGroup::collect
  */
  public function testCollectWithoutDialog() {
    $group = new PapayaUiDialogFieldGroup('Group Caption');
    $group->collection($this->getMock('PapayaUiDialogFields'));
    $this->assertFalse($group->collect());
  }

  /**
  * @covers PapayaUiDialogFieldGroup::appendTo
  */
  public function testAppendTo() {
    $fields = $this->getMock('PapayaUiDialogFields');
    $fields
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $fields
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(1));
    $group = new PapayaUiDialogFieldGroup('Group Caption');
    $group->collection($this->getMock('PapayaUiDialogFields'));
    $group->fields($fields);
    $this->assertEquals(
      '<field-group caption="Group Caption"/>',
      $group->getXml()
    );
  }

  /**
  * @covers PapayaUiDialogFieldGroup::appendTo
  */
  public function testAppendToWithId() {
    $fields = $this->getMock('PapayaUiDialogFields', array('appendTo', 'count'));
    $fields
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $fields
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(1));
    $group = new PapayaUiDialogFieldGroup('Group Caption');
    $group->setId('sampleId');
    $group->collection($this->getMock('PapayaUiDialogFields'));
    $group->fields($fields);
    $this->assertEquals(
      '<field-group caption="Group Caption" id="sampleId"/>',
      $group->getXml()
    );
  }

  /**
  * @covers PapayaUiDialogFieldGroup::appendTo
  */
  public function testAppendToWithoutFields() {
    $dom = new PapayaXmlDocument();
    $node = $dom->createElement('sample');
    $dom->appendChild($node);
    $group = new PapayaUiDialogFieldGroup('Group Caption');
    $group->appendTo($node);
    $this->assertEquals(
      '<sample/>',
      $dom->saveXml($node)
    );
  }

  /**
  * @covers PapayaUiDialogFieldGroup::collection
  */
  public function testCollectionGetAfterSet() {
    $owner = $this->getMock('PapayaUiDialog');
    $papaya = $this->mockPapaya()->application();
    $collection = $this->getMock('PapayaUiControlCollection');
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
    $fields = $this->getMock('PapayaUiDialogFields');
    $fields
      ->expects($this->once())
      ->method('owner')
      ->with($owner);
    $item = new PapayaUiDialogFieldGroup('Group Caption');
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

  public function getCollectionMock($owner = NULL) {
    $collection = $this->getMock('PapayaUiDialogFields');
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
