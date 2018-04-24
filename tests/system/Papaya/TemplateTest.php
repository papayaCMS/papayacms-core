<?php
require_once __DIR__.'/../../bootstrap.php';

class PapayaTemplateTest extends PapayaTestCase {

  /**
   * @covers PapayaTemplate
   */
  public function testValuesGetAfterSet() {
    $values = $this->createMock(PapayaTemplateValues::class);
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $template->values($values);
    $this->assertSame($values, $template->values());
  }

  /**
   * @covers PapayaTemplate
   */
  public function testValuesGetImplicitCreate() {
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $this->assertInstanceOf(PapayaTemplateValues::class, $template->values());
  }

  /**
   * @covers PapayaTemplate
   */
  public function testSetXml() {
    $document = $this->createMock(PapayaXmlDocument::class);
    $document
      ->expects($this->once())
      ->method('loadXml')
      ->with('<page/>');
    $values = $this->createMock(PapayaTemplateValues::class);
    $values
      ->expects($this->once())
      ->method('document')
      ->will($this->returnValue($document));
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $template->values($values);
    $template->setXml('<page/>');
  }

  /**
   * @covers PapayaTemplate
   */
  public function testGetXml() {
    $document = $this->createMock(PapayaXmlDocument::class);
    $document
      ->expects($this->once())
      ->method('saveXml')
      ->will($this->returnValue('<page/>'));
    $values = $this->createMock(PapayaTemplateValues::class);
    $values
      ->expects($this->once())
      ->method('document')
      ->will($this->returnValue($document));
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $template->values($values);
    $this->assertEquals('<page/>', $template->getXml());
  }

  /**
   * @covers PapayaTemplate
   */
  public function testParametersGetAfterSet() {
    $parameters = $this->createMock(PapayaTemplateParameters::class);
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $template->parameters($parameters);
    $this->assertSame($parameters, $template->parameters());
  }

  /**
   * @covers PapayaTemplate
   */
  public function testParametersGetImplicitCreate() {
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $this->assertInstanceOf(PapayaTemplateParameters::class, $template->parameters());
  }

  /**
   * @covers PapayaTemplate
   */
  public function testParametersGetImplicitWithArray() {
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $parameters = $template->parameters(array('foo' => 'bar'));
    $this->assertArrayHasKey('FOO', iterator_to_array($parameters));
    $this->assertEquals('bar', $parameters['FOO']);
  }


  /**
   * @covers PapayaTemplate
   */
  public function testErrorsGetAfterSet() {
    $errors = $this->createMock(PapayaXmlErrors::class);
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $template->errors($errors);
    $this->assertSame($errors, $template->errors());
  }

  /**
   * @covers PapayaTemplate
   */
  public function testErrorsGetImplicitCreate() {
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $this->assertInstanceOf(PapayaXmlErrors::class, $template->errors());
  }

  /**
   * @covers PapayaTemplate
   */
  public function testAddWithDomNode() {
    $dom = new PapayaXmlDocument();
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $template->add($dom->createElement('foo'));
    $this->assertXmlStringEqualsXmlString(
      '<page><centercol><foo/></centercol></page>',
      $template->getXml()
    );
  }

  /**
   * @covers PapayaTemplate
   */
  public function testAddWithXmlAppendable() {
    $appendable = $this->createMock(PapayaXmlAppendable::class);
    $appendable
      ->expects($this->once())
      ->method('appendTo');
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $template->add($appendable);
    $this->assertXmlStringEqualsXmlString(
      '<page><centercol></centercol></page>',
      $template->getXml()
    );
  }

  /**
   * @covers PapayaTemplate
   */
  public function testAddWithXmlString() {
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $template->add('<foo/>');
    $this->assertXmlStringEqualsXmlString(
      '<page><centercol><foo/></centercol></page>',
      $template->getXml()
    );
  }

  /**
   * @covers PapayaTemplate
   */
  public function testAddWithString() {
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $template->add('foo');
    $this->assertXmlStringEqualsXmlString(
      '<page><centercol>foo</centercol></page>',
      $template->getXml()
    );
  }

  /**
   * @covers PapayaTemplate
   */
  public function testAddWithStringContainingInvalidCharacters() {
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $template->add('foo &auml; & <bar/>');
    $this->assertXmlStringEqualsXmlString(
      '<page><centercol>foo ä &amp; <bar/></centercol></page>',
      $template->getXml()
    );
  }

  /**
   * @covers PapayaTemplate
   * @dataProvider providesDataForAddWithTarget
   */
  public function testAddWithDynamicMethods($expected, $method) {
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    call_user_func(array($template, $method), ('<foo/>'));
    $this->assertXmlStringEqualsXmlString(
      $expected,
      $template->getXml()
    );
  }

  /**
   * @covers PapayaTemplate
   */
  public function testAddWithInvalidContentExpectingException() {
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $this->setExpectedException(LogicException::class);
    /** @noinspection PhpParamsInspection */
    $template->addContent();
  }

  /**
   * @covers PapayaTemplate
   */
  public function testAddWithInvalidTargetExpectingException() {
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $this->setExpectedException(LogicException::class);
    /** @noinspection PhpUndefinedMethodInspection */
    $template->addInvalidTarget('<foo/>');
  }

  /**
   * @covers PapayaTemplate
   */
  public function testCallInvalidDynamicMethodExpectingException() {
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $this->setExpectedException(LogicException::class);
    /** @noinspection PhpUndefinedMethodInspection */
    $template->invalidMethod('<foo/>');
  }


  /**
   * @covers PapayaTemplate
   */
  public function testAddData() {
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $template->addData('<foo/>');
    $this->assertXmlStringEqualsXmlString(
      '<page><centercol><foo/></centercol></page>',
      $template->getXml()
    );
  }

  /**
   * @covers PapayaTemplate
   */
  public function testSetParam() {
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $template->setParam('foo', 'bar');
    $parameters = $template->parameters();
    $this->assertArrayHasKey('FOO', iterator_to_array($parameters));
    $this->assertEquals('bar', $parameters['FOO']);
  }

  /**
   * @covers PapayaTemplate
   */
  public function testXml() {
    $document = $this->createMock(PapayaXmlDocument::class);
    $document
      ->expects($this->once())
      ->method('saveXml')
      ->will($this->returnValue('<page/>'));
    $values = $this->createMock(PapayaTemplateValues::class);
    $values
      ->expects($this->once())
      ->method('document')
      ->will($this->returnValue($document));
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $template->values($values);
    $this->assertEquals('<page/>', $template->xml());
  }

  /**********************
   * Data Provider
   *********************/

  public static function providesDataForAddWithTarget() {
    return array(
      'navigation' => array(
        '<page><leftcol><foo/></leftcol></page>',
        'addNavigation'
      ),
      'content' => array(
        '<page><centercol><foo/></centercol></page>',
        'addContent'
      ),
      'information' => array(
        '<page><rightcol><foo/></rightcol></page>',
        'addInformation'
      ),
      'menu' => array(
        '<page><menus><foo/></menus></page>',
        'addMenu'
      ),
      'script' => array(
        '<page><scripts><foo/></scripts></page>',
        'addScript'
      ),
      // old methods, bc
      'left' => array(
        '<page><leftcol><foo/></leftcol></page>',
        'addLeft'
      ),
      'center' => array(
        '<page><centercol><foo/></centercol></page>',
        'addCenter'
      ),
      'right' => array(
        '<page><rightcol><foo/></rightcol></page>',
        'addRight'
      ),
    );
  }
}
