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
  * @covers \Papaya\Ui\Dialog\Field::setCaption
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
  * @covers \Papaya\Ui\Dialog\Field::getCaption
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
  * @covers \Papaya\Ui\Dialog\Field::setCaption
  */
  public function testSetCaptionExpectingException() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $this->expectException(UnexpectedValueException::class);
    /** @noinspection PhpParamsInspection */
    $field->setCaption(array());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field::setHint
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
  * @covers \Papaya\Ui\Dialog\Field::getHint
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
  * @covers \Papaya\Ui\Dialog\Field::setHint
  */
  public function testSetHintExpectingException() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $this->expectException(UnexpectedValueException::class);
    /** @noinspection PhpParamsInspection */
    $field->setHint(array());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field::description
  */
  public function testDescriptionGetAfterSet() {
    $description = $this->createMock(\Papaya\Ui\Dialog\Element\Description::class);
    $field = new \PapayaUiDialogField_TestProxy();
    $field->description($description);
    $this->assertSame($description, $field->description());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field::description
  */
  public function testDescriptionImplicitCreate() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->papaya($papaya = $this->mockPapaya()->application());
    $this->assertInstanceOf(
      \Papaya\Ui\Dialog\Element\Description::class, $description = $field->description()
    );
    $this->assertSame($papaya, $description->papaya());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field::setId
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
  * @covers \Papaya\Ui\Dialog\Field::getId
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
  * @covers \Papaya\Ui\Dialog\Field::setName
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
  * @covers \Papaya\Ui\Dialog\Field::getName
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
  * @covers \Papaya\Ui\Dialog\Field::setDefaultValue
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
  * @covers \Papaya\Ui\Dialog\Field::getDefaultValue
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
  * @covers \Papaya\Ui\Dialog\Field::setFilter
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
  * @covers \Papaya\Ui\Dialog\Field::getFilter
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
  * @covers \Papaya\Ui\Dialog\Field::getFilter
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
  * @covers \Papaya\Ui\Dialog\Field::getFilter
  */
  public function testGetFilterWithoutAnyFilter() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $this->assertNull(
      $field->getFilter()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field::validate
  * @covers \Papaya\Ui\Dialog\Field::_validateFilter
  */
  public function testValidate() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $this->assertTrue($field->validate());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field::validate
  */
  public function testValidateCachedResult() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->validate();
    $this->assertTrue($field->validate());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field::validate
  * @covers \Papaya\Ui\Dialog\Field::_validateFilter
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
  * @covers \Papaya\Ui\Dialog\Field::validate
  * @covers \Papaya\Ui\Dialog\Field::_validateFilter
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
  * @covers \Papaya\Ui\Dialog\Field::validate
  * @covers \Papaya\Ui\Dialog\Field::_validateFilter
  */
  public function testValidateExpectingError() {
    $dialog = $this
      ->getMockBuilder(\Papaya\Ui\Dialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->once())
      ->method('handleValidationFailure')
      ->with(
        $this->isInstanceOf(\Papaya\Filter\Exception::class),
        $this->isInstanceOf(\Papaya\Ui\Dialog\Field::class)
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
  * @covers \Papaya\Ui\Dialog\Field::handleValidationFailure
  */
  public function testHandleValidationFailure() {
    $dialog = $this
      ->getMockBuilder(\Papaya\Ui\Dialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->once())
      ->method('handleValidationFailure')
      ->with(
        $this->isInstanceOf(Exception::class),
        $this->isInstanceOf(\Papaya\Ui\Dialog\Field::class)
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
  * @covers \Papaya\Ui\Dialog\Field::collect
  */
  public function testCollectWithoutDialog() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $this->assertTrue($field->collect());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field::collect
  */
  public function testCollectWithoutName() {
    $dialog = $this
      ->getMockBuilder(\Papaya\Ui\Dialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $this->assertTrue($field->collect());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field::collect
  */
  public function testCollect() {
    $data = $this->createMock(\Papaya\Request\Parameters::class);
    $data
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('foo'), $this->identicalTo('42'));
    $dialog = $this
      ->getMockBuilder(\Papaya\Ui\Dialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->exactly(2))
      ->method('parameters')
      ->will($this->returnValue(new \Papaya\Request\Parameters(array('foo' => '42'))));
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
  * @covers \Papaya\Ui\Dialog\Field::collect
  */
  public function testCollectWithDefaultValue() {
    $data = $this->createMock(\Papaya\Request\Parameters::class);
    $data
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('foo'), $this->identicalTo(42));
    $dialog = $this
      ->getMockBuilder(\Papaya\Ui\Dialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->exactly(2))
      ->method('parameters')
      ->will($this->returnValue(new \Papaya\Request\Parameters(array('foo' => '42'))));
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
  * @covers \Papaya\Ui\Dialog\Field::collect
  */
  public function testCollectWithObjectDefaultValue() {
    $data = $this->createMock(\Papaya\Request\Parameters::class);
    $data
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('foo'), $this->identicalTo('42'));
    $dialog = $this
      ->getMockBuilder(\Papaya\Ui\Dialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->exactly(2))
      ->method('parameters')
      ->will($this->returnValue(new \Papaya\Request\Parameters(array('foo' => 42))));
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
  * @covers \Papaya\Ui\Dialog\Field::collect
  */
  public function testCollectWithFilter() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Filter $filter */
    $filter = $this->createMock(\Papaya\Filter::class);
    $filter
      ->expects($this->once())
      ->method('filter')
      ->with($this->identicalTo('42'))
      ->will($this->returnValue(42));
    $data = $this->createMock(\Papaya\Request\Parameters::class);
    $data
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('foo'), $this->identicalTo(42));
    $dialog = $this
      ->getMockBuilder(\Papaya\Ui\Dialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->exactly(2))
      ->method('parameters')
      ->will($this->returnValue(new \Papaya\Request\Parameters(array('foo' => '42'))));
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
  * @covers \Papaya\Ui\Dialog\Field::collect
  */
  public function testCollectWithFilterFailedAndDefaultValue() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Filter $filter */
    $filter = $this->createMock(\Papaya\Filter::class);
    $filter
      ->expects($this->once())
      ->method('filter')
      ->with($this->identicalTo(''))
      ->will($this->returnValue(NULL));
    $data = $this->createMock(\Papaya\Request\Parameters::class);
    $data
      ->expects($this->once())
      ->method('set')
      ->with($this->equalTo('foo'), $this->identicalTo(21));
    $dialog = $this
      ->getMockBuilder(\Papaya\Ui\Dialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->exactly(2))
      ->method('parameters')
      ->will($this->returnValue(new \Papaya\Request\Parameters(array('foo' => ''))));
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
  * @covers \Papaya\Ui\Dialog\Field::getCurrentValue
  */
  public function testGetCurrentValueExpectingNull() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $this->assertNull($field->getCurrentValue());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field::getCurrentValue
  */
  public function testGetCurrentValueAfterSetDefaultValue() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock());
    $field->setDefaultValue('test');
    $this->assertEquals('test', $field->getCurrentValue());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field::getCurrentValue
  */
  public function testGetCurrentValueAfterCheckingDialog() {
    $dialog = $this
      ->getMockBuilder(\Papaya\Ui\Dialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->once())
      ->method('parameters')
      ->will($this->returnValue(new \Papaya\Request\Parameters()));
    $dialog
      ->expects($this->once())
      ->method('data')
      ->will($this->returnValue(new \Papaya\Request\Parameters()));
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->setName('foo');
    $field->setDefaultValue('test');
    $this->assertEquals('test', $field->getCurrentValue());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field::getCurrentValue
  */
  public function testGetCurrentValueFromDialogParameters() {
    $dialog = $this
      ->getMockBuilder(\Papaya\Ui\Dialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->exactly(2))
      ->method('parameters')
      ->will($this->returnValue(new \Papaya\Request\Parameters(array('foo' => 42))));
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->setName('foo');
    $this->assertEquals(42, $field->getCurrentValue());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field::getCurrentValue
  */
  public function testGetCurrentValueFromDialogData() {
    $dialog = $this
      ->getMockBuilder(\Papaya\Ui\Dialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->once())
      ->method('parameters')
      ->will($this->returnValue(new \Papaya\Request\Parameters()));
    $dialog
      ->expects($this->exactly(2))
      ->method('data')
      ->will($this->returnValue(new \Papaya\Request\Parameters(array('foo' => 42))));
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->setName('foo');
    $this->assertEquals(42, $field->getCurrentValue());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field::getCurrentValue
  */
  public function testGetCurrentValueFromDialogDataValueNotFound() {
    $dialog = $this
      ->getMockBuilder(\Papaya\Ui\Dialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->once())
      ->method('parameters')
      ->will($this->returnValue(new \Papaya\Request\Parameters()));
    $dialog
      ->expects($this->once())
      ->method('data')
      ->will($this->returnValue(new \Papaya\Request\Parameters()));
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->setName('foo');
    $this->assertNull($field->getCurrentValue());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field::getCurrentValue
  */
  public function testGetCurrentValueFromDialogDataValueIsNull() {
    $dialog = $this
      ->getMockBuilder(\Papaya\Ui\Dialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->once())
      ->method('parameters')
      ->will($this->returnValue(new \Papaya\Request\Parameters()));
    $dialog
      ->expects($this->exactly(2))
      ->method('data')
      ->will($this->returnValue(new \Papaya\Request\Parameters(array('foo' => NULL))));
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $field->setName('foo');
    $this->assertNull($field->getCurrentValue());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field::_appendFieldTo
  */
  public function testAppendFieldTo() {
    $dialog = $this
      ->getMockBuilder(\Papaya\Ui\Dialog::class)
      ->setConstructorArgs(array(new stdClass()))
      ->getMock();
    $dialog
      ->expects($this->once())
      ->method('isSubmitted')
      ->will($this->returnValue(FALSE));
    $field = new \PapayaUiDialogField_TestProxy();
    $field->collection($this->getCollectionMock($dialog));
    $document = new \Papaya\Xml\Document();
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
  * @covers \Papaya\Ui\Dialog\Field::_appendFieldTo
  */
  public function testAppendFieldToWithFullData() {
    $description = $this->createMock(\Papaya\Ui\Dialog\Element\Description::class);
    $description
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    $dialog = $this
      ->getMockBuilder(\Papaya\Ui\Dialog::class)
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
    $document = new \Papaya\Xml\Document();
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
  * @covers \Papaya\Ui\Dialog\Field::_appendFieldTo
  */
  public function testAppendFieldToWithDisabledStatus() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->setDisabled(TRUE);
    $document = new \Papaya\Xml\Document();
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
  * @covers \Papaya\Ui\Dialog\Field::_appendFieldTo
  */
  public function testAppendFieldToWithMandatoryStatus() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->setMandatory(TRUE);
    $document = new \Papaya\Xml\Document();
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
  * @covers \Papaya\Ui\Dialog\Field::_getFieldClass
  */
  public function testGetFieldClass() {
    $field = new \PapayaUiDialogField_TestProxy();
    $this->assertEquals(
      'DialogField_TestProxy', $field->_getFieldClass()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field::_getFieldClass
  */
  public function testGetFieldClassWithPrefix() {
    $field = new \PapayaUiDialogField_TestProxy();
    $this->assertEquals(
      'TestProxy', $field->_getFieldClass('PapayaUiDialogField_')
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field::setDisabled
  * @covers \Papaya\Ui\Dialog\Field::getDisabled
  */
  public function testGetDisabledAfterSetDisabled() {
    $field = new \PapayaUiDialogField_TestProxy();
    $field->setDisabled(TRUE);
    $this->assertTrue($field->getDisabled());
  }

  /**
  * @covers \Papaya\Ui\Dialog\Field::setMandatory
  * @covers \Papaya\Ui\Dialog\Field::getMandatory
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
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\Ui\Dialog\Fields
   */
  public function getCollectionMock($owner = NULL) {
    $collection = $this->createMock(\Papaya\Ui\Dialog\Fields::class);
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

class PapayaUiDialogField_TestProxy extends \Papaya\Ui\Dialog\Field {

  public function appendTo(\Papaya\Xml\Element $parent) {
    $this->_appendFieldTo($parent);
  }
  public function _getFieldClass($prefix = 'PapayaUi') {
    return parent::_getFieldClass($prefix);
  }
}
