<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaUiDialogElementTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogElement::collect
  */
  public function testCollectWithDialog() {
    $dialog = $this->getDialogMock();
    $element = new PapayaUiDialogElement_TestProxy();
    $element->collection($this->getCollectionMock($dialog));
    $this->assertTrue($element->collect());
  }

  /**
  * @covers PapayaUiDialogElement::collect
  */
  public function testCollectWithoutDialog() {
    $element = new PapayaUiDialogElement_TestProxy();
    $element->collection($this->getCollectionMock());
    $this->assertFalse($element->collect());
  }

  /**
  * @covers PapayaUiDialogElement::_getParameterName
  * @dataProvider provideKeysForGetParameterName
  */
  public function testGetParameterName($expected, $keys) {
    $element = new PapayaUiDialogElement_TestProxy();
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $element->papaya($application);
    $element->collection($this->getCollectionMock());
    $this->assertEquals(
      $expected, $element->_getParameterName($keys)
    );
  }

  /**
  * @covers PapayaUiDialogElement::_getParameterName
  */
  public function testGetParameterNameWithDialog() {
    $dialog = $this->getDialogMock();
    $dialog
      ->expects($this->once())
      ->method('parameterGroup')
      ->will($this->returnValue('group'));
    $element = new PapayaUiDialogElement_TestProxy();
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $element->papaya($application);
    $element->collection($this->getCollectionMock($dialog));
    $this->assertEquals(
      'group[param]', $element->_getParameterName('param')
    );
  }

  /**
  * @covers PapayaUiDialogElement::hasDialog
  */
  public function testHasDialogExpectingTrue() {
    $dialog = $this->getDialogMock();
    $element = new PapayaUiDialogElement_TestProxy();
    $element->collection($this->getCollectionMock($dialog));
    $this->assertTrue($element->hasDialog());
  }

  /**
  * @covers PapayaUiDialogElement::hasDialog
  */
  public function testHasDialogWithoutAttachedCollectionExpectingFalse() {
    $element = new PapayaUiDialogElement_TestProxy();
    $this->assertFalse($element->hasDialog());
  }

  /**
  * @covers PapayaUiDialogElement::hasDialog
  */
  public function testHasDialogWithoutAttachedDialogExpectingFalse() {
    $element = new PapayaUiDialogElement_TestProxy();
    $element->collection($this->getCollectionMock());
    $this->assertFalse($element->hasDialog());
  }

  /**
  * @covers PapayaUiDialogElement::getDialog
  */
  public function testGetDialog() {
    $dialog = $this->getDialogMock();
    $element = new PapayaUiDialogElement_TestProxy();
    $element->collection($this->getCollectionMock($dialog));
    $this->assertSame($dialog, $element->getDialog());
  }

  /**
  * @covers PapayaUiDialogElement::getDialog
  */
  public function testGetDialogExpectingNull() {
    $element = new PapayaUiDialogElement_TestProxy();
    $this->assertNull($element->getDialog());
  }

  /*****************************
  * Data Provider
  *****************************/

  public static function provideKeysForGetParameterName() {
    return array(
      array('test', 'test'),
      array('group[test]', array('group', 'test')),
      array('group[subgroup][test]', array('group', 'subgroup', 'test'))
    );
  }

  /*****************************
  * Mocks
  *****************************/

  private function getDialogMock() {
    return $this->getMock(
      'PapayaUiDialog',
      array('isSubmitted', 'execute', 'appendTo', 'parameterGroup'),
      array(new stdClass())
    );
  }

  public function getCollectionMock($owner = NULL) {
    $collection = $this->getMock('PapayaUiDialogElements');
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

class PapayaUiDialogElement_TestProxy extends PapayaUiDialogElement {

  public function appendTo(PapayaXmlElement $parent) {
  }

  public function _getParameterName($key, $withGroup = TRUE) {
    return parent::_getParameterName($key, $withGroup);
  }
}
