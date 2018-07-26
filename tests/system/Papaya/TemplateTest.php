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

use Papaya\Template;

require_once __DIR__.'/../../bootstrap.php';

class PapayaTemplateTest extends PapayaTestCase {

  /**
   * @covers Template
   */
  public function testValuesGetAfterSet() {
    $values = $this->createMock(PapayaTemplateValues::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|Template $template */
    $template = $this->getMockForAbstractClass(Template::class);
    $template->values($values);
    $this->assertSame($values, $template->values());
  }

  /**
   * @covers Template
   */
  public function testValuesGetImplicitCreate() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Template $template */
    $template = $this->getMockForAbstractClass(Template::class);
    $this->assertInstanceOf(PapayaTemplateValues::class, $template->values());
  }

  /**
   * @covers Template
   */
  public function testSetXml() {
    $document = $this->createMock(PapayaXmlDocument::class);
    $document
      ->expects($this->once())
      ->method('loadXml')
      ->with(/** @lang XML */'<page/>');
    $values = $this->createMock(PapayaTemplateValues::class);
    $values
      ->expects($this->once())
      ->method('document')
      ->will($this->returnValue($document));
    /** @var PHPUnit_Framework_MockObject_MockObject|Template $template */
    $template = $this->getMockForAbstractClass(Template::class);
    $template->values($values);
    $template->setXml(/** @lang XML */'<page/>');
  }

  /**
   * @covers Template
   */
  public function testGetXml() {
    $document = $this->createMock(PapayaXmlDocument::class);
    $document
      ->expects($this->once())
      ->method('saveXml')
      ->will($this->returnValue(/** @lang XML */'<page/>'));
    $values = $this->createMock(PapayaTemplateValues::class);
    $values
      ->expects($this->once())
      ->method('document')
      ->will($this->returnValue($document));
    /** @var PHPUnit_Framework_MockObject_MockObject|Template $template */
    $template = $this->getMockForAbstractClass(Template::class);
    $template->values($values);
    $this->assertEquals(/** @lang XML */'<page/>', $template->getXml());
  }

  /**
   * @covers Template
   */
  public function testParametersGetAfterSet() {
    $parameters = $this->createMock(PapayaTemplateParameters::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|Template $template */
    $template = $this->getMockForAbstractClass(Template::class);
    $template->parameters($parameters);
    $this->assertSame($parameters, $template->parameters());
  }

  /**
   * @covers Template
   */
  public function testParametersGetImplicitCreate() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Template $template */
    $template = $this->getMockForAbstractClass(Template::class);
    $this->assertInstanceOf(PapayaTemplateParameters::class, $template->parameters());
  }

