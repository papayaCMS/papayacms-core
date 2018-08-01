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

class PapayaTemplateEngineTest extends \PapayaTestCase {

  /**
  * @covers \PapayaTemplateEngine::parameters
  */
  public function testParametersSetter() {
    $parameters = $this->createMock(\Papaya\BaseObject\Options\Collection::class);
    $engine = new \PapayaTemplateEngine_TestProxy();
    $engine->parameters($parameters);
    $this->assertAttributeSame(
      $parameters,
      '_parameters',
      $engine
    );
  }

  /**
  * @covers \PapayaTemplateEngine::parameters
  */
  public function testParametersGetter() {
    $parameters = $this->createMock(\Papaya\BaseObject\Options\Collection::class);
    $engine = new \PapayaTemplateEngine_TestProxy();
    $engine->parameters($parameters);
    $this->assertSame(
      $parameters,
      $engine->parameters()
    );
  }

  /**
  * @covers \PapayaTemplateEngine::parameters
  */
  public function testParametesImplicitCreate() {
    $engine = new \PapayaTemplateEngine_TestProxy();
    $this->assertInstanceOf(
      \Papaya\BaseObject\Options\Collection::class,
      $engine->parameters()
    );
  }

  /**
  * @covers \PapayaTemplateEngine::parameters
  */
  public function testParametesImplizitCreateWithArray() {
    $engine = new \PapayaTemplateEngine_TestProxy();
    $this->assertInstanceOf(
      \Papaya\BaseObject\Options\Collection::class,
      $engine->parameters(array())
    );
  }

  /**
  * @covers \PapayaTemplateEngine::parameters
  */
  public function testParametesWithInvalidArgument() {
    $engine = new \PapayaTemplateEngine_TestProxy();
    $this->expectException(InvalidArgumentException::class);
    $engine->parameters(23);
  }

  /**
  * @covers \PapayaTemplateEngine::loaders
  */
  public function testLoadersSetter() {
    $loaders = $this
      ->getMockBuilder(\Papaya\BaseObject\Collection::class)
      ->setConstructorArgs(array(\PapayaTemplateEngineValuesLoadable::class))
      ->getMock();
    $loaders
      ->expects($this->any())
      ->method('getItemClass')
      ->will($this->returnValue(\PapayaTemplateEngineValuesLoadable::class));
    $engine = new \PapayaTemplateEngine_TestProxy();
    $engine->loaders($loaders);
    $this->assertAttributeSame(
      $loaders,
      '_loaders',
      $engine
    );
  }

  /**
  * @covers \PapayaTemplateEngine::loaders
  */
  public function testLoadersSetterWithInvalidObjectList() {
    $loaders = $this
      ->getMockBuilder(\Papaya\BaseObject\Collection::class)
      ->setConstructorArgs(array(stdClass::class))
      ->getMock();
    $loaders
      ->expects($this->any())
      ->method('getItemClass')
      ->will($this->returnValue(stdClass::class));
    $engine = new \PapayaTemplateEngine_TestProxy();

    $this->expectException(InvalidArgumentException::class);
    $engine->loaders($loaders);
  }

  /**
  * @covers \PapayaTemplateEngine::loaders
  */
  public function testLoadersGetter() {
    $loaders = $this
      ->getMockBuilder(\Papaya\BaseObject\Collection::class)
      ->setConstructorArgs(array(\PapayaTemplateEngineValuesLoadable::class))
      ->getMock();
    $loaders
      ->expects($this->any())
      ->method('getItemClass')
      ->will($this->returnValue(\PapayaTemplateEngineValuesLoadable::class));
    $engine = new \PapayaTemplateEngine_TestProxy();
    $this->assertSame(
      $loaders,
      $engine->loaders($loaders)
    );
  }

  /**
  * @covers \PapayaTemplateEngine::loaders
  */
  public function testLoadersGetterWithImplicitCreate() {
    $engine = new \PapayaTemplateEngine_TestProxy();
    $this->assertInstanceOf(
      \Papaya\BaseObject\Collection::class,
      $engine->loaders()
    );
    $this->assertEquals(
      \PapayaTemplateEngineValuesLoadable::class,
      $engine->loaders()->getItemClass()
    );
  }

