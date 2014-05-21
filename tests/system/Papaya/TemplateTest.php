<?php
require_once(dirname(__FILE__).'/../../bootstrap.php');

class PapayaTemplateTest extends PapayaTestCase {

  /**
   * @covers PapayaTemplate
   */
  public function testValuesGetAfterSet() {
    $values = $this->getMock('PapayaTemplateValues');
    $template = $this->getMockForAbstractClass('PapayaTemplate');
    $template->values($values);
    $this->assertSame($values, $template->values());
  }

  /**
   * @covers PapayaTemplate
   */
  public function testValuesGetImplicitCreate() {
    $template = $this->getMockForAbstractClass('PapayaTemplate');
    $this->assertInstanceOf('PapayaTemplateValues', $template->values());
  }

  /**
   * @covers PapayaTemplate
   */
  public function testSetXml() {
    $document = $this->getMock('PapayaXmlDocument');
    $document
      ->expects($this->once())
      ->method('loadXml')
      ->with('<page/>');
    $values = $this->getMock('PapayaTemplateValues');
    $values
      ->expects($this->once())
      ->method('document')
      ->will($this->returnValue($document));
    $template = $this->getMockForAbstractClass('PapayaTemplate');
    $template->values($values);
    $template->setXml('<page/>');
  }

  /**
   * @covers PapayaTemplate
   */
  public function testGetXml() {
    $document = $this->getMock('PapayaXmlDocument');
    $document
      ->expects($this->once())
      ->method('saveXml')
      ->will($this->returnValue('<page/>'));
    $values = $this->getMock('PapayaTemplateValues');
    $values
      ->expects($this->once())
      ->method('document')
      ->will($this->returnValue($document));
    $template = $this->getMockForAbstractClass('PapayaTemplate');
    $template->values($values);
    $this->assertEquals('<page/>', $template->getXml());
  }

  /**
   * @covers PapayaTemplate
   */
  public function testParametersGetAfterSet() {
    $parameters = $this->getMock('PapayaTemplateParameters');
    $template = $this->getMockForAbstractClass('PapayaTemplate');
    $template->parameters($parameters);
    $this->assertSame($parameters, $template->parameters());
  }

  /**
   * @covers PapayaTemplate
   */
  public function testParametersGetImplicitCreate() {
    $template = $this->getMockForAbstractClass('PapayaTemplate');
    $this->assertInstanceOf('PapayaTemplateParameters', $template->parameters());
  }

  /**
   * @covers PapayaTemplate
   */
  public function testParametersGetImplicitWithArray() {
    $template = $this->getMockForAbstractClass('PapayaTemplate');
    $parameters = $template->parameters(array('foo' => 'bar'));
    $this->assertArrayHasKey('FOO', iterator_to_array($parameters));
    $this->assertEquals('bar', $parameters['FOO']);
  }


  /**
   * @covers PapayaTemplate
   */
  public function testErrorsGetAfterSet() {
    $errors = $this->getMock('PapayaXmlErrors');
    $template = $this->getMockForAbstractClass('PapayaTemplate');
    $template->errors($errors);
    $this->assertSame($errors, $template->errors());
  }

  /**
   * @covers PapayaTemplate
   */
  public function testErrorsGetImplicitCreate() {
    $template = $this->getMockForAbstractClass('PapayaTemplate');
    $this->assertInstanceOf('PapayaXmlErrors', $template->errors());
  }

  /**
   * @covers PapayaTemplate
   */
  public function testAddWithDomNode() {
    $dom = new PapayaXmlDocument();
    $template = $this->getMockForAbstractClass('PapayaTemplate');
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
    $appendable = $this->getMock('PapayaXmlAppendable');
    $appendable
      ->expects($this->once())
      ->method('appendTo');
    $template = $this->getMockForAbstractClass('PapayaTemplate');
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
    $template = $this->getMockForAbstractClass('PapayaTemplate');
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
    $template = $this->getMockForAbstractClass('PapayaTemplate');
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
    $template = $this->getMockForAbstractClass('PapayaTemplate');
    $template->add('foo &auml; & <bar/>');
    $this->assertXmlStringEqualsXmlString(
      '<page><centercol>foo ä &amp; <bar/></centercol></page>',
      $template->getXml()
    );
  }

  /**
   * @covers PapayaTemplate
   * @dataProvider testAddWithTarget
   */
  public function testAddWithDynamicMethods($expected, $method) {
    $template = $this->getMockForAbstractClass('PapayaTemplate');
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
    $template = $this->getMockForAbstractClass('PapayaTemplate');
    $this->setExpectedException('LogicException');
    /** @noinspection PhpParamsInspection */
    $template->addContent();
  }

  /**
   * @covers PapayaTemplate
   */
  public function testAddWithInvalidTargetExpectingException() {
    $template = $this->getMockForAbstractClass('PapayaTemplate');
    $this->setExpectedException('LogicException');
    /** @noinspection PhpUndefinedMethodInspection */
    $template->addInvalidTarget('<foo/>');
  }

  /**
   * @covers PapayaTemplate
   */
  public function testCallInvalidDynamicMethodExpectingException() {
    $template = $this->getMockForAbstractClass('PapayaTemplate');
    $this->setExpectedException('LogicException');
    /** @noinspection PhpUndefinedMethodInspection */
    $template->invalidMethod('<foo/>');
  }


  /**
   * @covers PapayaTemplate
   */
  public function testAddData() {
    $template = $this->getMockForAbstractClass('PapayaTemplate');
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
    $template = $this->getMockForAbstractClass('PapayaTemplate');
    $template->setParam('foo', 'bar');
    $parameters = $template->parameters();
    $this->assertArrayHasKey('FOO', iterator_to_array($parameters));
    $this->assertEquals('bar', $parameters['FOO']);
  }

  /**
   * @covers PapayaTemplate
   */
  public function testXml() {
    $document = $this->getMock('PapayaXmlDocument');
    $document
      ->expects($this->once())
      ->method('saveXml')
      ->will($this->returnValue('<page/>'));
    $values = $this->getMock('PapayaTemplateValues');
    $values
      ->expects($this->once())
      ->method('document')
      ->will($this->returnValue($document));
    $template = $this->getMockForAbstractClass('PapayaTemplate');
    $template->values($values);
    $this->assertEquals('<page/>', $template->xml());
  }

  /**********************
   * Data Provider
   *********************/

  public static function testAddWithTarget() {
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