  /**
   * @covers Template
   */
  public function testParametersGetImplicitWithArray() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Template $template */
    $template = $this->getMockForAbstractClass(Template::class);
    $parameters = $template->parameters(array('foo' => 'bar'));
    $this->assertArrayHasKey('FOO', iterator_to_array($parameters));
    $this->assertEquals('bar', $parameters['FOO']);
  }


  /**
   * @covers Template
   */
  public function testErrorsGetAfterSet() {
    $errors = $this->createMock(PapayaXmlErrors::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|Template $template */
    $template = $this->getMockForAbstractClass(Template::class);
    $template->errors($errors);
    $this->assertSame($errors, $template->errors());
  }

  /**
   * @covers Template
   */
  public function testErrorsGetImplicitCreate() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Template $template */
    $template = $this->getMockForAbstractClass(Template::class);
    $this->assertInstanceOf(PapayaXmlErrors::class, $template->errors());
  }

  /**
   * @covers Template
   */
  public function testAddWithDomNode() {
    $document = new \PapayaXmlDocument();
    /** @var PHPUnit_Framework_MockObject_MockObject|Template $template */
    $template = $this->getMockForAbstractClass(Template::class);
    $template->add($document->createElement('foo'));
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */'<page><centercol><foo/></centercol></page>',
      $template->getXml()
    );
  }

  /**
   * @covers Template
   */
  public function testAddWithXmlAppendable() {
    $appendable = $this->createMock(PapayaXmlAppendable::class);
    $appendable
      ->expects($this->once())
      ->method('appendTo');
    /** @var PHPUnit_Framework_MockObject_MockObject|Template $template */
    $template = $this->getMockForAbstractClass(Template::class);
    $template->add($appendable);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */'<page><centercol/></page>',
      $template->getXml()
    );
  }

  /**
   * @covers Template
   */
  public function testAddWithXmlString() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Template $template */
    $template = $this->getMockForAbstractClass(Template::class);
    $template->add(/** @lang XML */'<foo/>');
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */'<page><centercol><foo/></centercol></page>',
      $template->getXml()
    );
  }

  /**
   * @covers Template
   */
  public function testAddWithString() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Template $template */
    $template = $this->getMockForAbstractClass(Template::class);
    $template->add('foo');
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */'<page><centercol>foo</centercol></page>',
      $template->getXml()
    );
  }

  /**
   * @covers Template
   */
  public function testAddWithStringContainingInvalidCharacters() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Template $template */
    $template = $this->getMockForAbstractClass(Template::class);
    $template->add(
      /** @lang Text */
      'foo &auml; & <bar/>'
    );
    $this->assertXmlStringEqualsXmlString(
    /** @lang XML */'<page><centercol>foo ä &amp; <bar/></centercol></page>',
      $template->getXml()
    );
  }

  /**
   * @covers       Template
   * @dataProvider providesDataForAddWithTarget
   * @param string $expected
   * @param string $method
   */
  public function testAddWithDynamicMethods($expected, $method) {
    /** @var PHPUnit_Framework_MockObject_MockObject|Template $template */
    $template = $this->getMockForAbstractClass(Template::class);
    $template->$method(/** @lang XML */'<foo/>');
    $this->assertXmlStringEqualsXmlString(
      $expected,
      $template->getXml()
    );
  }

  /**
   * @covers Template
   */
  public function testAddWithInvalidContentExpectingException() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Template $template */
    $template = $this->getMockForAbstractClass(Template::class);
    $this->expectException(LogicException::class);
    /** @noinspection PhpParamsInspection */
    $template->addContent();
  }

  /**
   * @covers Template
   */
  public function testAddWithInvalidTargetExpectingException() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Template $template */
    $template = $this->getMockForAbstractClass(Template::class);
    $this->expectException(LogicException::class);
    /** @noinspection PhpUndefinedMethodInspection */
    $template->addInvalidTarget(/** @lang XML */'<foo/>');
  }

  /**
   * @covers Template
   */
  public function testCallInvalidDynamicMethodExpectingException() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Template $template */
    $template = $this->getMockForAbstractClass(Template::class);
    $this->expectException(LogicException::class);
    /** @noinspection PhpUndefinedMethodInspection */
    $template->invalidMethod(/** @lang XML */'<foo/>');
  }


  /**
   * @covers Template
   */
  public function testAddData() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Template $template */
    $template = $this->getMockForAbstractClass(Template::class);
    $template->addData(/** @lang XML */'<foo/>');
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */'<page><centercol><foo/></centercol></page>',
      $template->getXml()
    );
  }

  /**
   * @covers Template
   */
  public function testSetParam() {
    /** @var PHPUnit_Framework_MockObject_MockObject|Template $template */
    $template = $this->getMockForAbstractClass(Template::class);
    /** @noinspection PhpDeprecationInspection */
    $template->setParam('foo', 'bar');
    $parameters = $template->parameters();
    $this->assertArrayHasKey('FOO', iterator_to_array($parameters));
    $this->assertEquals('bar', $parameters['FOO']);
  }

  /**
   * @covers Template
   */
  public function testXml() {
    $document = $this->createMock(PapayaXmlDocument::class);
    $document
      ->expects($this->once())
      ->method('saveXml')
      ->will($this->returnValue(/** @lang XML */'<page/>'));
    $values = $this->createMock(PapayaTemplateValues::class);
    $values
      ->expects($this->once())
      ->method('document')
      ->will($this->returnValue($document));
    /** @var PHPUnit_Framework_MockObject_MockObject|Template $template */
    $template = $this->getMockForAbstractClass(Template::class);
    $template->values($values);
    /** @noinspection PhpDeprecationInspection */
    $this->assertEquals(/** @lang XML */'<page/>', $template->xml());
  }

  /**********************
   * Data Provider
   *********************/

  public static function providesDataForAddWithTarget() {
    return array(
      'navigation' => array(
        /** @lang XML */'<page><leftcol><foo/></leftcol></page>',
        'addNavigation'
      ),
      'content' => array(
        /** @lang XML */'<page><centercol><foo/></centercol></page>',
        'addContent'
      ),
      'information' => array(
        /** @lang XML */'<page><rightcol><foo/></rightcol></page>',
        'addInformation'
      ),
      'menu' => array(
        /** @lang XML */'<page><menus><foo/></menus></page>',
        'addMenu'
      ),
      'script' => array(
        /** @lang XML */'<page><scripts><foo/></scripts></page>',
        'addScript'
      ),
      // old methods, bc
      'left' => array(
        /** @lang XML */'<page><leftcol><foo/></leftcol></page>',
        'addLeft'
      ),
      'center' => array(
        /** @lang XML */'<page><centercol><foo/></centercol></page>',
        'addCenter'
      ),
      'right' => array(
        /** @lang XML */'<page><rightcol><foo/></rightcol></page>',
        'addRight'
      ),
    );
  }
}
