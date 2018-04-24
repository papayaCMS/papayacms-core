<?php
require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaUiDialogFieldButtonsTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldButtons::buttons
  */
  public function testFieldsGetImplicitCreate() {
    $field = new PapayaUiDialogFieldButtons();
    $this->assertInstanceOf(
      'PapayaUiDialogButtons', $field->buttons()
    );
  }

  /**
  * @covers PapayaUiDialogFieldButtons::buttons
  */
  public function testFieldsGetImplicitCreateWithDialog() {
    $dialog = $this->getMock(
      'PapayaUiDialog', array('isSubmitted', 'appendTo', 'execute'), array(new stdClass())
    );
    $field = new PapayaUiDialogFieldButtons();
    $field->collection($this->getCollectionMock($dialog));
    $this->assertSame(
      $dialog, $field->buttons()->owner()
    );
  }

  /**
  * @covers PapayaUiDialogFieldButtons::buttons
  */
  public function testFieldsSet() {
    $dialog = $this->getMock(
      'PapayaUiDialog', array('isSubmitted', 'appendTo', 'execute'), array(new stdClass())
    );
    $field = new PapayaUiDialogFieldButtons();
    $field->collection($this->getCollectionMock($dialog));
    $buttons = $this->getMock('PapayaUiDialogButtons', array('owner'));
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
  * @covers PapayaUiDialogFieldButtons::buttons
  */
  public function testFieldsGetAfterSet() {
    $dialog = $this->getMock(
      'PapayaUiDialog', array('isSubmitted', 'appendTo', 'execute'), array(new stdClass())
    );
    $field = new PapayaUiDialogFieldButtons();
    $field->collection($this->getCollectionMock($dialog));
    $buttons = $this->getMock('PapayaUiDialogButtons', array('owner'));
    $buttons
      ->expects($this->once())
      ->method('owner')
      ->with($this->equalTo($dialog));
    $this->assertSame(
      $buttons, $field->buttons($buttons)
    );
  }

  /**
  * @covers PapayaUiDialogFieldButtons::validate
  */
  public function testValidateExpectingTrue() {
    $field = new PapayaUiDialogFieldButtons();
    $this->assertTrue($field->validate());
  }

  /**
  * @covers PapayaUiDialogFieldButtons::collect
  */
  public function testCollect() {
    $dialog = $this->getMock(
      'PapayaUiDialog', array('isSubmitted', 'appendTo', 'execute'), array(new stdClass())
    );
    $buttons = $this->getMock('PapayaUiDialogButtons', array('collect'));
    $buttons
      ->expects($this->once())
      ->method('collect')
      ->will($this->returnValue(TRUE));
    $field = new PapayaUiDialogFieldButtons();
    $field->collection($this->getCollectionMock($dialog));
    $field->buttons($buttons);
    $this->assertTrue($field->collect());
  }

  /**
  * @covers PapayaUiDialogFieldButtons::collect
  */
  public function testCollectWithoutDialog() {
    $field = new PapayaUiDialogFieldButtons();
    $field->collection($this->createMock(PapayaUiDialogButtons::class));
    $this->assertFalse($field->collect());
  }

  /**
  * @covers PapayaUiDialogFieldButtons::appendTo
  */
  public function testAppendTo() {
    $buttons = $this->getMock('PapayaUiDialogButtons', array('appendTo', 'count'));
    $buttons
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $buttons
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(1));
    $field = new PapayaUiDialogFieldButtons();
    $field->collection($this->createMock(PapayaUiDialogButtons::class));
    $field->buttons($buttons);
    $this->assertEquals(
      '<field class="DialogFieldButtons" error="no"><buttons/></field>',
      $field->getXml()
    );
  }

  /**
  * @covers PapayaUiDialogFieldButtons::appendTo
  */
  public function testAppendToWithId() {
    $buttons = $this->getMock('PapayaUiDialogButtons', array('appendTo', 'count'));
    $buttons
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $buttons
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(1));
    $field = new PapayaUiDialogFieldButtons();
    $field->setId('sampleId');
    $field->collection($this->createMock(PapayaUiDialogButtons::class));
    $field->buttons($buttons);
    $this->assertEquals(
      '<field class="DialogFieldButtons" error="no" id="sampleId"><buttons/></field>',
      $field->getXml()
    );
  }

  /**
  * @covers PapayaUiDialogFieldButtons::appendTo
  */
  public function testAppendToWithoutFields() {
    $field = new PapayaUiDialogFieldButtons();
    $this->assertEquals(
      '',
      $field->getXml()
    );
  }

  /*************************
  * Mocks
  *************************/

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
