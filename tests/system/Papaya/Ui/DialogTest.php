<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaUiDialogTest extends PapayaTestCase {

  /**
  * @covers PapayaUiDialog::__construct
  */
  public function testConstructor() {
    $owner = new stdClass();
    $dialog = new PapayaUiDialog($owner);
    $this->assertAttributeSame(
      $owner, '_owner', $dialog
    );
  }

  /**
  * @covers PapayaUiDialog::getMethodString
  * @dataProvider provideMethodsAndStringRepresentations
  */
  public function testGetMethodString($expected, $method) {
    $dialog = new PapayaUiDialog_TestProxy(new stdClass());
    $dialog->parameterMethod($method);
    $this->assertEquals($expected, $dialog->getMethodString());
  }

  /**
  * @covers PapayaUiDialog::hiddenValues
  */
  public function testHiddenValuesSet() {
    $values = $this->createMock(PapayaRequestParameters::class);
    $dialog = new PapayaUiDialog(new stdClass());
    $dialog->hiddenValues($values);
    $this->assertAttributeSame(
      $values, '_hiddenValues', $dialog
    );
  }

  /**
  * @covers PapayaUiDialog::hiddenValues
  */
  public function testHiddenValuesGetAfterSet() {
    $values = $this->createMock(PapayaRequestParameters::class);
    $dialog = new PapayaUiDialog(new stdClass());
    $this->assertSame(
      $values, $dialog->hiddenValues($values)
    );
  }

  /**
  * @covers PapayaUiDialog::hiddenValues
  */
  public function testHiddenValuesGetImplicitCreate() {
    $dialog = new PapayaUiDialog(new stdClass());
    $this->assertInstanceOf(
      PapayaRequestParameters::class, $dialog->hiddenValues()
    );
  }

  /**
  * @covers PapayaUiDialog::hiddenFields
  */
  public function testHiddenFieldsSet() {
    $fields = $this->createMock(PapayaRequestParameters::class);
    $dialog = new PapayaUiDialog(new stdClass());
    $dialog->hiddenFields($fields);
    $this->assertAttributeSame(
      $fields, '_hiddenFields', $dialog
    );
  }

  /**
  * @covers PapayaUiDialog::hiddenFields
  */
  public function testHiddenFieldsGetAfterSet() {
    $fields = $this->createMock(PapayaRequestParameters::class);
    $dialog = new PapayaUiDialog(new stdClass());
    $this->assertSame(
      $fields, $dialog->hiddenFields($fields)
    );
  }

  /**
  * @covers PapayaUiDialog::hiddenFields
  */
  public function testHiddenFieldsGetImplicitCreate() {
    $dialog = new PapayaUiDialog(new stdClass());
    $this->assertInstanceOf(
      PapayaRequestParameters::class, $dialog->hiddenFields()
    );
  }

  /**
  * @covers PapayaUiDialog::action
  */
  public function testActionSet() {
    $dialog = new PapayaUiDialog(new stdClass());
    $dialog->action('sample');
    $this->assertAttributeEquals(
      'sample', '_action', $dialog
    );
  }

  /**
  * @covers PapayaUiDialog::action
  */
  public function testActionGetAfterSet() {
    $dialog = new PapayaUiDialog(new stdClass());
    $this->assertEquals(
      'sample', $dialog->action('sample')
    );
  }

  /**
  * @covers PapayaUiDialog::action
  */
  public function testActionGetWithoutSet() {
    $dialog = new PapayaUiDialog(new stdClass());
    $dialog->papaya($this->mockPapaya()->application());
    $this->assertEquals(
      'http://www.test.tld/test.html', $dialog->action()
    );
  }

  /**
  * @covers PapayaUiDialog::tokens
  */
  public function testTokensSet() {
    $tokens = $this->createMock(PapayaUiTokens::class);
    $dialog = new PapayaUiDialog(new stdClass());
    $dialog->tokens($tokens);
    $this->assertAttributeSame(
      $tokens, '_tokens', $dialog
    );
  }

  /**
  * @covers PapayaUiDialog::tokens
  */
  public function testTokensGetAfterSet() {
    $tokens = $this->createMock(PapayaUiTokens::class);
    $dialog = new PapayaUiDialog(new stdClass());
    $this->assertSame(
      $tokens, $dialog->tokens($tokens)
    );
  }

  /**
  * @covers PapayaUiDialog::tokens
  */
  public function testTokensGetImplicitCreate() {
    $dialog = new PapayaUiDialog(new stdClass());
    $this->assertInstanceOf(
      PapayaUiTokens::class, $dialog->tokens()
    );
  }

  /**
  * @covers PapayaUiDialog::appendHidden
  * @dataProvider provideHiddenDataAndResult
  */
  public function testAppendHidden($group, $values, $expected) {
    $dialog = new PapayaUiDialog_TestProxy(new stdClass());
    $request = $this->mockPapaya()->request();
    $application = $this->mockPapaya()->application(array('request' => $request));
    $dialog->papaya($application);
    $dom = new PapayaXmlDocument();
    $dom->appendElement('test');
    $dialog->appendHidden($dom->documentElement, new PapayaRequestParameters($values), $group);
    $this->assertEquals(
      $expected,
      $dom->saveXml($dom->documentElement)
    );
  }

  /**
  * @covers PapayaUiDialog::getParameterName
  */
  public function testGetParameterNameReturnsObject() {
    $dialog = new PapayaUiDialog_TestProxy(new stdClass());
    $this->assertInstanceOf(PapayaRequestParametersName::class, $dialog->getParameterName('foo'));
  }

  /**
  * @covers PapayaUiDialog::getParameterName
  * @dataProvider provideParameterNameSamples
  */
  public function testGetParameterName($expected, $name, $method) {
    $dialog = new PapayaUiDialog(new stdClass());
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
  * @covers PapayaUiDialog::errors
  */
  public function testErrorsSet() {
    $errors = $this->createMock(PapayaUiDialogErrors::class);
    $dialog = new PapayaUiDialog(new stdClass());
    $dialog->errors($errors);
    $this->assertAttributeSame(
      $errors, '_errors', $dialog
    );
  }

  /**
  * @covers PapayaUiDialog::errors
  */
  public function testErrorsGetAfterSet() {
    $errors = $this->createMock(PapayaUiDialogErrors::class);
    $dialog = new PapayaUiDialog(new stdClass());
    $this->assertSame(
      $errors, $dialog->errors($errors)
    );
  }

  /**
  * @covers PapayaUiDialog::errors
  */
  public function testErrorsGetImplicitCreate() {
    $dialog = new PapayaUiDialog(new stdClass());
    $this->assertInstanceOf(
      PapayaUiDialogErrors::class, $dialog->errors()
    );
  }

  /**
  * @covers PapayaUiDialog::handleValidationFailure
  */
  public function testHandleValidationFailure() {
    $errors = $this->getMock(PapayaUiDialogErrors::class, array('add'));
    $errors
      ->expects($this->once())
      ->method('add')
      ->with(
        $this->isInstanceOf(PapayaFilterException::class),
        $this->isInstanceOf(PapayaUiDialogField::class)
      );
    $dialog = new PapayaUiDialog(new stdClass());
    $dialog->errors($errors);
    $dialog->handleValidationFailure(
      $this->createMock(PapayaFilterException::class),
      $this->createMock(PapayaUiDialogField::class)
    );
    $this->assertFalse($dialog->execute());
  }

  /**
  * @covers PapayaUiDialog::appendTo
  * @covers PapayaUiDialog::getMethodString
  */
  public function testAppendTo() {
    $options = $this->getMock(PapayaUiDialogOptions::class, array('__get', 'appendTo'));
    $options
      ->expects($this->atLeastOnce())
      ->method('__get')
      ->with($this->logicalOr('useConfirmation', 'useToken'))
      ->will($this->returnValue(TRUE));
    $options
      ->expects($this->once())
      ->method('appendTo');
    $owner = new stdClass();
    $tokens = $this->getMock(PapayaUiTokens::class, array('create', 'validate'));
    $tokens
      ->expects($this->once())
      ->method('create')
      ->with($this->equalTo($owner))
      ->will($this->returnValue('TOKEN_STRING'));
    $fields = $this->getMock(
      PapayaUiDialogFields::class, array('owner', 'appendTo', 'validate', 'collect')
    );
    $fields
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(PapayaXMLElement::class));
    $buttons = $this->getMock(
      PapayaUiDialogButtons::class, array('owner', 'appendTo', 'collect')
    );
    $buttons
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(PapayaXMLElement::class));
    $dialog = new PapayaUiDialog($owner);
    $dialog->papaya($this->mockPapaya()->application());
    $dialog->tokens($tokens);
    $dialog->fields($fields);
    $dialog->buttons($buttons);
    $dialog->options($options);
    $this->assertEquals(
      '<dialog-box action="http://www.test.tld/test.html" method="post">'.
        '<input type="hidden" name="confirmation" value="true"/>'.
        '<input type="hidden" name="token" value="TOKEN_STRING"/>'.
        '</dialog-box>',
      $dialog->getXml()
    );
  }

  /**
  * @covers PapayaUiDialog::appendTo
  */
  public function testAppendToWithoutConfirmationWithoutToken() {
    $options = $this->getMock(PapayaUiDialogOptions::class, array('__get', 'appendTo'));
    $options
      ->expects($this->exactly(2))
      ->method('__get')
      ->with($this->logicalOr('useConfirmation', 'useToken'))
      ->will($this->returnValue(FALSE));
    $options
      ->expects($this->once())
      ->method('appendTo');
    $dialog = new PapayaUiDialog(new stdClass());
    $dialog->papaya($this->mockPapaya()->application());
    $dialog->options($options);
    $this->assertEquals(
      '<dialog-box action="http://www.test.tld/test.html" method="post"/>',
      $dialog->getXml()
    );
  }

  /**
  * @covers PapayaUiDialog::appendTo
  * @covers PapayaUiDialog::setEncoding
  * @covers PapayaUiDialog::getEncoding
  */
  public function testAppendToWithEncoding() {
    $options = $this->getMock(PapayaUiDialogOptions::class, array('__get', 'appendTo'));
    $options
      ->expects($this->exactly(2))
      ->method('__get')
      ->with($this->logicalOr('useConfirmation', 'useToken'))
      ->will($this->returnValue(FALSE));
    $options
      ->expects($this->once())
      ->method('appendTo');
    $dialog = new PapayaUiDialog(new stdClass());
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
  * @covers PapayaUiDialog::appendTo
  */
  public function testAppendToWithConfirmationWithoutToken() {
    $options = $this->getMock(PapayaUiDialogOptions::class, array('__get', 'appendTo'));
    $options
      ->expects($this->exactly(2))
      ->method('__get')
      ->with($this->logicalOr('useConfirmation', 'useToken'))
      ->will($this->onConsecutiveCalls(TRUE, FALSE));
    $options
      ->expects($this->once())
      ->method('appendTo');
    $dialog = new PapayaUiDialog(new stdClass());
    $dialog->papaya($this->mockPapaya()->application());
    $dialog->options($options);
    $this->assertEquals(
      '<dialog-box action="http://www.test.tld/test.html" method="post">'.
        '<input type="hidden" name="confirmation" value="true"/>'.
        '</dialog-box>',
      $dialog->getXml()
    );
  }

  /**
  * @covers PapayaUiDialog::appendTo
  */
  public function testAppendToWithoutTokenButWithHiddenFields() {
    $options = $this->getMock(PapayaUiDialogOptions::class, array('__get', 'appendTo'));
    $options
      ->expects($this->exactly(2))
      ->method('__get')
      ->with($this->logicalOr('useConfirmation', 'useToken'))
      ->will($this->onConsecutiveCalls(TRUE, FALSE));
    $options
      ->expects($this->once())
      ->method('appendTo');
    $dialog = new PapayaUiDialog(new stdClass());
    $dialog->papaya($this->mockPapaya()->application());
    $dialog->options($options);
    $dialog->hiddenFields()->set('foo', 'bar');
    $this->assertEquals(
      '<dialog-box action="http://www.test.tld/test.html" method="post">'.
        '<input type="hidden" name="foo" value="bar"/>'.
        '<input type="hidden" name="confirmation" value="49a3696adf0fbfacc12383a2d7400d51"/>'.
        '</dialog-box>',
      $dialog->getXml()
    );
  }

  /**
  * @covers PapayaUiDialog::appendTo
  */
  public function testAppendToWithCaption() {
    $options = $this->getMock(PapayaUiDialogOptions::class, array('__get', 'appendTo'));
    $options
      ->expects($this->exactly(2))
      ->method('__get')
      ->with($this->logicalOr('useConfirmation', 'useToken'))
      ->will($this->returnValue(FALSE));
    $options
      ->expects($this->once())
      ->method('appendTo');
    $dialog = new PapayaUiDialog(new stdClass());
    $dialog->papaya($this->mockPapaya()->application());
    $dialog->options($options);
    $dialog->caption('Test');
    $this->assertEquals(
      '<dialog-box action="http://www.test.tld/test.html" method="post">'.
        '<title caption="Test"/>'.
      '</dialog-box>',
      $dialog->getXml()
    );
  }

  /**
  * @covers PapayaUiDialog::appendTo
  */
  public function testAppendToWithDescription() {
    $options = $this->getMock(PapayaUiDialogOptions::class, array('__get', 'appendTo'));
    $options
      ->expects($this->exactly(2))
      ->method('__get')
      ->with($this->logicalOr('useConfirmation', 'useToken'))
      ->will($this->returnValue(FALSE));
    $options
      ->expects($this->once())
      ->method('appendTo');

    $description = $this->createMock(PapayaUiDialogElementDescription::class);
    $description
      ->expects($this->once())
      ->method('appendTo')
      ->with($this->isInstanceOf(PapayaXmlElement::class));

    $dialog = new PapayaUiDialog(new stdClass());
    $dialog->papaya($this->mockPapaya()->application());
    $dialog->options = $options;
    $dialog->description = $description;
    $this->assertEquals(
      '<dialog-box action="http://www.test.tld/test.html" method="post"/>',
      $dialog->getXml()
    );
  }

  /**
  * @covers PapayaUiDialog::options
  */
  public function testOptionsGetImplicitCreate() {
    $dialog = new PapayaUiDialog(new stdClass());
    $this->assertInstanceOf(
      PapayaUiDialogOptions::class, $dialog->options()
    );
  }

  /**
  * @covers PapayaUiDialog::options
  */
  public function testOptionsSet() {
    $dialog = new PapayaUiDialog(new stdClass());
    $options = $this->createMock(PapayaUiDialogOptions::class);
    $dialog->options($options);
    $this->assertAttributeSame(
      $options, '_options', $dialog
    );
  }

  /**
  * @covers PapayaUiDialog::options
  */
  public function testOptionsGetAfterSet() {
    $dialog = new PapayaUiDialog(new stdClass());
    $options = $this->createMock(PapayaUiDialogOptions::class);
    $this->assertSame(
      $options, $dialog->options($options)
    );
  }

  /**
  * @covers PapayaUiDialog::caption
  */
  public function testCaptionSet() {
    $dialog = new PapayaUiDialog(new stdClass());
    $dialog->caption('success');
    $this->assertAttributeEquals(
      'success', '_caption', $dialog
    );
  }

  /**
  * @covers PapayaUiDialog::caption
  */
  public function testCaptionGet() {
    $dialog = new PapayaUiDialog(new stdClass());
    $this->assertEquals(
      'success', $dialog->caption('success')
    );
  }

  /**
  * @covers PapayaUiDialog::title
  */
  public function testTitleGetAfterSet() {
    $dialog = new PapayaUiDialog(new stdClass());
    $this->assertEquals(
      'success', $dialog->title('success')
    );
  }

  /**
  * @covers PapayaUiDialog::fields
  */
  public function testFieldsGetImplicitCreate() {
    $dialog = new PapayaUiDialog(new stdClass());
    $dialog->papaya($application = $this->mockPapaya()->application());
    $this->assertInstanceOf(
      PapayaUiDialogFields::class, $dialog->fields()
    );
    $this->assertSame(
      $application, $dialog->fields()->papaya()
    );
  }

  /**
  * @covers PapayaUiDialog::fields
  */
  public function testFieldsSet() {
    $dialog = new PapayaUiDialog(new stdClass());
    $fields = $this->getMock(PapayaUiDialogFields::class, array('owner'));
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
  * @covers PapayaUiDialog::fields
  */
  public function testFieldsSetFromTraversable() {
    $fields = new ArrayIterator(
      array(
        $this->createMock(PapayaUiDialogField::class),
        $this->createMock(PapayaUiDialogField::class)
      )
    );
    $dialog = new PapayaUiDialog(new stdClass());
    $dialog->fields($fields);
    $this->assertCount(2, $dialog->fields());
  }

  /**
  * @covers PapayaUiDialog::fields
  */
  public function testFieldsGetAfterSet() {
    $dialog = new PapayaUiDialog(new stdClass());
    $fields = $this->getMock(PapayaUiDialogFields::class, array('owner'));
    $fields
      ->expects($this->once())
      ->method('owner')
      ->with($this->equalTo($dialog));
    $this->assertSame(
      $fields, $dialog->fields($fields)
    );
  }

  /**
  * @covers PapayaUiDialog::buttons
  */
  public function testButtonsGetImplicitCreate() {
    $dialog = new PapayaUiDialog(new stdClass());
    $dialog->papaya($application = $this->mockPapaya()->application());
    $this->assertInstanceOf(
      PapayaUiDialogButtons::class, $dialog->buttons()
    );
    $this->assertSame(
      $application, $dialog->buttons()->papaya()
    );
  }

  /**
  * @covers PapayaUiDialog::buttons
  */
  public function testButtonsSet() {
    $dialog = new PapayaUiDialog(new stdClass());
    $buttons = $this->getMock(PapayaUiDialogButtons::class, array('owner'));
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
  * @covers PapayaUiDialog::buttons
  */
  public function testButtonsGetAfterSet() {
    $dialog = new PapayaUiDialog(new stdClass());
    $buttons = $this->getMock(PapayaUiDialogButtons::class, array('owner'));
    $buttons
      ->expects($this->once())
      ->method('owner')
      ->with($this->equalTo($dialog));
    $this->assertSame(
      $buttons, $dialog->buttons($buttons)
    );
  }

  /**
  * @covers PapayaUiDialog::data
  */
  public function testDataGetImplicitCreate() {
    $dialog = new PapayaUiDialog(new stdClass());
    $this->assertInstanceOf(
      PapayaRequestParameters::class, $dialog->data()
    );
  }

  /**
  * @covers PapayaUiDialog::data
  */
  public function testDataGetImplicitCreateMergingHiddenFields() {
    $dialog = new PapayaUiDialog(new stdClass());
    $dialog->hiddenFields()->merge(array('merge' => 'success'));
    $this->assertEquals(
      'success', $dialog->data()->get('merge')
    );
  }

  /**
  * @covers PapayaUiDialog::data
  */
  public function testDataSet() {
    $dialog = new PapayaUiDialog(new stdClass());
    $data = $this->createMock(PapayaRequestParameters::class);
    $dialog->data($data);
    $this->assertAttributeSame(
      $data, '_data', $dialog
    );
  }

  /**
  * @covers PapayaUiDialog::data
  */
  public function testDataGetAfterSet() {
    $dialog = new PapayaUiDialog(new stdClass());
    $data = $this->createMock(PapayaRequestParameters::class);
    $this->assertSame(
      $data, $dialog->data($data)
    );
  }

  /**
  * @covers PapayaUiDialog::description
  */
  public function testDescriptionGetAfterSet() {
    $dialog = new PapayaUiDialog(new stdClass());
    $description = $this->createMock(PapayaUiDialogElementDescription::class);
    $this->assertSame(
      $description, $dialog->description($description)
    );
  }

  /**
  * @covers PapayaUiDialog::description
  */
  public function testDescriptionGetImplicitCreateMergingHiddenFields() {
    $dialog = new PapayaUiDialog(new stdClass());
    $dialog->papaya($papaya = $this->mockPapaya()->application());
    $this->assertInstanceOf(
      PapayaUiDialogElementDescription::class, $description = $dialog->description()
    );
    $this->assertSame($papaya, $description->papaya());
  }

  /**
  * @covers PapayaUiDialog::isSubmitted
  * @dataProvider provideValidMethodPairs
  */
  public function testIsSubmittedExpectingTrue($requestMethod, $dialogMethod) {
    $request = $this->getMock(PapayaRequest::class, array('getMethod'));
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue($requestMethod));
    $dialog = new PapayaUiDialog(new stdClass());
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $dialog->parameters(
      new PapayaRequestParameters(array('confirmation' => 'true'))
    );
    $dialog->options()->useToken = FALSE;
    $dialog->parameterMethod($dialogMethod);
    $this->assertTrue($dialog->isSubmitted());
  }

  /**
  * @covers PapayaUiDialog::isSubmitted
  */
  public function testIsSubmittedWithHiddenFieldsExpectingTrue() {
    $request = $this->getMock(PapayaRequest::class, array('getMethod'));
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('post'));
    $dialog = new PapayaUiDialog(new stdClass());
    $dialog->hiddenFields()->set('foo', 'bar');
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $dialog->parameters(
      new PapayaRequestParameters(array('confirmation' => '49a3696adf0fbfacc12383a2d7400d51'))
    );
    $dialog->options()->useToken = FALSE;
    $this->assertTrue($dialog->isSubmitted());
  }

  /**
  * @covers PapayaUiDialog::isSubmitted
  */
  public function testIsSubmittedWithValidTokenExpectingTrue() {
    $owner = new stdClass();
    $request = $this->getMock(PapayaRequest::class, array('getMethod'));
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('post'));
    $tokens = $this->getMock(PapayaUiTokens::class, array('create', 'validate'));
    $tokens
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo('TOKEN_STRING'), $this->equalTo($owner))
      ->will($this->returnValue(TRUE));
    $dialog = new PapayaUiDialog($owner);
    $dialog->tokens($tokens);
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $dialog->parameters(
      new PapayaRequestParameters(
        array(
          'confirmation' => '40cd750bba9870f18aada2478b24840a',
          'token' => 'TOKEN_STRING'
        )
      )
    );
    $this->assertTrue($dialog->isSubmitted());
  }

  /**
  * @covers PapayaUiDialog::isSubmitted
  */
  public function testIsSubmittedWithInvalidTokenExpectingFalse() {
    $owner = new stdClass();
    $request = $this->getMock(PapayaRequest::class, array('getMethod'));
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('post'));
    $tokens = $this->getMock(PapayaUiTokens::class, array('create', 'validate'));
    $tokens
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo('TOKEN_STRING'), $this->equalTo($owner))
      ->will($this->returnValue(FALSE));
    $dialog = new PapayaUiDialog($owner);
    $dialog->tokens($tokens);
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $dialog->parameters(
      new PapayaRequestParameters(
        array(
          'confirmation' => '40cd750bba9870f18aada2478b24840a',
          'token' => 'TOKEN_STRING'
        )
      )
    );
    $this->assertFalse($dialog->isSubmitted());
  }

  /**
  * @covers PapayaUiDialog::isSubmitted
  * @dataProvider provideInvalidMethodPairs
  */
  public function testIsSubmittedExpectingFalse($requestMethod, $dialogMethod) {
    $request = $this->getMock(PapayaRequest::class, array('getMethod'));
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue($requestMethod));
    $dialog = new PapayaUiDialog(new stdClass());
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $dialog->parameterMethod($dialogMethod);
    $dialog->options()->useToken = FALSE;
    $this->assertFalse($dialog->isSubmitted());
  }

  /**
  * @covers PapayaUiDialog::execute
  */
  public function testExecuteExpectingTrue() {
    $owner = new stdClass();
    $request = $this->getMock(PapayaRequest::class, array('getMethod'));
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('post'));
    $tokens = $this->getMock(PapayaUiTokens::class, array('create', 'validate'));
    $tokens
      ->expects($this->once())
      ->method('validate')
      ->with($this->equalTo('TOKEN_STRING'), $this->equalTo($owner))
      ->will($this->returnValue(TRUE));
    $fields = $this->getMock(
      PapayaUiDialogFields::class, array('owner', 'appendTo', 'validate', 'collect')
    );
    $fields
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(TRUE));
    $fields
      ->expects($this->once())
      ->method('collect');
    $buttons = $this->getMock(
      PapayaUiDialogButtons::class, array('owner', 'appendTo', 'collect')
    );
    $buttons
      ->expects($this->once())
      ->method('collect');
    $dialog = new PapayaUiDialog($owner);
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $dialog->tokens($tokens);
    $dialog->fields($fields);
    $dialog->buttons($buttons);
    $dialog->parameters(
      new PapayaRequestParameters(
        array(
          'confirmation' => '40cd750bba9870f18aada2478b24840a',
          'token' => 'TOKEN_STRING'
        )
      )
    );
    $this->assertTrue($dialog->execute());
  }

  /**
  * @covers PapayaUiDialog::execute
  */
  public function testExecuteWithoutTokenExpectingTrue() {
    $owner = new stdClass();
    $request = $this->getMock(PapayaRequest::class, array('getMethod'));
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('post'));
    $fields = $this->getMock(
      PapayaUiDialogFields::class, array('owner', 'appendTo', 'validate', 'collect')
    );
    $fields
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(TRUE));
    $fields
      ->expects($this->once())
      ->method('collect');
    $buttons = $this->getMock(
      PapayaUiDialogButtons::class, array('owner', 'appendTo', 'collect')
    );
    $buttons
      ->expects($this->once())
      ->method('collect');
    $dialog = new PapayaUiDialog($owner);
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $dialog->options()->useToken = FALSE;
    $dialog->fields($fields);
    $dialog->buttons($buttons);
    $dialog->parameters(
      new PapayaRequestParameters(
        array(
          'confirmation' => '40cd750bba9870f18aada2478b24840a'
        )
      )
    );
    $this->assertTrue($dialog->execute());
  }

  /**
  * @covers PapayaUiDialog::execute
  */
  public function testExecuteWithoutTokenExpectingFalse() {
    $owner = new stdClass();
    $request = $this->getMock(PapayaRequest::class, array('getMethod'));
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('post'));
    $fields = $this->getMock(
      PapayaUiDialogFields::class, array('owner', 'appendTo', 'validate', 'collect')
    );
    $fields
      ->expects($this->once())
      ->method('validate')
      ->will($this->returnValue(FALSE));
    $dialog = new PapayaUiDialog($owner);
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $dialog->options()->useToken = FALSE;
    $dialog->fields($fields);
    $dialog->parameters(
      new PapayaRequestParameters(
        array(
          'confirmation' => '40cd750bba9870f18aada2478b24840a'
        )
      )
    );
    $this->assertFalse($dialog->execute());
  }

  /**
  * @covers PapayaUiDialog::execute
  */
  public function testExecuteWrongMethodAndCachedResult() {
    $owner = new stdClass();
    $request = $this->getMock(PapayaRequest::class, array('getMethod'));
    $request
      ->expects($this->once())
      ->method('getMethod')
      ->will($this->returnValue('get'));
    $dialog = new PapayaUiDialog($owner);
    $dialog->papaya($this->mockPapaya()->application(array('Request' => $request)));
    $this->assertFalse($dialog->execute());
    $this->assertFalse($dialog->execute());
  }

  /**************************
  * Data Provider
  **************************/

  public static function provideValidMethodPairs() {
    return array(
      'get + get' => array('get', PapayaUidialog::METHOD_GET),
      'post + post' => array('post', PapayaUidialog::METHOD_POST),
      'get + mixed' => array('get', PapayaUidialog::METHOD_MIXED),
      'post + mixed' => array('post', PapayaUidialog::METHOD_MIXED)
    );
  }

  public static function provideInvalidMethodPairs() {
    return array(
      'get + post' => array('get', PapayaUidialog::METHOD_POST),
      'post + get' => array('post', PapayaUidialog::METHOD_GET),
      'head + mixed' => array('head', PapayaUidialog::METHOD_MIXED)
    );
  }

  public static function provideMethodsAndStringRepresentations() {
    return array(
      array('post', PapayaUiControlInteractive::METHOD_POST),
      array('get', PapayaUiControlInteractive::METHOD_GET),
      array('post', PapayaUiControlInteractive::METHOD_MIXED)
    );
  }

  public static function provideHiddenDataAndResult() {
    return array(
      array(
        NULL,
        array('foo' => 'bar', 'bar' => 'foo'),
        '<test>'.
          '<input type="hidden" name="foo" value="bar"/>'.
          '<input type="hidden" name="bar" value="foo"/>'.
          '</test>'
      ),
      array(
        'group',
        array('foo' => 'bar', 'bar' => 'foo'),
        '<test>'.
          '<input type="hidden" name="group[foo]" value="bar"/>'.
          '<input type="hidden" name="group[bar]" value="foo"/>'.
          '</test>'
      ),
      array(
        'group',
        array('foo' => array(TRUE, FALSE), 'bar' => array(21, 42)),
        '<test>'.
          '<input type="hidden" name="group[foo][0]" value="1"/>'.
          '<input type="hidden" name="group[foo][1]" value=""/>'.
          '<input type="hidden" name="group[bar][0]" value="21"/>'.
          '<input type="hidden" name="group[bar][1]" value="42"/>'.
          '</test>'
      )
    );
  }

  public static function provideParameterNameSamples() {
    return array(
      array('foo', 'foo', PapayaUiDialog::METHOD_GET),
      array('foo', 'foo', PapayaUiDialog::METHOD_POST),
      array('foo', 'foo', PapayaUiDialog::METHOD_MIXED),
      array('foo*bar', 'foo/bar', PapayaUiDialog::METHOD_GET),
      array('foo[bar]', 'foo/bar', PapayaUiDialog::METHOD_POST),
      array('foo[bar]', 'foo/bar', PapayaUiDialog::METHOD_MIXED)
    );
  }
}

class PapayaUiDialog_TestProxy extends PapayaUiDialog {

  public function getMethodString() {
    return parent::getMethodString();
  }

  public function appendHidden(PapayaXmlElement $parent,
                               PapayaRequestParameters $values,
                               $path = NULL) {
    return parent::appendHidden($parent, $values, $path);
  }
}
