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

require_once __DIR__.'/../../bootstrap.php';

class PapayaTemplateTest extends PapayaTestCase {

  /**
   * @covers PapayaTemplate
   */
  public function testValuesGetAfterSet() {
    $values = $this->createMock(PapayaTemplateValues::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $template */
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $template->values($values);
    $this->assertSame($values, $template->values());
  }

  /**
   * @covers PapayaTemplate
   */
  public function testValuesGetImplicitCreate() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $template */
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
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $template */
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
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $template */
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $template->values($values);
    $this->assertEquals('<page/>', $template->getXml());
  }

  /**
   * @covers PapayaTemplate
   */
  public function testParametersGetAfterSet() {
    $parameters = $this->createMock(PapayaTemplateParameters::class);
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $template */
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $template->parameters($parameters);
    $this->assertSame($parameters, $template->parameters());
  }

  /**
   * @covers PapayaTemplate
   */
  public function testParametersGetImplicitCreate() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $template */
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $this->assertInstanceOf(PapayaTemplateParameters::class, $template->parameters());
  }

  /**
   * @covers PapayaTemplate
   */
  public function testParametersGetImplicitWithArray() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $template */
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
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $template */
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $template->errors($errors);
    $this->assertSame($errors, $template->errors());
  }

  /**
   * @covers PapayaTemplate
   */
  public function testErrorsGetImplicitCreate() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $template */
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $this->assertInstanceOf(PapayaXmlErrors::class, $template->errors());
  }

  /**
   * @covers PapayaTemplate
   */
  public function testAddWithDomNode() {
    $dom = new PapayaXmlDocument();
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $template */
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
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $template */
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
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $template */
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
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $template */
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
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $template */
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
   * @param string $expected
   * @param string $method
   */
  public function testAddWithDynamicMethods($expected, $method) {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $template */
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $template->$method('<foo/>');
    $this->assertXmlStringEqualsXmlString(
      $expected,
      $template->getXml()
    );
  }

  /**
   * @covers PapayaTemplate
   */
  public function testAddWithInvalidContentExpectingException() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $template */
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $this->expectException(LogicException::class);
    /** @noinspection PhpParamsInspection */
    $template->addContent();
  }

  /**
   * @covers PapayaTemplate
   */
  public function testAddWithInvalidTargetExpectingException() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $template */
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $this->expectException(LogicException::class);
    /** @noinspection PhpUndefinedMethodInspection */
    $template->addInvalidTarget('<foo/>');
  }

  /**
   * @covers PapayaTemplate
   */
  public function testCallInvalidDynamicMethodExpectingException() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $template */
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $this->expectException(LogicException::class);
    /** @noinspection PhpUndefinedMethodInspection */
    $template->invalidMethod('<foo/>');
  }


  /**
   * @covers PapayaTemplate
   */
  public function testAddData() {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $template */
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
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $template */
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    /** @noinspection PhpDeprecationInspection */
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
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaTemplate $template */
    $template = $this->getMockForAbstractClass(PapayaTemplate::class);
    $template->values($values);
    /** @noinspection PhpDeprecationInspection */
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
