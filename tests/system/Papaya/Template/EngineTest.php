<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaTemplateEngineTest extends PapayaTestCase {

  /**
  * @covers PapayaTemplateEngine::parameters
  */
  public function testParametersSetter() {
    $parameters = $this->getMock('PapayaObjectOptionsList');
    $engine = new PapayaTemplateEngine_TestProxy();
    $engine->parameters($parameters);
    $this->assertAttributeSame(
      $parameters,
      '_parameters',
      $engine
    );
  }

  /**
  * @covers PapayaTemplateEngine::parameters
  */
  public function testParametersGetter() {
    $parameters = $this->getMock('PapayaObjectOptionsList');
    $engine = new PapayaTemplateEngine_TestProxy();
    $engine->parameters($parameters);
    $this->assertSame(
      $parameters,
      $engine->parameters()
    );
  }

  /**
  * @covers PapayaTemplateEngine::parameters
  */
  public function testParametesImplicitCreate() {
    $engine = new PapayaTemplateEngine_TestProxy();
    $this->assertInstanceOf(
      'PapayaObjectOptionsList',
      $engine->parameters()
    );
  }

  /**
  * @covers PapayaTemplateEngine::parameters
  */
  public function testParametesImplizitCreateWithArray() {
    $engine = new PapayaTemplateEngine_TestProxy();
    $this->assertInstanceOf(
      'PapayaObjectOptionsList',
      $engine->parameters(array())
    );
  }

  /**
  * @covers PapayaTemplateEngine::parameters
  */
  public function testParametesWithInvalidArgument() {
    $engine = new PapayaTemplateEngine_TestProxy();
    $this->setExpectedException('InvalidArgumentException');
    $engine->parameters(23);
  }

  /**
  * @covers PapayaTemplateEngine::loaders
  */
  public function testLoadersSetter() {
    $loaders = $this->getMock(
      'PapayaObjectList',
      array(),
      array('PapayaTemplateEngineValuesLoadable')
    );
    $loaders
      ->expects($this->any())
      ->method('getItemClass')
      ->will($this->returnValue('PapayaTemplateEngineValuesLoadable'));
    $engine = new PapayaTemplateEngine_TestProxy();
    $engine->loaders($loaders);
    $this->assertAttributeSame(
      $loaders,
      '_loaders',
      $engine
    );
  }

  /**
  * @covers PapayaTemplateEngine::loaders
  */
  public function testLoadersSetterWithInvalidObjectList() {
    $loaders = $this->getMock('PapayaObjectList', array(), array('stdClass'));
    $loaders
      ->expects($this->any())
      ->method('getItemClass')
      ->will($this->returnValue('stdClass'));
    $engine = new PapayaTemplateEngine_TestProxy();

    $this->setExpectedException('InvalidArgumentException');
    $engine->loaders($loaders);
  }

  /**
  * @covers PapayaTemplateEngine::loaders
  */
  public function testLoadersGetter() {
    $loaders = $this->getMock(
      'PapayaObjectList',
      array(),
      array('PapayaTemplateEngineValuesLoadable')
    );
    $loaders
      ->expects($this->any())
      ->method('getItemClass')
      ->will($this->returnValue('PapayaTemplateEngineValuesLoadable'));
    $engine = new PapayaTemplateEngine_TestProxy();
    $this->assertSame(
      $loaders,
      $engine->loaders($loaders)
    );
  }

  /**
  * @covers PapayaTemplateEngine::loaders
  */
  public function testLoadersGetterWithImplicitCreate() {
    $engine = new PapayaTemplateEngine_TestProxy();
    $this->assertInstanceOf(
      'PapayaObjectList',
      $engine->loaders()
    );
    $this->assertEquals(
      'PapayaTemplateEngineValuesLoadable',
      $engine->loaders()->getItemClass()
    );
  }

  /**
  * @covers PapayaTemplateEngine::values
  */
  public function testValuesSetterWithDomDocument() {
    $document = new DOMDocument;
    $document->appendChild($document->createElement('test'));
    $engine = new PapayaTemplateEngine_TestProxy();
    $engine->values($document);
    $this->assertXmlStringEqualsXmlString(
      '<?xml version="1.0" encoding="UTF-8"?><test/>',
      $engine->values()->saveXml($engine->getContext())
    );
  }

  /**
  * @covers PapayaTemplateEngine::values
  */
  public function testValuesSetterWithDomElement() {
    $document = new DOMDocument;
    $document->appendChild($document->createElement('test'));
    $node = $document->createElement('sample');
    $document->documentElement->appendChild($node);
    $engine = new PapayaTemplateEngine_TestProxy();
    $engine->values($node);
    $this->assertEquals(
      '<sample/>', $engine->values()->saveXml($engine->getContext())
    );
  }

  /**
  * @covers PapayaTemplateEngine::values
  */
  public function testValuesSetterWithPapayaXmlElement() {
    $document = new PapayaXmlDocument();
    $node = $document->appendElement('test');
    $engine = new PapayaTemplateEngine_TestProxy();
    $engine->values($node);
    $this->assertEquals(
      '<test/>', $engine->values()->saveXml($engine->getContext())
    );
  }

  /**
  * @covers PapayaTemplateEngine::values
  */
  public function testValuesSetterUsingLoaderMechanism() {
    $document = new PapayaXmlDocument();
    $document->appendElement('test');
    $loaderFailure = $this->getMock('PapayaTemplateEngineValuesLoadable');
    $loaderFailure
      ->expects($this->once())
      ->method('load')
      ->with($this->equalTo('DATA'))
      ->will($this->returnValue(FALSE));
    $loaderSuccess = $this->getMock('PapayaTemplateEngineValuesLoadable');
    $loaderSuccess
      ->expects($this->once())
      ->method('load')
      ->with($this->equalTo('DATA'))
      ->will($this->returnValue($document));
    $engine = new PapayaTemplateEngine_TestProxy();
    $engine
      ->loaders()
      ->add($loaderFailure)
      ->add($loaderSuccess);
    $engine->values('DATA');
    $this->assertAttributeSame(
      $document, '_values', $engine
    );
    $this->assertNull($engine->getContext());
  }

  /**
  * @covers PapayaTemplateEngine::values
  */
  public function testValuesSetterWithInvalidValue() {
    $engine = new PapayaTemplateEngine_TestProxy();
    $this->setExpectedException('UnexpectedValueException');
    $engine->values('load');
  }

  /**
  * @covers PapayaTemplateEngine::values
  */
  public function testValuesGetterWithImplicitCreate() {
    $engine = new PapayaTemplateEngine_TestProxy();
    $this->assertInstanceOf(
      'DOMDocument', $engine->values()
    );
  }

  /**
  * @covers PapayaTemplateEngine::getContext
  */
  public function testGetContextExpectingNull() {
    $engine = new PapayaTemplateEngine_TestProxy();
    $this->assertNull($engine->getContext());
  }

  /**
  * @covers PapayaTemplateEngine::getContext
  */
  public function testGetContextExpectingXmlElement() {
    $document = new PapayaXmlDocument();
    $node = $document->appendElement('test');
    $engine = new PapayaTemplateEngine_TestProxy();
    $engine->values($node);
    $this->assertSame($node, $engine->getContext());
  }

  /**
  * @covers PapayaTemplateEngine::__get
  */
  public function testMagicMethodGetForLoaders() {
    $engine = new PapayaTemplateEngine_TestProxy();
    $loaders = $engine->loaders;
    $this->assertInstanceOf(
      'PapayaObjectList',
      $loaders
    );
    $this->assertEquals(
      'PapayaTemplateEngineValuesLoadable',
      $loaders->getItemClass()
    );
  }

  /**
  * @covers PapayaTemplateEngine::__set
  */
  public function testMagicMethodSetForLoaders() {
    $loaders = $this->getMock(
      'PapayaObjectList',
      array(),
      array('PapayaTemplateEngineValuesLoadable')
    );
    $loaders
      ->expects($this->any())
      ->method('getItemClass')
      ->will($this->returnValue('PapayaTemplateEngineValuesLoadable'));
    $engine = new PapayaTemplateEngine_TestProxy();
    $engine->loaders = $loaders;
    $this->assertAttributeSame(
      $loaders, '_loaders', $engine
    );
  }

  /**
  * @covers PapayaTemplateEngine::__get
  */
  public function testMagicMethodGetForParameters() {
    $engine = new PapayaTemplateEngine_TestProxy();
    $parameters = $engine->parameters;
    $this->assertInstanceOf(
      'PapayaObjectOptionsList', $parameters
    );
  }

  /**
  * @covers PapayaTemplateEngine::__set
  */
  public function testMagicMethodSetForParameters() {
    $parameters = $this->getMock('PapayaObjectOptionsList');
    $engine = new PapayaTemplateEngine_TestProxy();
    $engine->parameters = $parameters;
    $this->assertAttributeSame(
      $parameters, '_parameters', $engine
    );
  }

  /**
  * @covers PapayaTemplateEngine::__get
  */
  public function testMagicMethodGetForValues() {
    $engine = new PapayaTemplateEngine_TestProxy();
    $values = $engine->values;
    $this->assertInstanceOf(
      'DOMDocument', $values
    );
  }

  /**
  * @covers PapayaTemplateEngine::__set
  */
  public function testMagicMethodSetForValues() {
    $document = new PapayaXmlDocument();
    $node = $document->appendElement('node');
    $document->appendChild($node);
    $engine = new PapayaTemplateEngine_TestProxy();
    $engine->values = $node;
    $this->assertSame(
      $document, $engine->values()
    );
    $this->assertSame(
      $node, $engine->getContext()
    );
  }

  /**
  * @covers PapayaTemplateEngine::__get
  */
  public function testMagicMethodsGetForUndefinedProperty() {
    $engine = new PapayaTemplateEngine_TestProxy();
    $this->setExpectedException(
      'PHPUnit_Framework_Error_Notice'
    );
    $engine->dynamic_property;
  }

  /**
  * @covers PapayaTemplateEngine::__set
  */
  public function testMagicMethodsSetForDynamicProperty() {
    $engine = new PapayaTemplateEngine_TestProxy();
    $engine->dynamic_property = 'dynamic_value';
    $this->assertSame(
      'dynamic_value',
      $engine->dynamic_property
    );
  }
}

class PapayaTemplateEngine_TestProxy extends PapayaTemplateEngine {

  public function prepare() {
  }

  public function run() {
  }

  public function getResult() {
  }

  public function setTemplateString($string) {
  }

  public function setTemplateFile($filename) {
  }

}