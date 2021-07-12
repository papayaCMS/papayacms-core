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

namespace Papaya\Template {

  use LogicException;
  use Papaya\BaseObject\Collection;
  use Papaya\BaseObject\Options\Collection as OptionsCollection;
  use Papaya\TestFramework\TestCase;
  use Papaya\XML\Document as XMLDocument;

  require_once __DIR__.'/../../../bootstrap.php';

  /**
   * @covers \Papaya\Template\Engine
   */
  class EngineTest extends TestCase {

    public function testParametersGetter() {
      $parameters = $this->createMock(OptionsCollection::class);
      $engine = new Engine_TestProxy();
      $engine->parameters($parameters);
      $this->assertSame(
        $parameters,
        $engine->parameters()
      );
    }

    public function testParametersImplicitCreate() {
      $engine = new Engine_TestProxy();
      $this->assertInstanceOf(
        OptionsCollection::class,
        $engine->parameters()
      );
    }

    public function testParametersImplicitCreateWithArray() {
      $engine = new Engine_TestProxy();
      $this->assertInstanceOf(
        OptionsCollection::class,
        $engine->parameters([])
      );
    }

    public function testParametersWithInvalidArgument() {
      $engine = new Engine_TestProxy();
      $this->expectException(\InvalidArgumentException::class);
      $engine->parameters(23);
    }

    public function testLoadersSetterWithInvalidObjectList() {
      $loaders = $this
        ->getMockBuilder(Collection::class)
        ->setConstructorArgs([\stdClass::class])
        ->getMock();
      $loaders
        ->method('getItemClass')
        ->willReturn(\stdClass::class);
      $engine = new Engine_TestProxy();

      $this->expectException(\InvalidArgumentException::class);
      $engine->loaders($loaders);
    }

    public function testLoadersGetter() {
      $loaders = $this
        ->getMockBuilder(Collection::class)
        ->setConstructorArgs([Engine\Values\Loadable::class])
        ->getMock();
      $loaders
        ->method('getItemClass')
        ->willReturn(Engine\Values\Loadable::class);
      $engine = new Engine_TestProxy();
      $this->assertSame(
        $loaders,
        $engine->loaders($loaders)
      );
    }

    public function testLoadersGetterWithImplicitCreate() {
      $engine = new Engine_TestProxy();
      $this->assertInstanceOf(
        Collection::class,
        $engine->loaders()
      );
      $this->assertEquals(
        Engine\Values\Loadable::class,
        $engine->loaders()->getItemClass()
      );
    }

    public function testValuesSetterWithDomDocument() {
      $document = new \DOMDocument();
      $document->appendChild($document->createElement('test'));
      $engine = new Engine_TestProxy();
      $engine->values($document);
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<?xml version="1.0" encoding="UTF-8"?><test/>',
        $engine->values()->saveXML($engine->getContext())
      );
    }

    public function testValuesSetterWithDomElement() {
      $document = new \DOMDocument();
      $document->appendChild($document->createElement('test'));
      $node = $document->createElement('sample');
      $document->documentElement->appendChild($node);
      $engine = new Engine_TestProxy();
      $engine->values($node);
      $this->assertEquals(
      /** @lang XML */
        '<sample/>', $engine->values()->saveXML($engine->getContext())
      );
    }

    public function testValuesSetterWithXMLElement() {
      $document = new XMLDocument();
      $node = $document->appendElement('test');
      $engine = new Engine_TestProxy();
      $engine->values($node);
      $this->assertEquals(
      /** @lang XML */
        '<test/>', $engine->values()->saveXML($engine->getContext())
      );
    }