  /**
  * @covers \PapayaTemplateEngine::values
  */
  public function testValuesSetterWithDomDocument() {
    $document = new DOMDocument;
    $document->appendChild($document->createElement('test'));
    $engine = new \PapayaTemplateEngine_TestProxy();
    $engine->values($document);
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */'<?xml version="1.0" encoding="UTF-8"?><test/>',
      $engine->values()->saveXML($engine->getContext())
    );
  }

  /**
  * @covers \PapayaTemplateEngine::values
  */
  public function testValuesSetterWithDomElement() {
    $document = new DOMDocument;
    $document->appendChild($document->createElement('test'));
    $node = $document->createElement('sample');
    $document->documentElement->appendChild($node);
    $engine = new \PapayaTemplateEngine_TestProxy();
    $engine->values($node);
    $this->assertEquals(
    /** @lang XML */'<sample/>', $engine->values()->saveXML($engine->getContext())
    );
  }

  /**
  * @covers \PapayaTemplateEngine::values
  */
  public function testValuesSetterWithPapayaXmlElement() {
    $document = new \Papaya\Xml\Document();
    $node = $document->appendElement('test');
    $engine = new \PapayaTemplateEngine_TestProxy();
    $engine->values($node);
    $this->assertEquals(
    /** @lang XML */'<test/>', $engine->values()->saveXML($engine->getContext())
    );
  }

  /**
  * @covers \PapayaTemplateEngine::values
  */
  public function testValuesSetterUsingLoaderMechanism() {
    $document = new \Papaya\Xml\Document();
    $document->appendElement('test');
    $loaderFailure = $this->createMock(\PapayaTemplateEngineValuesLoadable::class);
    $loaderFailure
      ->expects($this->once())
      ->method('load')
      ->with($this->equalTo('DATA'))
      ->will($this->returnValue(FALSE));
    $loaderSuccess = $this->createMock(\PapayaTemplateEngineValuesLoadable::class);
    $loaderSuccess
      ->expects($this->once())
      ->method('load')
      ->with($this->equalTo('DATA'))
      ->will($this->returnValue($document));
    $engine = new \PapayaTemplateEngine_TestProxy();
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
  * @covers \PapayaTemplateEngine::values
  */
  public function testValuesSetterWithInvalidValue() {
    $engine = new \PapayaTemplateEngine_TestProxy();
    $this->expectException(UnexpectedValueException::class);
    $engine->values('load');
  }

  /**
  * @covers \PapayaTemplateEngine::values
  */
  public function testValuesGetterWithImplicitCreate() {
    $engine = new \PapayaTemplateEngine_TestProxy();
    $this->assertInstanceOf(
      'DOMDocument', $engine->values()
    );
  }

  /**
  * @covers \PapayaTemplateEngine::getContext
  */
  public function testGetContextExpectingNull() {
    $engine = new \PapayaTemplateEngine_TestProxy();
    $this->assertNull($engine->getContext());
  }

  /**
  * @covers \PapayaTemplateEngine::getContext
  */
  public function testGetContextExpectingXmlElement() {
    $document = new \Papaya\Xml\Document();
    $node = $document->appendElement('test');
    $engine = new \PapayaTemplateEngine_TestProxy();
    $engine->values($node);
    $this->assertSame($node, $engine->getContext());
  }

  /**
  * @covers \PapayaTemplateEngine::__get
  */
  public function testMagicMethodGetForLoaders() {
    $engine = new \PapayaTemplateEngine_TestProxy();
    $loaders = $engine->loaders;
    $this->assertInstanceOf(
      \Papaya\BaseObject\Collection::class,
      $loaders
    );
    $this->assertEquals(
      \PapayaTemplateEngineValuesLoadable::class,
      $loaders->getItemClass()
    );
  }

  /**
  * @covers \PapayaTemplateEngine::__set
  */
  public function testMagicMethodSetForLoaders() {
    $loaders = $this
      ->getMockBuilder(\Papaya\BaseObject\Collection::class)
      ->setConstructorArgs(array(\PapayaTemplateEngineValuesLoadable::class))
      ->getMock();
    $loaders
      ->expects($this->any())
      ->method('getItemClass')
      ->will($this->returnValue(\PapayaTemplateEngineValuesLoadable::class));
    $engine = new \PapayaTemplateEngine_TestProxy();
    $engine->loaders = $loaders;
    $this->assertAttributeSame(
      $loaders, '_loaders', $engine
    );
  }

  /**
  * @covers \PapayaTemplateEngine::__get
  */
  public function testMagicMethodGetForParameters() {
    $engine = new \PapayaTemplateEngine_TestProxy();
    $parameters = $engine->parameters;
    $this->assertInstanceOf(
      \Papaya\BaseObject\Options\Collection::class, $parameters
    );
  }

  /**
  * @covers \PapayaTemplateEngine::__set
  */
  public function testMagicMethodSetForParameters() {
    $parameters = $this->createMock(\Papaya\BaseObject\Options\Collection::class);
    $engine = new \PapayaTemplateEngine_TestProxy();
    $engine->parameters = $parameters;
    $this->assertAttributeSame(
      $parameters, '_parameters', $engine
    );
  }

  /**
  * @covers \PapayaTemplateEngine::__get
  */
  public function testMagicMethodGetForValues() {
    $engine = new \PapayaTemplateEngine_TestProxy();
    $values = $engine->values;
    $this->assertInstanceOf(
      'DOMDocument', $values
    );
  }

  /**
  * @covers \PapayaTemplateEngine::__set
  */
  public function testMagicMethodSetForValues() {
    $document = new \Papaya\Xml\Document();
    $node = $document->appendElement('node');
    $document->appendChild($node);
    $engine = new \PapayaTemplateEngine_TestProxy();
    $engine->values = $node;
    $this->assertSame(
      $document, $engine->values()
    );
    $this->assertSame(
      $node, $engine->getContext()
    );
  }

  /**
  * @covers \PapayaTemplateEngine::__get
  */
  public function testMagicMethodsGetForUndefinedProperty() {
    $engine = new \PapayaTemplateEngine_TestProxy();
    $this->expectError(E_NOTICE);
    $engine->dynamic_property;
  }

  /**
  * @covers \PapayaTemplateEngine::__set
  */
  public function testMagicMethodsSetForDynamicProperty() {
    $engine = new \PapayaTemplateEngine_TestProxy();
    $engine->dynamic_property = 'dynamic_value';
    $this->assertSame(
      'dynamic_value',
      $engine->dynamic_property
    );
  }
}

/**
 * @property \Papaya\BaseObject\Collection|\Papaya\BaseObject\Options\Collection|\Papaya\Xml\Document dynamic_property
 */
class PapayaTemplateEngine_TestProxy extends \PapayaTemplateEngine {

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
