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

require_once __DIR__.'/../../../bootstrap.php';

class PapayaUiDialogTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\Ui\Dialog::__construct
  */
  public function testConstructor() {
    $owner = new stdClass();
    $dialog = new \Papaya\Ui\Dialog($owner);
    $this->assertAttributeSame(
      $owner, '_owner', $dialog
    );
  }

  /**
   * @covers \Papaya\Ui\Dialog::getMethodString
   * @dataProvider provideMethodsAndStringRepresentations
   * @param int $expected
   * @param string $method
   */
  public function testGetMethodString($expected, $method) {
    $dialog = new \PapayaUiDialog_TestProxy(new stdClass());
    $dialog->parameterMethod($method);
    $this->assertEquals($expected, $dialog->getMethodString());
  }

  /**
  * @covers \Papaya\Ui\Dialog::hiddenValues
  */
  public function testHiddenValuesSet() {
    $values = $this->createMock(\Papaya\Request\Parameters::class);
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $dialog->hiddenValues($values);
    $this->assertAttributeSame(
      $values, '_hiddenValues', $dialog
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::hiddenValues
  */
  public function testHiddenValuesGetAfterSet() {
    $values = $this->createMock(\Papaya\Request\Parameters::class);
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $this->assertSame(
      $values, $dialog->hiddenValues($values)
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::hiddenValues
  */
  public function testHiddenValuesGetImplicitCreate() {
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $this->assertInstanceOf(
      \Papaya\Request\Parameters::class, $dialog->hiddenValues()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::hiddenFields
  */
  public function testHiddenFieldsSet() {
    $fields = $this->createMock(\Papaya\Request\Parameters::class);
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $dialog->hiddenFields($fields);
    $this->assertAttributeSame(
      $fields, '_hiddenFields', $dialog
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::hiddenFields
  */
  public function testHiddenFieldsGetAfterSet() {
    $fields = $this->createMock(\Papaya\Request\Parameters::class);
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $this->assertSame(
      $fields, $dialog->hiddenFields($fields)
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::hiddenFields
  */
  public function testHiddenFieldsGetImplicitCreate() {
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $this->assertInstanceOf(
      \Papaya\Request\Parameters::class, $dialog->hiddenFields()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::action
  */
  public function testActionSet() {
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $dialog->action('sample');
    $this->assertAttributeEquals(
      'sample', '_action', $dialog
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::action
  */
  public function testActionGetAfterSet() {
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $this->assertEquals(
      'sample', $dialog->action('sample')
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::action
  */
  public function testActionGetWithoutSet() {
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $dialog->papaya($this->mockPapaya()->application());
    $this->assertEquals(
      'http://www.test.tld/test.html', $dialog->action()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::tokens
  */
  public function testTokensSet() {
    $tokens = $this->createMock(\Papaya\Ui\Tokens::class);
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $dialog->tokens($tokens);
    $this->assertAttributeSame(
      $tokens, '_tokens', $dialog
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::tokens
  */
  public function testTokensGetAfterSet() {
    $tokens = $this->createMock(\Papaya\Ui\Tokens::class);
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $this->assertSame(
      $tokens, $dialog->tokens($tokens)
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::tokens
  */
  public function testTokensGetImplicitCreate() {
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $this->assertInstanceOf(
      \Papaya\Ui\Tokens::class, $dialog->tokens()
    );
  }

  /**
   * @covers \Papaya\Ui\Dialog::appendHidden
   * @dataProvider provideHiddenDataAndResult
   * @param string|NULL $group
   * @param array $values
   * @param string $expected
   */
  public function testAppendHidden($group, $values, $expected) {
    $dialog = new \PapayaUiDialog_TestProxy(new stdClass());
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $dialog->papaya($application);
    $document = new \Papaya\Xml\Document();
    $document->appendElement('test');
    $dialog->appendHidden($document->documentElement, new \Papaya\Request\Parameters($values), $group);
    $this->assertXmlStringEqualsXmlString(
      $expected,
      $document->saveXML($document->documentElement)
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::getParameterName
  */
  public function testGetParameterNameReturnsObject() {
    $dialog = new \PapayaUiDialog_TestProxy(new stdClass());
    $this->assertInstanceOf(\Papaya\Request\Parameters\Name::class, $dialog->getParameterName('foo'));
  }

  /**
   * @covers \Papaya\Ui\Dialog::getParameterName
   * @dataProvider provideParameterNameSamples
   * @param string $expected
   * @param string $name
   * @param int $method
   */
  public function testGetParameterName($expected, $name, $method) {
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $dialog->papaya(
      $this->mockPapaya()->application(
        array(
          'request' => $this->mockPapaya()->request(array(), 'http://www.test.tld/test.html', '*')
        )
      )
    );
    $dialog->parameterMethod($method);
    $this->assertEquals(
      $expected, (string)$dialog->getParameterName($name)
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::errors
  */
  public function testErrorsSet() {
    $errors = $this->createMock(\Papaya\Ui\Dialog\Errors::class);
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $dialog->errors($errors);
    $this->assertAttributeSame(
      $errors, '_errors', $dialog
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::errors
  */
  public function testErrorsGetAfterSet() {
    $errors = $this->createMock(\Papaya\Ui\Dialog\Errors::class);
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $this->assertSame(
      $errors, $dialog->errors($errors)
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::errors
  */
  public function testErrorsGetImplicitCreate() {
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $this->assertInstanceOf(
      \Papaya\Ui\Dialog\Errors::class, $dialog->errors()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::handleValidationFailure
  */
  public function testHandleValidationFailure() {
    $errors = $this->createMock(\Papaya\Ui\Dialog\Errors::class);
    $errors
      ->expects($this->once())
      ->method('add')
      ->with(
        $this->isInstanceOf(\Papaya\Filter\Exception::class),
        $this->isInstanceOf(\Papaya\Ui\Dialog\Field::class)
      );
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $dialog->errors($errors);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Filter\Exception $exception */
    $exception = $this->createMock(\Papaya\Filter\Exception::class);
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\Ui\Dialog\Field $field */
    $field = $this->createMock(\Papaya\Ui\Dialog\Field::class);
    $dialog->handleValidationFailure($exception, $field);
    $this->assertFalse($dialog->execute());
  }

  /**
  * @covers \Papaya\Ui\Dialog::appendTo
  * @covers \Papaya\Ui\Dialog::getMethodString
  */
  public function testAppendTo() {
    $options = $this->createMock(\Papaya\Ui\Dialog\Options::class);
    $options
      ->expects($this->atLeastOnce())
      ->method('__get')
      ->with($this->logicalOr('useConfirmation', 'useToken'))
      ->will($this->returnValue(TRUE));
    $options
      ->expects($this->once())
      ->method('appendTo');
    $owner = new stdClass();
    $tokens = $this->createMock(\Papaya\Ui\Tokens::class);
    $tokens
      ->expects($this->once())
      ->method('create')
      ->with($this->equalTo($owner))
      ->will($this->returnValue('TOKEN_STRING'));
    $fields = $this->createMock(\Papaya\Ui\Dialog\Fields::class);
    $fields
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    $buttons = $this->createMock(\Papaya\Ui\Dialog\Buttons::class);
    $buttons
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));
    $dialog = new \Papaya\Ui\Dialog($owner);
    $dialog->papaya($this->mockPapaya()->application());
    $dialog->tokens($tokens);
    $dialog->fields($fields);
    $dialog->buttons($buttons);
    $dialog->options($options);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<dialog-box action="http://www.test.tld/test.html" method="post">
        <input type="hidden" name="confirmation" value="true"/>
        <input type="hidden" name="token" value="TOKEN_STRING"/>
        </dialog-box>',
      $dialog->getXml()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::appendTo
  */
  public function testAppendToWithoutConfirmationWithoutToken() {
    $options = $this->createMock(\Papaya\Ui\Dialog\Options::class);
    $options
      ->expects($this->exactly(2))
      ->method('__get')
      ->with($this->logicalOr('useConfirmation', 'useToken'))
      ->will($this->returnValue(FALSE));
    $options
      ->expects($this->once())
      ->method('appendTo');
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $dialog->papaya($this->mockPapaya()->application());
    $dialog->options($options);
    $this->assertEquals(
    /** @lang XML */'<dialog-box action="http://www.test.tld/test.html" method="post"/>',
      $dialog->getXml()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::appendTo
  * @covers \Papaya\Ui\Dialog::setEncoding
  * @covers \Papaya\Ui\Dialog::getEncoding
  */
  public function testAppendToWithEncoding() {
    $options = $this->createMock(\Papaya\Ui\Dialog\Options::class);
    $options
      ->expects($this->exactly(2))
      ->method('__get')
      ->with($this->logicalOr('useConfirmation', 'useToken'))
      ->will($this->returnValue(FALSE));
    $options
      ->expects($this->once())
      ->method('appendTo');
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $dialog->papaya($this->mockPapaya()->application());
    $dialog->options($options);
    $dialog->setEncoding('multipart/form-data');
    $this->assertEquals(
      '<dialog-box'.
      ' action="http://www.test.tld/test.html" method="post" enctype="multipart/form-data"/>',
      $dialog->getXml()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::appendTo
  */
  public function testAppendToWithConfirmationWithoutToken() {
    $options = $this->createMock(\Papaya\Ui\Dialog\Options::class);
    $options
      ->expects($this->exactly(2))
      ->method('__get')
      ->with($this->logicalOr('useConfirmation', 'useToken'))
      ->will($this->onConsecutiveCalls(TRUE, FALSE));
    $options
      ->expects($this->once())
      ->method('appendTo');
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $dialog->papaya($this->mockPapaya()->application());
    $dialog->options($options);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<dialog-box action="http://www.test.tld/test.html" method="post">
        <input type="hidden" name="confirmation" value="true"/>
        </dialog-box>',
      $dialog->getXml()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::appendTo
  */
  public function testAppendToWithoutTokenButWithHiddenFields() {
    $options = $this->createMock(\Papaya\Ui\Dialog\Options::class);
    $options
      ->expects($this->exactly(2))
      ->method('__get')
      ->with($this->logicalOr('useConfirmation', 'useToken'))
      ->will($this->onConsecutiveCalls(TRUE, FALSE));
    $options
      ->expects($this->once())
      ->method('appendTo');
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $dialog->papaya($this->mockPapaya()->application());
    $dialog->options($options);
    $dialog->hiddenFields()->set('foo', 'bar');
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<dialog-box action="http://www.test.tld/test.html" method="post">
        <input type="hidden" name="foo" value="bar"/>
        <input type="hidden" name="confirmation" value="49a3696adf0fbfacc12383a2d7400d51"/>
        </dialog-box>',
      $dialog->getXml()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::appendTo
  */
  public function testAppendToWithCaption() {
    $options = $this->createMock(\Papaya\Ui\Dialog\Options::class);
    $options
      ->expects($this->exactly(2))
      ->method('__get')
      ->with($this->logicalOr('useConfirmation', 'useToken'))
      ->will($this->returnValue(FALSE));
    $options
      ->expects($this->once())
      ->method('appendTo');
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $dialog->papaya($this->mockPapaya()->application());
    $dialog->options($options);
    $dialog->caption('Test');
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<dialog-box action="http://www.test.tld/test.html" method="post">
        <title caption="Test"/>
      </dialog-box>',
      $dialog->getXml()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::appendTo
  */
  public function testAppendToWithDescription() {
    $options = $this->createMock(\Papaya\Ui\Dialog\Options::class);
    $options
      ->expects($this->exactly(2))
      ->method('__get')
      ->with($this->logicalOr('useConfirmation', 'useToken'))
      ->will($this->returnValue(FALSE));
    $options
      ->expects($this->once())
      ->method('appendTo');

    $description = $this->createMock(\Papaya\Ui\Dialog\Element\Description::class);
    $description
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(\Papaya\Xml\Element::class));

    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $dialog->papaya($this->mockPapaya()->application());
    $dialog->options = $options;
    $dialog->description = $description;
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
      '<dialog-box action="http://www.test.tld/test.html" method="post"/>',
      $dialog->getXml()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::options
  */
  public function testOptionsGetImplicitCreate() {
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $this->assertInstanceOf(
      \Papaya\Ui\Dialog\Options::class, $dialog->options()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::options
  */
  public function testOptionsSet() {
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $options = $this->createMock(\Papaya\Ui\Dialog\Options::class);
    $dialog->options($options);
    $this->assertAttributeSame(
      $options, '_options', $dialog
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::options
  */
  public function testOptionsGetAfterSet() {
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $options = $this->createMock(\Papaya\Ui\Dialog\Options::class);
    $this->assertSame(
      $options, $dialog->options($options)
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::caption
  */
  public function testCaptionSet() {
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $dialog->caption('success');
    $this->assertAttributeEquals(
      'success', '_caption', $dialog
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::caption
  */
  public function testCaptionGet() {
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $this->assertEquals(
      'success', $dialog->caption('success')
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::title
  */
  public function testTitleGetAfterSet() {
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $this->assertEquals(
      'success', $dialog->title('success')
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::fields
  */
  public function testFieldsGetImplicitCreate() {
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $dialog->papaya($application = $this->mockPapaya()->application());
    $this->assertInstanceOf(
      \Papaya\Ui\Dialog\Fields::class, $dialog->fields()
    );
    $this->assertSame(
      $application, $dialog->fields()->papaya()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::fields
  */
  public function testFieldsSet() {
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $fields = $this->createMock(\Papaya\Ui\Dialog\Fields::class);
    $fields
      ->expects($this->once())
      ->method('owner')
      ->with($this->equalTo($dialog));
    $dialog->fields($fields);
    $this->assertAttributeSame(
      $fields, '_fields', $dialog
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::fields
  */
  public function testFieldsSetFromTraversable() {
    $fields = new ArrayIterator(
      array(
        $this->createMock(\Papaya\Ui\Dialog\Field::class),
        $this->createMock(\Papaya\Ui\Dialog\Field::class)
      )
    );
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $dialog->fields($fields);
    $this->assertCount(2, $dialog->fields());
  }

  /**
  * @covers \Papaya\Ui\Dialog::fields
  */
  public function testFieldsGetAfterSet() {
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $fields = $this->createMock(\Papaya\Ui\Dialog\Fields::class);
    $fields
      ->expects($this->once())
      ->method('owner')
      ->with($this->equalTo($dialog));
    $this->assertSame(
      $fields, $dialog->fields($fields)
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::buttons
  */
  public function testButtonsGetImplicitCreate() {
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $dialog->papaya($application = $this->mockPapaya()->application());
    $this->assertInstanceOf(
      \Papaya\Ui\Dialog\Buttons::class, $dialog->buttons()
    );
    $this->assertSame(
      $application, $dialog->buttons()->papaya()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::buttons
  */
  public function testButtonsSet() {
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $buttons = $this->createMock(\Papaya\Ui\Dialog\Buttons::class);
    $buttons
      ->expects($this->once())
      ->method('owner')
      ->with($this->equalTo($dialog));
    $dialog->buttons($buttons);
    $this->assertAttributeSame(
      $buttons, '_buttons', $dialog
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::buttons
  */
  public function testButtonsGetAfterSet() {
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $buttons = $this->createMock(\Papaya\Ui\Dialog\Buttons::class);
    $buttons
      ->expects($this->once())
      ->method('owner')
      ->with($this->equalTo($dialog));
    $this->assertSame(
      $buttons, $dialog->buttons($buttons)
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::data
  */
  public function testDataGetImplicitCreate() {
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $this->assertInstanceOf(
      \Papaya\Request\Parameters::class, $dialog->data()
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::data
  */
  public function testDataGetImplicitCreateMergingHiddenFields() {
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $dialog->hiddenFields()->merge(array('merge' => 'success'));
    $this->assertEquals(
      'success', $dialog->data()->get('merge')
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::data
  */
  public function testDataSet() {
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $data = $this->createMock(\Papaya\Request\Parameters::class);
    $dialog->data($data);
    $this->assertAttributeSame(
      $data, '_data', $dialog
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::data
  */
  public function testDataGetAfterSet() {
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $data = $this->createMock(\Papaya\Request\Parameters::class);
    $this->assertSame(
      $data, $dialog->data($data)
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::description
  */
  public function testDescriptionGetAfterSet() {
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $description = $this->createMock(\Papaya\Ui\Dialog\Element\Description::class);
    $this->assertSame(
      $description, $dialog->description($description)
    );
  }

  /**
  * @covers \Papaya\Ui\Dialog::description
  */
  public function testDescriptionGetImplicitCreateMergingHiddenFields() {
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $dialog->papaya($papaya = $this->mockPapaya()->application());
    $this->assertInstanceOf(
      \Papaya\Ui\Dialog\Element\Description::class, $description = $dialog->description()
    );
    $this->assertSame($papaya, $description->papaya());
  }

  /**
   * @covers \Papaya\Ui\Dialog::isSubmitted
   * @dataProvider provideValidMethodPairs
   * @param int $requestMethod
   * @param int $dialogMethod
   */
  public function testIsSubmittedExpectingTrue($requestMethod, $dialogMethod) {
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue($requestMethod));
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $dialog->parameters(
      new \Papaya\Request\Parameters(array('confirmation' => 'true'))
    );
    $dialog->options()->useToken = FALSE;
    $dialog->parameterMethod($dialogMethod);
    $this->assertTrue($dialog->isSubmitted());
  }

  /**
  * @covers \Papaya\Ui\Dialog::isSubmitted
  */
  public function testIsSubmittedWithHiddenFieldsExpectingTrue() {
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('post'));
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $dialog->hiddenFields()->set('foo', 'bar');
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $dialog->parameters(
      new \Papaya\Request\Parameters(array('confirmation' => '49a3696adf0fbfacc12383a2d7400d51'))
    );
    $dialog->options()->useToken = FALSE;
    $this->assertTrue($dialog->isSubmitted());
  }

  /**
  * @covers \Papaya\Ui\Dialog::isSubmitted
  */
  public function testIsSubmittedWithValidTokenExpectingTrue() {
    $owner = new stdClass();
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('post'));
    $tokens = $this->createMock(\Papaya\Ui\Tokens::class);
    $tokens
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo('TOKEN_STRING'), $this->equalTo($owner))
      ->will($this->returnValue(TRUE));
    $dialog = new \Papaya\Ui\Dialog($owner);
    $dialog->tokens($tokens);
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $dialog->parameters(
      new \Papaya\Request\Parameters(
        array(
          'confirmation' => '40cd750bba9870f18aada2478b24840a',
          'token' => 'TOKEN_STRING'
        )
      )
    );
    $this->assertTrue($dialog->isSubmitted());
  }

  /**
  * @covers \Papaya\Ui\Dialog::isSubmitted
  */
  public function testIsSubmittedWithInvalidTokenExpectingFalse() {
    $owner = new stdClass();
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('post'));
    $tokens = $this->createMock(\Papaya\Ui\Tokens::class);
    $tokens
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo('TOKEN_STRING'), $this->equalTo($owner))
      ->will($this->returnValue(FALSE));
    $dialog = new \Papaya\Ui\Dialog($owner);
    $dialog->tokens($tokens);
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $dialog->parameters(
      new \Papaya\Request\Parameters(
        array(
          'confirmation' => '40cd750bba9870f18aada2478b24840a',
          'token' => 'TOKEN_STRING'
        )
      )
    );
    $this->assertFalse($dialog->isSubmitted());
  }

  /**
   * @covers \Papaya\Ui\Dialog::isSubmitted
   * @dataProvider provideInvalidMethodPairs
   * @param int $requestMethod
   * @param int $dialogMethod
   */
  public function testIsSubmittedExpectingFalse($requestMethod, $dialogMethod) {
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue($requestMethod));
    $dialog = new \Papaya\Ui\Dialog(new stdClass());
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $dialog->parameterMethod($dialogMethod);
    $dialog->options()->useToken = FALSE;
    $this->assertFalse($dialog->isSubmitted());
  }

  /**
  * @covers \Papaya\Ui\Dialog::execute
  */
  public function testExecuteExpectingTrue() {
    $owner = new stdClass();
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('post'));
    $tokens = $this->createMock(\Papaya\Ui\Tokens::class);
    $tokens
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo('TOKEN_STRING'), $this->equalTo($owner))
      ->will($this->returnValue(TRUE));
    $fields = $this->createMock(\Papaya\Ui\Dialog\Fields::class);
    $fields
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(TRUE));
    $fields
      ->expects($this->once())
      ->method('collect');
    $buttons = $this->createMock(\Papaya\Ui\Dialog\Buttons::class);
    $buttons
      ->expects($this->once())
      ->method('collect');
    $dialog = new \Papaya\Ui\Dialog($owner);
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $dialog->tokens($tokens);
    $dialog->fields($fields);
    $dialog->buttons($buttons);
    $dialog->parameters(
      new \Papaya\Request\Parameters(
        array(
          'confirmation' => '40cd750bba9870f18aada2478b24840a',
          'token' => 'TOKEN_STRING'
        )
      )
    );
    $this->assertTrue($dialog->execute());
  }

  /**
  * @covers \Papaya\Ui\Dialog::execute
  */
  public function testExecuteWithoutTokenExpectingTrue() {
    $owner = new stdClass();
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('post'));
    $fields = $this->createMock(\Papaya\Ui\Dialog\Fields::class);
    $fields
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(TRUE));
    $fields
      ->expects($this->once())
      ->method('collect');
    $buttons = $this->createMock(\Papaya\Ui\Dialog\Buttons::class);
    $buttons
      ->expects($this->once())
      ->method('collect');
    $dialog = new \Papaya\Ui\Dialog($owner);
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $dialog->options()->useToken = FALSE;
    $dialog->fields($fields);
    $dialog->buttons($buttons);
    $dialog->parameters(
      new \Papaya\Request\Parameters(
        array(
          'confirmation' => '40cd750bba9870f18aada2478b24840a'
        )
      )
    );
    $this->assertTrue($dialog->execute());
  }

  /**
  * @covers \Papaya\Ui\Dialog::execute
  */
  public function testExecuteWithoutTokenExpectingFalse() {
    $owner = new stdClass();
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('post'));
    $fields = $this->createMock(\Papaya\Ui\Dialog\Fields::class);
    $fields
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(FALSE));
    $dialog = new \Papaya\Ui\Dialog($owner);
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $dialog->options()->useToken = FALSE;
    $dialog->fields($fields);
    $dialog->parameters(
      new \Papaya\Request\Parameters(
        array(
          'confirmation' => '40cd750bba9870f18aada2478b24840a'
        )
      )
    );
    $this->assertFalse($dialog->execute());
  }

  /**
  * @covers \Papaya\Ui\Dialog::execute
  */
  public function testExecuteWrongMethodAndCachedResult() {
    $owner = new stdClass();
    $request = $this->createMock(\Papaya\Request::class);
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('get'));
    $dialog = new \Papaya\Ui\Dialog($owner);
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $this->assertFalse($dialog->execute());
    $this->assertFalse($dialog->execute());
  }

  /**************************
  * Data Provider
  **************************/

  public static function provideValidMethodPairs() {
    return array(
      'get + get' => array('get', \Papaya\Ui\Dialog::METHOD_GET),
      'post + post' => array('post', \Papaya\Ui\Dialog::METHOD_POST),
      'get + mixed' => array('get', \Papaya\Ui\Dialog::METHOD_MIXED),
      'post + mixed' => array('post', \Papaya\Ui\Dialog::METHOD_MIXED)
    );
  }

  public static function provideInvalidMethodPairs() {
    return array(
      'get + post' => array('get', \Papaya\Ui\Dialog::METHOD_POST),
      'post + get' => array('post', \Papaya\Ui\Dialog::METHOD_GET),
      'head + mixed' => array('head', \Papaya\Ui\Dialog::METHOD_MIXED)
    );
  }

  public static function provideMethodsAndStringRepresentations() {
    return array(
      array('post', \Papaya\Ui\Control\Interactive::METHOD_POST),
      array('get', \Papaya\Ui\Control\Interactive::METHOD_GET),
      array('post', \Papaya\Ui\Control\Interactive::METHOD_MIXED)
    );
  }

  public static function provideHiddenDataAndResult() {
    return array(
      array(
        NULL,
        array('foo' => 'bar', 'bar' => 'foo'),
        /** @lang XML */
        '<test>
          <input type="hidden" name="foo" value="bar"/>
          <input type="hidden" name="bar" value="foo"/>
          </test>'
      ),
      array(
        'group',
        array('foo' => 'bar', 'bar' => 'foo'),
        /** @lang XML */
        '<test>
          <input type="hidden" name="group[foo]" value="bar"/>
          <input type="hidden" name="group[bar]" value="foo"/>
          </test>'
      ),
      array(
        'group',
        array('foo' => array(TRUE, FALSE), 'bar' => array(21, 42)),
        /** @lang XML */
        '<test>
          <input type="hidden" name="group[foo][0]" value="1"/>
          <input type="hidden" name="group[foo][1]" value=""/>
          <input type="hidden" name="group[bar][0]" value="21"/>
          <input type="hidden" name="group[bar][1]" value="42"/>
          </test>'
      )
    );
  }

  public static function provideParameterNameSamples() {
    return array(
      array('foo', 'foo', \Papaya\Ui\Dialog::METHOD_GET),
      array('foo', 'foo', \Papaya\Ui\Dialog::METHOD_POST),
      array('foo', 'foo', \Papaya\Ui\Dialog::METHOD_MIXED),
      array('foo*bar', 'foo/bar', \Papaya\Ui\Dialog::METHOD_GET),
      array('foo[bar]', 'foo/bar', \Papaya\Ui\Dialog::METHOD_POST),
      array('foo[bar]', 'foo/bar', \Papaya\Ui\Dialog::METHOD_MIXED)
    );
  }
}

class PapayaUiDialog_TestProxy extends \Papaya\Ui\Dialog {

  public function getMethodString() {
    return parent::getMethodString();
  }

  public function appendHidden(\Papaya\Xml\Element $parent,
                               \Papaya\Request\Parameters $values,
                               $path = NULL) {
    return parent::appendHidden($parent, $values, $path);
  }
}