    public function testValuesSetterUsingLoaderMechanism() {
      $document = new XMLDocument();
      $document->appendElement('test');
      $loaderFailure = $this->createMock(Engine\Values\Loadable::class);
      $loaderFailure
        ->expects($this->once())
        ->method('load')
        ->with($this->equalTo('DATA'))
        ->willReturn(FALSE);
      $loaderSuccess = $this->createMock(Engine\Values\Loadable::class);
      $loaderSuccess
        ->expects($this->once())
        ->method('load')
        ->with($this->equalTo('DATA'))
        ->willReturn($document);
      $engine = new Engine_TestProxy();
      $engine
        ->loaders()
        ->add($loaderFailure)
        ->add($loaderSuccess);
      $engine->values('DATA');
      $this->assertSame(
        $document, $engine->values()
      );
      $this->assertNull($engine->getContext());
    }

    public function testValuesSetterWithInvalidValue() {
      $engine = new Engine_TestProxy();
      $this->expectException(\UnexpectedValueException::class);
      $engine->values('load');
    }

    public function testValuesGetterWithImplicitCreate() {
      $engine = new Engine_TestProxy();
      $this->assertInstanceOf(
        'DOMDocument', $engine->values()
      );
    }

    public function testGetContextExpectingNull() {
      $engine = new Engine_TestProxy();
      $this->assertNull($engine->getContext());
    }

    public function testGetContextExpectingXmlElement() {
      $document = new XMLDocument();
      $node = $document->appendElement('test');
      $engine = new Engine_TestProxy();
      $engine->values($node);
      $this->assertSame($node, $engine->getContext());
    }

    public function testMagicMethodGetForLoaders() {
      $engine = new Engine_TestProxy();
      $this->assertTrue(isset($engine->loaders));
      $loaders = $engine->loaders;
      $this->assertInstanceOf(
        Collection::class,
        $loaders
      );
      $this->assertEquals(
        Engine\Values\Loadable::class,
        $loaders->getItemClass()
      );
    }

    public function testMagicMethodSetForLoaders() {
      $loaders = $this
        ->getMockBuilder(Collection::class)
        ->setConstructorArgs([Engine\Values\Loadable::class])
        ->getMock();
      $loaders
        ->method('getItemClass')
        ->willReturn(Engine\Values\Loadable::class);
      $engine = new Engine_TestProxy();
      $engine->loaders = $loaders;
      $this->assertSame(
        $loaders, $engine->loaders
      );
    }

    public function testMagicMethodGetForParameters() {
      $engine = new Engine_TestProxy();
      $this->assertTrue(isset($engine->parameters));
      $parameters = $engine->parameters;
      $this->assertInstanceOf(
        OptionsCollection::class, $parameters
      );
    }

    public function testMagicMethodSetForParameters() {
      $parameters = $this->createMock(OptionsCollection::class);
      $engine = new Engine_TestProxy();
      $engine->parameters = $parameters;
      $this->assertSame(
        $parameters, $engine->parameters
      );
    }

    public function testMagicMethodGetForValues() {
      $engine = new Engine_TestProxy();
      $this->assertTrue(isset($engine->values));
      $values = $engine->values;
      $this->assertInstanceOf(
        'DOMDocument', $values
      );
    }

    public function testMagicMethodSetForValues() {
      $document = new XMLDocument();
      $node = $document->appendElement('node');
      $document->appendChild($node);
      $engine = new Engine_TestProxy();
      $engine->values = $node;
      $this->assertSame(
        $document, $engine->values()
      );
      $this->assertSame(
        $node, $engine->getContext()
      );
    }

    public function testMagicMethodsGetForUndefinedProperty() {
      $engine = new Engine_TestProxy();
      $this->assertFalse(isset($engine->dynamic_property));
      $this->assertNull(
        $engine->dynamic_property
      );
    }

    public function testMagicMethodsSetForDynamicProperty() {
      $engine = new Engine_TestProxy();
      $engine->dynamic_property = 'dynamic_value';
      $this->assertSame(
        'dynamic_value',
        $engine->dynamic_property
      );
    }

    public function testMagicMethodsUnsetExpectingException() {
      $engine = new Engine_TestProxy();
      $this->expectException(LogicException::class);
      unset($engine->property);
    }
  }

  /**
   * @property Collection|OptionsCollection|XMLDocument dynamic_property
   */
  class Engine_TestProxy extends Engine {

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
}
