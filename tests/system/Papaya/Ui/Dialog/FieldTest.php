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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaUiDialogFieldTest extends \PapayaTestCase {

  /**
  * @covers \PapayaUiDialogField::setCaption
  */
  public function testSetCaption() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setCaption('Test Caption');
    $this->assertAttributeEquals(
      'Test Caption', '_caption', $field
    );
  }

  /**
  * @covers \PapayaUiDialogField::getCaption
  */
  public function testGetCaption() {
    $string = $this
      ->getMockBuilder(\PapayaUiString::class)
      ->setConstructorArgs(array('.'))
      ->getMock();
    $string
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('Test Caption'));
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setCaption($string);
    $this->assertEquals(
      'Test Caption', $field->getCaption()
    );
  }

  /**
  * @covers \PapayaUiDialogField::setCaption
  */
  public function testSetCaptionExpectingException() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $this->expectException(UnexpectedValueException::class);
    /** @noinspection PhpParamsInspection */
    $field->setCaption(array());
  }

  /**
  * @covers \PapayaUiDialogField::setHint
  */
  public function testSetHint() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setHint('Test Hint');
    $this->assertAttributeEquals(
      'Test Hint', '_hint', $field
    );
  }

  /**
  * @covers \PapayaUiDialogField::getHint
  */
  public function testGetHint() {
    $string = $this
      ->getMockBuilder(\PapayaUiString::class)
      ->setConstructorArgs(array('.'))
      ->getMock();
    $string
      ->expects($this->once())
      ->method('__toString')
      ->will($this->returnValue('Test Hint'));
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setHint($string);
    $this->assertEquals(
      'Test Hint', $field->getHint()
    );
  }

  /**
  * @covers \PapayaUiDialogField::setHint
  */
  public function testSetHintExpectingException() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $this->expectException(UnexpectedValueException::class);
    /** @noinspection PhpParamsInspection */
    $field->setHint(array());
  }

  /**
  * @covers \PapayaUiDialogField::description
  */
  public function testDescriptionGetAfterSet() {
    $description = $this->createMock(\PapayaUiDialogElementDescription::class);
    $field = new \PapayaUiDialogField_TestProxy();
    $field->description($description);
    $this->assertSame($description, $field->description());
  }

  /**
  * @covers \PapayaUiDialogField::description
  */
  public function testDescriptionImplicitCreate() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->papaya($papaya = $this->mockPapaya()->application());
    $this->assertInstanceOf(
      \PapayaUiDialogElementDescription::class, $description = $field->description()
    );
    $this->assertSame($papaya, $description->papaya());
  }

  /**
  * @covers \PapayaUiDialogField::setId
  */
  public function testSetId() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setId('sample_id');
    $this->assertAttributeEquals(
      'sample_id', '_id', $field
    );
  }

  /**
  * @covers \PapayaUiDialogField::getId
  */
  public function testGetId() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setId('sample_id');
    $this->assertEquals(
      'sample_id', $field->getId()
    );
  }

  /**
  * @covers \PapayaUiDialogField::setName
  */
  public function testSetName() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setName('sample');
    $this->assertAttributeEquals(
      'sample', '_name', $field
    );
  }

  /**
  * @covers \PapayaUiDialogField::getName
  */
  public function testGetName() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setName('sample');
    $this->assertEquals(
      'sample', $field->getName()
    );
  }

  /**
  * @covers \PapayaUiDialogField::setDefaultValue
  */
  public function testSetDefaultValue() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setDefaultValue(42);
    $this->assertAttributeEquals(
      42, '_defaultValue', $field
    );
  }

  /**
  * @covers \PapayaUiDialogField::getDefaultValue
  */
  public function testGetDefaultValueAfterSetDefaultValue() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setDefaultValue(42);
    $this->assertEquals(
      42, $field->getDefaultValue()
    );
  }

  /**
  * @covers \PapayaUiDialogField::setFilter
  */
  public function testSetFilter() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Filter $filter */
    $filter = $this->createMock(\Papaya\Filter::class);
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setFilter($filter);
    $this->assertAttributeEquals(
      $filter, '_filter', $field
    );
  }

  /**
  * @covers \PapayaUiDialogField::getFilter
  */
  public function testGetFilterWhileMandatoryTrue() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Filter $filter */
    $filter = $this->createMock(\Papaya\Filter::class);
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setFilter($filter);
    $field->setMandatory(TRUE);
    $this->assertSame(
      $filter, $field->getFilter()
    );
  }

  /**
  * @covers \PapayaUiDialogField::getFilter
  */
  public function testGetFilterWhileMandatoryFalse() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Filter $filter */
    $filter = $this->createMock(\Papaya\Filter::class);
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setFilter($filter);
    $field->setMandatory(FALSE);
    $this->assertInstanceOf(
      \Papaya\Filter\LogicalOr::class, $field->getFilter()
    );
  }

  /**
  * @covers \PapayaUiDialogField::getFilter
  */
  public function testGetFilterWithoutAnyFilter() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $this->assertNull(
      $field->getFilter()
    );
  }

  /**
  * @covers \PapayaUiDialogField::validate
  * @covers \PapayaUiDialogField::_validateFilter
  */
  public function testValidate() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $this->assertTrue($field->validate());
  }

  /**
  * @covers \PapayaUiDialogField::validate
  */
  public function testValidateCachedResult() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->validate();
    $this->assertTrue($field->validate());
  }

  /**
  * @covers \PapayaUiDialogField::validate
  * @covers \PapayaUiDialogField::_validateFilter
  */
  public function testValidateWithFilter() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Filter $filter */
    $filter = $this->createMock(\Papaya\Filter::class);
    $filter
      ->expects($this->once())
      ->method('validate')
      ->withAnyParameters()
      ->will($this->returnValue(TRUE));
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setMandatory(TRUE);
    $field->setFilter($filter);
    $this->assertTrue($field->validate());
  }

  /**
  * @covers \PapayaUiDialogField::validate
  * @covers \PapayaUiDialogField::_validateFilter
  */
  public function testValidateNotMandatoryWithEmptyValueIsInvalidForFilterButReturnTrue() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Filter $filter */
    $filter = $this->createMock(\Papaya\Filter::class);
    $filter
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(FALSE));
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setFilter($filter);
    $this->assertTrue($field->validate());
  }

  /**
  * @covers \PapayaUiDialogField::validate
  * @covers \PapayaUiDialogField::_validateFilter
  */
  public function testValidateExpectingError() {
    $dialog = $this
      ->getMockBuilder(\PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->once())
      ->method('handleValidationFailure')
      ->with(
        $this->isInstanceOf(\Papaya\Filter\Exception::class),
        $this->isInstanceOf(\PapayaUiDialogField::class)
      );
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Filter $filter */
    $filter = $this->createMock(\Papaya\Filter::class);
    $filter
      ->expects($this->once())
      ->method('validate')
      ->withAnyParameters()
      ->will($this->returnCallback(array($this, 'throwFilterExceptionCallback')));
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->setMandatory(TRUE);
    $field->setFilter($filter);
    $this->assertFalse($field->validate());
  }

  /**
  * @covers \PapayaUiDialogField::handleValidationFailure
  */
  public function testHandleValidationFailure() {
    $dialog = $this
      ->getMockBuilder(\PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->once())
      ->method('handleValidationFailure')
      ->with(
        $this->isInstanceOf(Exception::class),
        $this->isInstanceOf(\PapayaUiDialogField::class)
      );
    $exception = new LogicException();
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->handleValidationFailure($exception);
    $this->assertAttributeSame(
      FALSE, '_validationResult', $field
    );
  }

  /**
  * @covers \PapayaUiDialogField::collect
  */
  public function testCollectWithoutDialog() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $this->assertTrue($field->collect());
  }

  /**
  * @covers \PapayaUiDialogField::collect
  */
  public function testCollectWithoutName() {
    $dialog = $this
      ->getMockBuilder(\PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $this->assertTrue($field->collect());
  }

  /**
  * @covers \PapayaUiDialogField::collect
  */
  public function testCollect() {
    $data = $this->createMock(\PapayaRequestParameters::class);
    $data
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('foo'), $this->identicalTo('42'));
    $dialog = $this
      ->getMockBuilder(\PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->exactly(2))
      ->method('parameters')
      ->will($this->returnValue(new \PapayaRequestParameters(array('foo' => '42'))));
    $dialog
      ->expects($this->once())
      ->method('data')
      ->will($this->returnValue($data));
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->setName('foo');
    $this->assertTrue($field->collect());
  }

  /**
  * @covers \PapayaUiDialogField::collect
  */
  public function testCollectWithDefaultValue() {
    $data = $this->createMock(\PapayaRequestParameters::class);
    $data
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('foo'), $this->identicalTo(42));
    $dialog = $this
      ->getMockBuilder(\PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->exactly(2))
      ->method('parameters')
      ->will($this->returnValue(new \PapayaRequestParameters(array('foo' => '42'))));
    $dialog
      ->expects($this->once())
      ->method('data')
      ->will($this->returnValue($data));
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->setName('foo');
    $field->setDefaultValue(21);
    $this->assertTrue($field->collect());
  }


  /**
  * @covers \PapayaUiDialogField::collect
  */
  public function testCollectWithObjectDefaultValue() {
    $data = $this->createMock(\PapayaRequestParameters::class);
    $data
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('foo'), $this->identicalTo('42'));
    $dialog = $this
      ->getMockBuilder(\PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->exactly(2))
      ->method('parameters')
      ->will($this->returnValue(new \PapayaRequestParameters(array('foo' => 42))));
    $dialog
      ->expects($this->once())
      ->method('data')
      ->will($this->returnValue($data));
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->setName('foo');
    $field->setDefaultValue(new stdClass());
    $this->assertTrue($field->collect());
  }

  /**
  * @covers \PapayaUiDialogField::collect
  */
  public function testCollectWithFilter() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Filter $filter */
    $filter = $this->createMock(\Papaya\Filter::class);
    $filter
      ->expects($this->once())
      ->method('filter')
      ->with($this->identicalTo('42'))
      ->will($this->returnValue(42));
    $data = $this->createMock(\PapayaRequestParameters::class);
    $data
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('foo'), $this->identicalTo(42));
    $dialog = $this
      ->getMockBuilder(\PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->exactly(2))
      ->method('parameters')
      ->will($this->returnValue(new \PapayaRequestParameters(array('foo' => '42'))));
    $dialog
      ->expects($this->once())
      ->method('data')
      ->will($this->returnValue($data));
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->setName('foo');
    $field->setFilter($filter);
    $this->assertTrue($field->collect());
  }

  /**
  * @covers \PapayaUiDialogField::collect
  */
  public function testCollectWithFilterFailedAndDefaultValue() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Filter $filter */
    $filter = $this->createMock(\Papaya\Filter::class);
    $filter
      ->expects($this->once())
      ->method('filter')
      ->with($this->identicalTo(''))
      ->will($this->returnValue(NULL));
    $data = $this->createMock(\PapayaRequestParameters::class);
    $data
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('foo'), $this->identicalTo(21));
    $dialog = $this
      ->getMockBuilder(\PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->exactly(2))
      ->method('parameters')
      ->will($this->returnValue(new \PapayaRequestParameters(array('foo' => ''))));
    $dialog
      ->expects($this->once())
      ->method('data')
      ->will($this->returnValue($data));
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->setName('foo');
    $field->setFilter($filter);
    $field->setDefaultValue(21);
    $this->assertTrue($field->collect());
  }

  /**
  * @covers \PapayaUiDialogField::getCurrentValue
  */
  public function testGetCurrentValueExpectingNull() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $this->assertNull($field->getCurrentValue());
  }

  /**
  * @covers \PapayaUiDialogField::getCurrentValue
  */
  public function testGetCurrentValueAfterSetDefaultValue() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setDefaultValue('test');
    $this->assertEquals('test', $field->getCurrentValue());
  }

  /**
  * @covers \PapayaUiDialogField::getCurrentValue
  */
  public function testGetCurrentValueAfterCheckingDialog() {
    $dialog = $this
      ->getMockBuilder(\PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->once())
      ->method('parameters')
      ->will($this->returnValue(new \PapayaRequestParameters()));
    $dialog
      ->expects($this->once())
      ->method('data')
      ->will($this->returnValue(new \PapayaRequestParameters()));
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->setName('foo');
    $field->setDefaultValue('test');
    $this->assertEquals('test', $field->getCurrentValue());
  }

  /**
  * @covers \PapayaUiDialogField::getCurrentValue
  */
  public function testGetCurrentValueFromDialogParameters() {
    $dialog = $this
      ->getMockBuilder(\PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->exactly(2))
      ->method('parameters')
      ->will($this->returnValue(new \PapayaRequestParameters(array('foo' => 42))));
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->setName('foo');
    $this->assertEquals(42, $field->getCurrentValue());
  }

  /**
  * @covers \PapayaUiDialogField::getCurrentValue
  */
  public function testGetCurrentValueFromDialogData() {
    $dialog = $this
      ->getMockBuilder(\PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->once())
      ->method('parameters')
      ->will($this->returnValue(new \PapayaRequestParameters()));
    $dialog
      ->expects($this->exactly(2))
      ->method('data')
      ->will($this->returnValue(new \PapayaRequestParameters(array('foo' => 42))));
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->setName('foo');
    $this->assertEquals(42, $field->getCurrentValue());
  }

  /**
  * @covers \PapayaUiDialogField::getCurrentValue
  */
  public function testGetCurrentValueFromDialogDataValueNotFound() {
    $dialog = $this
      ->getMockBuilder(\PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->once())
      ->method('parameters')
      ->will($this->returnValue(new \PapayaRequestParameters()));
    $dialog
      ->expects($this->once())
      ->method('data')
      ->will($this->returnValue(new \PapayaRequestParameters()));
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->setName('foo');
    $this->assertNull($field->getCurrentValue());
  }

  /**
  * @covers \PapayaUiDialogField::getCurrentValue
  */
  public function testGetCurrentValueFromDialogDataValueIsNull() {
    $dialog = $this
      ->getMockBuilder(\PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->once())
      ->method('parameters')
      ->will($this->returnValue(new \PapayaRequestParameters()));
    $dialog
      ->expects($this->exactly(2))
      ->method('data')
      ->will($this->returnValue(new \PapayaRequestParameters(array('foo' => NULL))));
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->setName('foo');
    $this->assertNull($field->getCurrentValue());
  }

  /**
  * @covers \PapayaUiDialogField::_appendFieldTo
  */
  public function testAppendFieldTo() {
    $dialog = $this
      ->getMockBuilder(\PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->once())
      ->method('isSubmitted')
      ->will($this->returnValue(FALSE));
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $document = new \PapayaXmlDocument();
    $node = $document->createElement('sample');
    $document->appendChild($node);
    $field->appendTo($node);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample>
        <field class="DialogField_TestProxy" error="no"/>
        </sample>',
      $document->saveXML($node)
    );
  }

  /**
  * @covers \PapayaUiDialogField::_appendFieldTo
  */
  public function testAppendFieldToWithFullData() {
    $description = $this->createMock(\PapayaUiDialogElementDescription::class);
    $description
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\PapayaXmlElement::class));
    $dialog = $this
      ->getMockBuilder(\PapayaUiDialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->once())
      ->method('isSubmitted')
      ->will($this->returnValue(TRUE));
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->description($description);
    $field->setCaption('sample_caption');
    $field->setHint('sample_hint');
    $field->setId('sample_id');
    $document = new \PapayaXmlDocument();
    $node = $document->createElement('sample');
    $document->appendChild($node);
    $field->appendTo($node);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample>
        <field caption="sample_caption" class="DialogField_TestProxy"
         error="no" hint="sample_hint" id="sample_id"/>
        </sample>',
      $document->saveXML($node)
    );
  }

  /**
  * @covers \PapayaUiDialogField::_appendFieldTo
  */
  public function testAppendFieldToWithDisabledStatus() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->setDisabled(TRUE);
    $document = new \PapayaXmlDocument();
    $node = $document->createElement('sample');
    $document->appendChild($node);
    $field->appendTo($node);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample>
        <field class="DialogField_TestProxy" error="no" disabled="yes"/>
      </sample>',
      $document->saveXML($node)
    );
  }


  /**
  * @covers \PapayaUiDialogField::_appendFieldTo
  */
  public function testAppendFieldToWithMandatoryStatus() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->setMandatory(TRUE);
    $document = new \PapayaXmlDocument();
    $node = $document->createElement('sample');
    $document->appendChild($node);
    $field->appendTo($node);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<sample>
        <field class="DialogField_TestProxy" error="no" mandatory="yes"/>
      </sample>',
      $document->saveXML($node)
    );
  }

  /**
  * @covers \PapayaUiDialogField::_getFieldClass
  */
  public function testGetFieldClass() {
    $field = new \PapayaUiDialogField_TestProxy();
    $this->assertEquals(
      'DialogField_TestProxy', $field->_getFieldClass()
    );
  }

  /**
  * @covers \PapayaUiDialogField::_getFieldClass
  */
  public function testGetFieldClassWithPrefix() {
    $field = new \PapayaUiDialogField_TestProxy();
    $this->assertEquals(
      'TestProxy', $field->_getFieldClass('PapayaUiDialogField_')
    );
  }

  /**
  * @covers \PapayaUiDialogField::setDisabled
  * @covers \PapayaUiDialogField::getDisabled
  */
  public function testGetDisabledAfterSetDisabled() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->setDisabled(TRUE);
    $this->assertTrue($field->getDisabled());
  }

  /**
  * @covers \PapayaUiDialogField::setMandatory
  * @covers \PapayaUiDialogField::getMandatory
  */
  public function testGetMandatoryAfterSetMandatory() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->setMandatory(TRUE);
    $this->assertTrue($field->getMandatory());
  }

  /*************************
  * Callbacks
  *************************/

  public function throwFilterExceptionCallback() {
    throw $this->createMock(\Papaya\Filter\Exception::class);
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

class PapayaUiDialogField_TestProxy extends \PapayaUiDialogField {

  public function appendTo(\PapayaXmlElement $parent) {
    $this->_appendFieldTo($parent);
  }
  public function _getFieldClass($prefix = 'PapayaUi') {
    return parent::_getFieldClass($prefix);
  }
}
