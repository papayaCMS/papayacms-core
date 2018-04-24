<?php
require_once __DIR__.'/../../../../../../bootstrap.php';

class PapayaUiDialogFieldGroupButtonsTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogFieldGroupButtons::__construct
  */
  public function testConstructor() {
    $group = new PapayaUiDialogFieldGroupButtons('Group Caption');
    $this->assertEquals(
      'Group Caption', $group->getCaption()
    );
  }

  /**
  * @covers PapayaUiDialogFieldGroupButtons::buttons
  */
  public function testFieldsGetImplicitCreate() {
    $group = new PapayaUiDialogFieldGroupButtons('Group Caption');
    $this->assertInstanceOf(
      'PapayaUiDialogButtons', $group->buttons()
    );
  }

  /**
  * @covers PapayaUiDialogFieldGroupButtons::buttons
  */
  public function testFieldsGetImplicitCreateWithDialog() {
    $dialog = $this->getMock(
      'PapayaUiDialog', array('isSubmitted', 'appendTo', 'execute'), array(new stdClass())
    );
    $group = new PapayaUiDialogFieldGroupButtons('Group Caption');
    $group->collection($this->getCollectionMock($dialog));
    $this->assertSame(
      $dialog, $group->buttons()->owner()
    );
  }

  /**
  * @covers PapayaUiDialogFieldGroupButtons::buttons
  */
  public function testFieldsSet() {
    $dialog = $this->getMock(
      'PapayaUiDialog', array('isSubmitted', 'appendTo', 'execute'), array(new stdClass())
    );
    $group = new PapayaUiDialogFieldGroupButtons('Group Caption');
    $group->collection($this->getCollectionMock($dialog));
    $buttons = $this->getMock('PapayaUiDialogButtons', array('owner'));
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
  * @covers PapayaUiDialogFieldGroupButtons::buttons
  */
  public function testFieldsGetAfterSet() {
    $dialog = $this->getMock(
      'PapayaUiDialog', array('isSubmitted', 'appendTo', 'execute'), array(new stdClass())
    );
    $group = new PapayaUiDialogFieldGroupButtons('Group Caption');
    $group->collection($this->getCollectionMock($dialog));
    $buttons = $this->getMock('PapayaUiDialogButtons', array('owner'));
    $buttons
      ->expects($this->once())
      ->method('owner')
      ->with($this->equalTo($dialog));
    $this->assertSame(
      $buttons, $group->buttons($buttons)
    );
  }

  /**
  * @covers PapayaUiDialogFieldGroupButtons::validate
  */
  public function testValidateExpectingTrue() {
    $dialog = $this->getMock(
      'PapayaUiDialog', array('isSubmitted', 'appendTo', 'execute'), array(new stdClass())
    );
    $group = new PapayaUiDialogFieldGroupButtons('Group Caption');
    $this->assertTrue($group->validate());
  }

  /**
  * @covers PapayaUiDialogFieldGroupButtons::validate
  */
  public function testValidateWithoutDialogExpectingFalse() {
    $group = new PapayaUiDialogFieldGroupButtons('Group Caption');
    $this->assertTrue($group->validate());
  }

  /**
  * @covers PapayaUiDialogFieldGroupButtons::collect
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
    $group = new PapayaUiDialogFieldGroupButtons('Group Caption');
    $group->collection($this->getCollectionMock($dialog));
    $group->buttons($buttons);
    $this->assertTrue($group->collect());
  }

  /**
  * @covers PapayaUiDialogFieldGroupButtons::collect
  */
  public function testCollectWithoutDialog() {
    $group = new PapayaUiDialogFieldGroupButtons('Group Caption');
    $group->collection($this->getMock('PapayaUiDialogButtons'));
    $this->assertFalse($group->collect());
  }

  /**
  * @covers PapayaUiDialogFieldGroupButtons::appendTo
  */
  public function testAppendTo() {
    $buttons = $this->getMock('PapayaUiDialogButtons');
    $buttons
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $buttons
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(1));
    $group = new PapayaUiDialogFieldGroupButtons('Group Caption');
    $group->collection($this->getMock('PapayaUiDialogButtons'));
    $group->buttons($buttons);
    $this->assertEquals(
      '<field-group caption="Group Caption"/>',
      $group->getXml()
    );
  }

  /**
  * @covers PapayaUiDialogFieldGroupButtons::appendTo
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
    $group = new PapayaUiDialogFieldGroupButtons('Group Caption');
    $group->setId('sampleId');
    $group->collection($this->getMock('PapayaUiDialogButtons'));
    $group->buttons($buttons);
    $this->assertEquals(
      '<field-group caption="Group Caption" id="sampleId"/>',
      $group->getXml()
    );
  }

  /**
  * @covers PapayaUiDialogFieldGroupButtons::appendTo
  */
  public function testAppendToWithoutFields() {
    $dom = new PapayaXmlDocument();
    $node = $dom->createElement('sample');
    $dom->appendChild($node);
    $group = new PapayaUiDialogFieldGroupButtons('Group Caption');
    $group->appendTo($node);
    $this->assertEquals(
      '<sample/>',
      $dom->saveXml($node)
    );
  }

  /**
  * @covers PapayaUiDialogFieldGroupButtons::collection
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
    $buttons = $this->getMock('PapayaUiDialogButtons');
    $buttons
      ->expects($this->once())
      ->method('owner')
      ->with($owner);
    $item = new PapayaUiDialogFieldGroupButtons('Group Caption');
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
