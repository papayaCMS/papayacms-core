<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaUiDialogFieldTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialogField::setCaption
  */
  public function testSetCaption() {
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setCaption('Test Caption');
    $this->assertAttributeEquals(
      'Test Caption', '_caption', $field
    );
  }

  /**
  * @covers PapayaUiDialogField::getCaption
  */
  public function testGetCaption() {
    $string = $this->getMock('PapayaUiString', array('__toString'), array('.'));
    $string
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('Test Caption'));
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setCaption($string);
    $this->assertEquals(
      'Test Caption', $field->getCaption()
    );
  }

  /**
  * @covers PapayaUiDialogField::setCaption
  */
  public function testSetCaptionExpectingException() {
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $this->setExpectedException('UnexpectedValueException');
    $field->setCaption(array());
  }

  /**
  * @covers PapayaUiDialogField::setHint
  */
  public function testSetHint() {
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setHint('Test Hint');
    $this->assertAttributeEquals(
      'Test Hint', '_hint', $field
    );
  }

  /**
  * @covers PapayaUiDialogField::getHint
  */
  public function testGetHint() {
    $string = $this->getMock('PapayaUiString', array('__toString'), array('.'));
    $string
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('Test Hint'));
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setHint($string);
    $this->assertEquals(
      'Test Hint', $field->getHint()
    );
  }

  /**
  * @covers PapayaUiDialogField::setHint
  */
  public function testSetHintExpectingException() {
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $this->setExpectedException('UnexpectedValueException');
    $field->setHint(array());
  }

  /**
  * @covers PapayaUiDialogField::description
  */
  public function testDescriptionGetAfterSet() {
    $description = $this->getMock('PapayaUiDialogElementDescription');
    $field = new PapayaUiDialogField_TestProxy();
    $field->description($description);
    $this->assertSame($description, $field->description());
  }

  /**
  * @covers PapayaUiDialogField::description
  */
  public function testDescriptionImplicitCreate() {
    $field = new PapayaUiDialogField_TestProxy();
    $field->papaya($papaya = $this->mockPapaya()->application());
    $this->assertInstanceOf(
      'PapayaUiDialogElementDescription', $description = $field->description()
    );
    $this->assertSame($papaya, $description->papaya());
  }

  /**
  * @covers PapayaUiDialogField::setId
  */
  public function testSetId() {
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setId('sample_id');
    $this->assertAttributeEquals(
      'sample_id', '_id', $field
    );
  }

  /**
  * @covers PapayaUiDialogField::getId
  */
  public function testGetId() {
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setId('sample_id');
    $this->assertEquals(
      'sample_id', $field->getId()
    );
  }

  /**
  * @covers PapayaUiDialogField::setName
  */
  public function testSetName() {
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setName('sample');
    $this->assertAttributeEquals(
      'sample', '_name', $field
    );
  }

  /**
  * @covers PapayaUiDialogField::getName
  */
  public function testGetName() {
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setName('sample');
    $this->assertEquals(
      'sample', $field->getName()
    );
  }

  /**
  * @covers PapayaUiDialogField::setDefaultValue
  */
  public function testSetDefaultValue() {
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setDefaultValue(42);
    $this->assertAttributeEquals(
      42, '_defaultValue', $field
    );
  }

  /**
  * @covers PapayaUiDialogField::getDefaultValue
  */
  public function testGetDefaultValueAfterSetDefaultValue() {
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setDefaultValue(42);
    $this->assertEquals(
      42, $field->getDefaultValue()
    );
  }

  /**
  * @covers PapayaUiDialogField::setFilter
  */
  public function testSetFilter() {
    $filter = $this->getMock('PapayaFilter', array('validate', 'filter'));
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setFilter($filter);
    $this->assertAttributeEquals(
      $filter, '_filter', $field
    );
  }

  /**
  * @covers PapayaUiDialogField::getFilter
  */
  public function testGetFilterWhileMandatoryTrue() {
    $filter = $this->getMock('PapayaFilter', array('validate', 'filter'));
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setFilter($filter);
    $field->setMandatory(TRUE);
    $this->assertSame(
      $filter, $field->getFilter()
    );
  }

  /**
  * @covers PapayaUiDialogField::getFilter
  */
  public function testGetFilterWhileMandatoryFalse() {
    $filter = $this->getMock('PapayaFilter', array('validate', 'filter'));
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setFilter($filter);
    $field->setMandatory(FALSE);
    $this->assertInstanceOf(
      'PapayaFilterLogicalOr', $field->getFilter()
    );
  }

  /**
  * @covers PapayaUiDialogField::getFilter
  */
  public function testGetFilterWithoutAnyFilter() {
    $filter = $this->getMock('PapayaFilter', array('validate', 'filter'));
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $this->assertNull(
      $field->getFilter()
    );
  }

  /**
  * @covers PapayaUiDialogField::validate
  * @covers PapayaUiDialogField::_validateFilter
  */
  public function testValidate() {
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $this->assertTrue($field->validate());
  }

  /**
  * @covers PapayaUiDialogField::validate
  */
  public function testValidateCachedResult() {
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->validate();
    $this->assertTrue($field->validate());
  }

  /**
  * @covers PapayaUiDialogField::validate
  * @covers PapayaUiDialogField::_validateFilter
  */
  public function testValidateWithFilter() {
    $filter = $this->getMock('PapayaFilter', array('validate', 'filter'));
    $filter
      ->expects($this->once())
      ->method('validate')
      ->withAnyParameters()
      ->will($this->returnValue(TRUE));
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setMandatory(TRUE);
    $field->setFilter($filter);
    $this->assertTrue($field->validate());
  }

  /**
  * @covers PapayaUiDialogField::validate
  * @covers PapayaUiDialogField::_validateFilter
  */
  public function testValidateNotMandatoryWithEmptyValueIsInvalidForFilterButReturnTrue() {
    $filter = $this->getMock('PapayaFilter', array('validate', 'filter'));
    $filter
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(FALSE));
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setFilter($filter);
    $this->assertTrue($field->validate());
  }

  /**
  * @covers PapayaUiDialogField::validate
  * @covers PapayaUiDialogField::_validateFilter
  */
  public function testValidateExpectingError() {
    $dialog = $this->getMock(
      'PapayaUiDialog',
      array('appendTo', 'isSubmitted', 'execute', 'handleValidationFailure'),
      array(new stdClass())
    );
    $dialog
      ->expects($this->once())
      ->method('handleValidationFailure')
      ->with(
        $this->isInstanceOf('PapayaFilterException'),
        $this->isInstanceOf('PapayaUiDialogField')
      );
    $filter = $this->getMock('PapayaFilter', array('validate', 'filter'));
    $filter
      ->expects($this->once())
      ->method('validate')
      ->withAnyParameters()
      ->will($this->returnCallback(array($this, 'throwFilterExceptionCallback')));
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->setMandatory(TRUE);
    $field->setFilter($filter);
    $this->assertFalse($field->validate());
  }

  /**
  * @covers PapayaUiDialogField::handleValidationFailure
  */
  public function testHandleValidationFailure() {
    $dialog = $this->getMock(
      'PapayaUiDialog',
      array('appendTo', 'isSubmitted', 'execute', 'handleValidationFailure'),
      array(new stdClass())
    );
    $dialog
      ->expects($this->once())
      ->method('handleValidationFailure')
      ->with(
        $this->isInstanceOf('Exception'),
        $this->isInstanceOf('PapayaUiDialogField')
      );
    $exception = new LogicException();
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->handleValidationFailure($exception);
    $this->assertAttributeSame(
      FALSE, '_validationResult', $field
    );
  }

  /**
  * @covers PapayaUiDialogField::collect
  */
  public function testCollectWithoutDialog() {
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $this->assertTrue($field->collect());
  }

  /**
  * @covers PapayaUiDialogField::collect
  */
  public function testCollectWithoutName() {
    $dialog = $this->getMock(
      'PapayaUiDialog',
      array('appendTo', 'isSubmitted', 'execute'),
      array(new stdClass())
    );
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $this->assertTrue($field->collect());
  }

  /**
  * @covers PapayaUiDialogField::collect
  */
  public function testCollect() {
    $data = $this->getMock('PapayaRequestParameters', array('set'));
    $data
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('foo'), $this->identicalTo('42'));
    $dialog = $this->getMock(
      'PapayaUiDialog',
      array('appendTo', 'isSubmitted', 'execute', 'parameters', 'data'),
      array(new stdClass())
    );
    $dialog
      ->expects($this->exactly(2))
      ->method('parameters')
      ->will($this->returnValue(new PapayaRequestParameters(array('foo' => '42'))));
    $dialog
      ->expects($this->once())
      ->method('data')
      ->will($this->returnValue($data));
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->setName('foo');
    $this->assertTrue($field->collect());
  }

  /**
  * @covers PapayaUiDialogField::collect
  */
  public function testCollectWithDefaultValue() {
    $data = $this->getMock('PapayaRequestParameters', array('set'));
    $data
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('foo'), $this->identicalTo(42));
    $dialog = $this->getMock(
      'PapayaUiDialog',
      array('appendTo', 'isSubmitted', 'execute', 'parameters', 'data'),
      array(new stdClass())
    );
    $dialog
      ->expects($this->exactly(2))
      ->method('parameters')
      ->will($this->returnValue(new PapayaRequestParameters(array('foo' => '42'))));
    $dialog
      ->expects($this->once())
      ->method('data')
      ->will($this->returnValue($data));
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->setName('foo');
    $field->setDefaultValue(21);
    $this->assertTrue($field->collect());
  }


  /**
  * @covers PapayaUiDialogField::collect
  */
  public function testCollectWithObjectDefaultValue() {
    $data = $this->getMock('PapayaRequestParameters', array('set'));
    $data
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('foo'), $this->identicalTo('42'));
    $dialog = $this->getMock(
      'PapayaUiDialog',
      array('appendTo', 'isSubmitted', 'execute', 'parameters', 'data'),
      array(new stdClass())
    );
    $dialog
      ->expects($this->exactly(2))
      ->method('parameters')
      ->will($this->returnValue(new PapayaRequestParameters(array('foo' => 42))));
    $dialog
      ->expects($this->once())
      ->method('data')
      ->will($this->returnValue($data));
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->setName('foo');
    $field->setDefaultValue(new stdClass());
    $this->assertTrue($field->collect());
  }

  /**
  * @covers PapayaUiDialogField::collect
  */
  public function testCollectWithFilter() {
    $filter = $this->getMock('PapayaFilter', array('validate', 'filter'));
    $filter
      ->expects($this->once())
      ->method('filter')
      ->with($this->identicalTo('42'))
      ->will($this->returnValue(42));
    $data = $this->getMock('PapayaRequestParameters', array('set'));
    $data
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('foo'), $this->identicalTo(42));
    $dialog = $this->getMock(
      'PapayaUiDialog',
      array('appendTo', 'isSubmitted', 'execute', 'parameters', 'data'),
      array(new stdClass())
    );
    $dialog
      ->expects($this->exactly(2))
      ->method('parameters')
      ->will($this->returnValue(new PapayaRequestParameters(array('foo' => '42'))));
    $dialog
      ->expects($this->once())
      ->method('data')
      ->will($this->returnValue($data));
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->setName('foo');
    $field->setFilter($filter);
    $this->assertTrue($field->collect());
  }

  /**
  * @covers PapayaUiDialogField::collect
  */
  public function testCollectWithFilterFailedAndDefaultValue() {
    $filter = $this->getMock('PapayaFilter', array('validate', 'filter'));
    $filter
      ->expects($this->once())
      ->method('filter')
      ->with($this->identicalTo(''))
      ->will($this->returnValue(NULL));
    $data = $this->getMock('PapayaRequestParameters', array('set'));
    $data
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('foo'), $this->identicalTo(21));
    $dialog = $this->getMock(
      'PapayaUiDialog',
      array('appendTo', 'isSubmitted', 'execute', 'parameters', 'data'),
      array(new stdClass())
    );
    $dialog
      ->expects($this->exactly(2))
      ->method('parameters')
      ->will($this->returnValue(new PapayaRequestParameters(array('foo' => ''))));
    $dialog
      ->expects($this->once())
      ->method('data')
      ->will($this->returnValue($data));
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->setName('foo');
    $field->setFilter($filter);
    $field->setDefaultValue(21);
    $this->assertTrue($field->collect());
  }

  /**
  * @covers PapayaUiDialogField::getCurrentValue
  */
  public function testGetCurrentValueExpectingNull() {
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $this->assertNull($field->getCurrentValue());
  }

  /**
  * @covers PapayaUiDialogField::getCurrentValue
  */
  public function testGetCurrentValueAfterSetDefaultValue() {
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setDefaultValue('test');
    $this->assertEquals('test', $field->getCurrentValue());
  }

  /**
  * @covers PapayaUiDialogField::getCurrentValue
  */
  public function testGetCurrentValueAfterCheckingDialog() {
    $dialog = $this->getMock(
      'PapayaUiDialog',
      array('appendTo', 'isSubmitted', 'execute', 'parameters', 'data'),
      array(new stdClass())
    );
    $dialog
      ->expects($this->once())
      ->method('parameters')
      ->will($this->returnValue(new PapayaRequestParameters()));
    $dialog
      ->expects($this->once())
      ->method('data')
      ->will($this->returnValue(new PapayaRequestParameters()));
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->setName('foo');
    $field->setDefaultValue('test');
    $this->assertEquals('test', $field->getCurrentValue());
  }

  /**
  * @covers PapayaUiDialogField::getCurrentValue
  */
  public function testGetCurrentValueFromDialogParameters() {
    $dialog = $this->getMock(
      'PapayaUiDialog',
      array('appendTo', 'isSubmitted', 'execute', 'parameters'),
      array(new stdClass())
    );
    $dialog
      ->expects($this->exactly(2))
      ->method('parameters')
      ->will($this->returnValue(new PapayaRequestParameters(array('foo' => 42))));
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->setName('foo');
    $this->assertEquals(42, $field->getCurrentValue());
  }

  /**
  * @covers PapayaUiDialogField::getCurrentValue
  */
  public function testGetCurrentValueFromDialogData() {
    $dialog = $this->getMock(
      'PapayaUiDialog',
      array('appendTo', 'isSubmitted', 'execute', 'parameters', 'data'),
      array(new stdClass())
    );
    $dialog
      ->expects($this->once())
      ->method('parameters')
      ->will($this->returnValue(new PapayaRequestParameters()));
    $dialog
      ->expects($this->exactly(2))
      ->method('data')
      ->will($this->returnValue(new PapayaRequestParameters(array('foo' => 42))));
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->setName('foo');
    $this->assertEquals(42, $field->getCurrentValue());
  }

  /**
  * @covers PapayaUiDialogField::getCurrentValue
  */
  public function testGetCurrentValueFromDialogDataValueNotFound() {
    $dialog = $this->getMock(
      'PapayaUiDialog',
      array('appendTo', 'isSubmitted', 'execute', 'parameters', 'data'),
      array(new stdClass())
    );
    $dialog
      ->expects($this->once())
      ->method('parameters')
      ->will($this->returnValue(new PapayaRequestParameters()));
    $dialog
      ->expects($this->once())
      ->method('data')
      ->will($this->returnValue(new PapayaRequestParameters()));
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->setName('foo');
    $this->assertNull($field->getCurrentValue());
  }

  /**
  * @covers PapayaUiDialogField::getCurrentValue
  */
  public function testGetCurrentValueFromDialogDataValueIsNull() {
    $dialog = $this->getMock(
      'PapayaUiDialog',
      array('appendTo', 'isSubmitted', 'execute', 'parameters', 'data'),
      array(new stdClass())
    );
    $dialog
      ->expects($this->once())
      ->method('parameters')
      ->will($this->returnValue(new PapayaRequestParameters()));
    $dialog
      ->expects($this->exactly(2))
      ->method('data')
      ->will($this->returnValue(new PapayaRequestParameters(array('foo' => NULL))));
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->setName('foo');
    $this->assertNull($field->getCurrentValue());
  }

  /**
  * @covers PapayaUiDialogField::_appendFieldTo
  */
  public function testAppendFieldTo() {
    $dialog = $this->getMock(
      'PapayaUiDialog',
      array('appendTo', 'isSubmitted', 'execute', 'parameters', 'data'),
      array(new stdClass())
    );
    $dialog
      ->expects($this->once())
      ->method('isSubmitted')
      ->will($this->returnValue(FALSE));
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $dom = new PapayaXmlDocument();
    $node = $dom->createElement('sample');
    $dom->appendChild($node);
    $field->appendTo($node);
    $this->assertEquals(
      '<sample>'.
        '<field class="DialogField_TestProxy" error="no"/>'.
        '</sample>',
      $dom->saveXml($node)
    );
  }

  /**
  * @covers PapayaUiDialogField::_appendFieldTo
  */
  public function testAppendFieldToWithFullData() {
    $description = $this->getMock('PapayaUiDialogElementDescription');
    $description
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf('PapayaXmlElement'));
    $dialog = $this->getMock(
      'PapayaUiDialog',
      array('appendTo', 'isSubmitted', 'execute', 'parameters', 'data'),
      array(new stdClass())
    );
    $dialog
      ->expects($this->once())
      ->method('isSubmitted')
      ->will($this->returnValue(TRUE));
    $field = new PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->description($description);
    $field->setCaption('sample_caption');
    $field->setHint('sample_hint');
    $field->setId('sample_id');
    $dom = new PapayaXmlDocument();
    $node = $dom->createElement('sample');
    $dom->appendChild($node);
    $field->appendTo($node);
    $this->assertEquals(
      '<sample>'.
        '<field caption="sample_caption" class="DialogField_TestProxy"'.
        ' error="no" hint="sample_hint" id="sample_id"/>'.
        '</sample>',
      $dom->saveXml($node)
    );
  }

  /**
  * @covers PapayaUiDialogField::_appendFieldTo
  */
  public function testAppendFieldToWithDisabledStatus() {
    $field = new PapayaUiDialogField_TestProxy();
    $field->setDisabled(TRUE);
    $dom = new PapayaXmlDocument();
    $node = $dom->createElement('sample');
    $dom->appendChild($node);
    $field->appendTo($node);
    $this->assertEquals(
      '<sample>'.
        '<field class="DialogField_TestProxy" error="no" disabled="yes"/>'.
      '</sample>',
      $dom->saveXml($node)
    );
  }


  /**
  * @covers PapayaUiDialogField::_appendFieldTo
  */
  public function testAppendFieldToWithMandatoryStatus() {
    $field = new PapayaUiDialogField_TestProxy();
    $field->setMandatory(TRUE);
    $dom = new PapayaXmlDocument();
    $node = $dom->createElement('sample');
    $dom->appendChild($node);
    $field->appendTo($node);
    $this->assertEquals(
      '<sample>'.
        '<field class="DialogField_TestProxy" error="no" mandatory="yes"/>'.
      '</sample>',
      $dom->saveXml($node)
    );
  }

  /**
  * @covers PapayaUiDialogField::_getFieldClass
  */
  public function testGetFieldClass() {
    $field = new PapayaUiDialogField_TestProxy();
    $this->assertEquals(
      'DialogField_TestProxy', $field->_getFieldClass()
    );
  }

  /**
  * @covers PapayaUiDialogField::_getFieldClass
  */
  public function testGetFieldClassWithPrefix() {
    $field = new PapayaUiDialogField_TestProxy();
    $this->assertEquals(
      'TestProxy', $field->_getFieldClass('PapayaUiDialogField_')
    );
  }

  /**
  * @covers PapayaUiDialogField::setDisabled
  * @covers PapayaUiDialogField::getDisabled
  */
  public function testGetDisabledAfterSetDisabled() {
    $field = new PapayaUiDialogField_TestProxy();
    $field->setDisabled(TRUE);
    $this->assertTrue($field->getDisabled());
  }

  /**
  * @covers PapayaUiDialogField::setMandatory
  * @covers PapayaUiDialogField::getMandatory
  */
  public function testGetMandatoryAfterSetMandatory() {
    $field = new PapayaUiDialogField_TestProxy();
    $field->setMandatory(TRUE);
    $this->assertTrue($field->getMandatory());
  }

  /*************************
  * Callbacks
  *************************/

  public function throwFilterExceptionCallback() {
    throw $this->getMock('PapayaFilterException');
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

class PapayaUiDialogField_TestProxy extends PapayaUiDialogField {

  public function appendTo(PapayaXmlElement $parent) {
    $this->_appendFieldTo($parent);
  }
  public function _getFieldClass($prefix = 'PapayaUi') {
    return parent::_getFieldClass($prefix);
  }
}