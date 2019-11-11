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

namespace Papaya {

  use Papaya\Response\Content\Text;
  use Papaya\XML\Document;

  require_once __DIR__.'/../../bootstrap.php';

  \Papaya\Test\TestCase::defineConstantDefaults(
    [
      'PAPAYA_DB_TBL_AUTHOPTIONS',
      'PAPAYA_DB_TBL_AUTHUSER',
      'PAPAYA_DB_TBL_AUTHGROUPS',
      'PAPAYA_DB_TBL_AUTHLINK',
      'PAPAYA_DB_TBL_AUTHPERM',
      'PAPAYA_DB_TBL_AUTHMODPERMS',
      'PAPAYA_DB_TBL_AUTHMODPERMLINKS',
      'PAPAYA_DB_TBL_SURFER'
    ]
  );

  /**
   * @covers \Papaya\Template
   */
  class TemplateTest extends TestCase {

    public function testValuesGetAfterSet() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Template\Values $values */
      $values = $this->createMock(Template\Values::class);

      $template = new Template_TestProxy();
      $template->values($values);
      $this->assertSame($values, $template->values());
    }

    public function testValuesGetImplicitCreate() {

      $template = new Template_TestProxy();
      $this->assertInstanceOf(Template\Values::class, $template->values());
    }

    public function testSetXml() {
      $document = $this->createMock(XML\Document::class);
      $document
        ->expects($this->once())
        ->method('loadXml')
        ->with(
        /** @lang XML */
          '<page/>'
        );
      /** @var \PHPUnit_Framework_MockObject_MockObject|Template\Values $values */
      $values = $this->createMock(Template\Values::class);
      $values
        ->expects($this->once())
        ->method('document')
        ->willReturn($document);

      $template = new Template_TestProxy();
      $template->values($values);
      $template->setXML(
      /** @lang XML */
        '<page/>'
      );
    }

    public function testGetXml() {
      $document = Document::createFromXML('<page/>');

      /** @var \PHPUnit_Framework_MockObject_MockObject|Template\Values $values */
      $values = $this->createMock(Template\Values::class);
      $values
        ->method('document')
        ->willReturn($document);

      $template = new Template_TestProxy();
      $template->values($values);
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<page/>', $template->getXML()
      );
    }

    public function testParametersGetAfterSet() {
      $parameters = $this->createMock(Template\Parameters::class);

      $template = new Template_TestProxy();
      $template->parameters($parameters);
      $this->assertSame($parameters, $template->parameters());
    }

    public function testParametersGetImplicitCreate() {

      $template = new Template_TestProxy();
      $this->assertInstanceOf(Template\Parameters::class, $template->parameters());
    }

    public function testParametersGetImplicitWithArray() {

      $template = new Template_TestProxy();
      $parameters = $template->parameters(['foo' => 'bar']);
      $this->assertArrayHasKey('FOO', iterator_to_array($parameters));
      $this->assertEquals('bar', $parameters['FOO']);
    }

    public function testErrorsGetAfterSet() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|XML\Errors $errors */
      $errors = $this->createMock(XML\Errors::class);

      $template = new Template_TestProxy();
      $template->errors($errors);
      $this->assertSame($errors, $template->errors());
    }

    public function testErrorsGetImplicitCreate() {

      $template = new Template_TestProxy();
      $this->assertInstanceOf(XML\Errors::class, $template->errors());
    }

    public function testAddWithDomNode() {
      $document = new XML\Document();

      $template = new Template_TestProxy();
      $template->add($document->createElement('foo'));
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<page><centercol><foo/></centercol></page>',
        $template->getXML()
      );
    }

    public function testAddWithXmlAppendable() {
      $appendable = $this->createMock(XML\Appendable::class);
      $appendable
        ->expects($this->once())
        ->method('appendTo');

      $template = new Template_TestProxy();
      $template->add($appendable);
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<page><centercol/></page>',
        $template->getXML()
      );
    }

    public function testAddWithXmlString() {

      $template = new Template_TestProxy();
      $template->add(
      /** @lang XML */
        '<foo/>'
      );
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<page><centercol><foo/></centercol></page>',
        $template->getXML()
      );
    }

    public function testAddWithString() {

      $template = new Template_TestProxy();
      $template->add('foo');
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<page><centercol>foo</centercol></page>',
        $template->getXML()
      );
    }

    public function testAddWithStringContainingInvalidCharacters() {

      $template = new Template_TestProxy();
      $template->add(
      /** @lang Text */
        'foo &auml; & <bar/>'
      );
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<page><centercol>foo ä &amp; <bar/></centercol></page>',
        $template->getXML()
      );
    }

    /**
     * @dataProvider providesDataForAddWithTarget
     * @param string $expected
     * @param string $method
     */
    public function testAddWithDynamicMethods($expected, $method) {

      $template = new Template_TestProxy();
      $template->$method(
      /** @lang XML */
        '<foo/>'
      );
      $this->assertXmlStringEqualsXmlString(
        $expected,
        $template->getXML()
      );
    }

    public function testAddData() {

      $template = new Template_TestProxy();
      $template->addData(
      /** @lang XML */
        '<foo/>'
      );
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<page><centercol><foo/></centercol></page>',
        $template->getXML()
      );
    }

    public function testSetParam() {

      $template = new Template_TestProxy();
      /** @noinspection PhpDeprecationInspection */
      $template->setParam('foo', 'bar');
      $parameters = $template->parameters();
      $this->assertArrayHasKey('FOO', iterator_to_array($parameters));
      $this->assertEquals('bar', $parameters['FOO']);
    }

    public function testXml() {
      $document = Document::createFromXML('<page/>');
      /** @var \PHPUnit_Framework_MockObject_MockObject|Template\Values $values */
      $values = $this->createMock(Template\Values::class);
      $values
        ->method('document')
        ->willReturn($document);

      $template = new Template_TestProxy();
      $template->values($values);
      /** @noinspection PhpDeprecationInspection */
      $this->assertXmlStringEqualsXmlString(
      /** @lang XML */
        '<page/>', $template->xml()
      );
    }

    public function testGetOutput() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Template $template */
      $template = $this->createPartialMock(Template_TestProxy::class, ['parse']);
      $template
        ->expects($this->once())
        ->method('parse')
        ->with(Template::STRIP_XML_EMPTY_NAMESPACE)
        ->willReturn('success');
      $template->papaya($this->mockPapaya()->application());
      $this->assertSame(
        'success',
        $template->getOutput()
      );
    }

    public function testXhtml() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|Template $template */
      $template = $this->createPartialMock(Template_TestProxy::class, ['parse']);
      $template
        ->expects($this->once())
        ->method('parse')
        ->with(0)
        ->willReturn('success');
      $template->papaya($this->mockPapaya()->application());
      $this->assertSame(
        'success',
        $template->xhtml()
      );
    }

    public function testGetOutputGenerateDebugXML() {
      $user = $this->createMock(\base_auth::class);
      $user->method('isLoggedIn')->willReturn(TRUE);

      $request = $this->mockPapaya()->request(['XML' => 1]);
      $response = $this->mockPapaya()->response();
      $response
        ->expects($this->once())
        ->method('setContentType')
        ->with('text/xml', 'utf-8');
      $response
        ->expects($this->once())
        ->method('content')
        ->with(new Text("<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<page/>\n"));
      $response
        ->expects($this->once())
        ->method('send')
        ->with(TRUE);

      /** @var \PHPUnit_Framework_MockObject_MockObject|Template $template */
      $template = new Template_TestProxy();
      $template->papaya(
        $this->mockPapaya()->application(
          [
            'request' => $request,
            'response' => $response,
            'administrationUser' => $user
          ]
        )
      );
      $template->getOutput();
    }

    /**
     * @param string $expected
     * @param string $input
     * @param int $options
     * @dataProvider provideXMLToClean
     */
    public function testClean($expected, $input, $options = 0) {
      $template = new Template_TestProxy();
      $this->assertEquals($expected, $template->clean($input, $options));
    }

    public function testCleanPassthroughForFalse() {
      $template = new Template_TestProxy();
      $this->assertFalse($template->clean(FALSE, 0));
    }

    /**********************
     * Data Provider
     *********************/

    public static function providesDataForAddWithTarget() {
      return [
        'navigation' => [
          /** @lang XML */
          '<page><leftcol><foo/></leftcol></page>',
          'addNavigation'
        ],
        'content' => [
          /** @lang XML */
          '<page><centercol><foo/></centercol></page>',
          'addContent'
        ],
        'information' => [
          /** @lang XML */
          '<page><rightcol><foo/></rightcol></page>',
          'addInformation'
        ],
        'menu' => [
          /** @lang XML */
          '<page><menus><foo/></menus></page>',
          'addMenu'
        ],
        'script' => [
          /** @lang XML */
          '<page><scripts><foo/></scripts></page>',
          'addScript'
        ],
        // old methods, bc
        'left' => [
          /** @lang XML */
          '<page><leftcol><foo/></leftcol></page>',
          'addLeft'
        ],
        'center' => [
          /** @lang XML */
          '<page><centercol><foo/></centercol></page>',
          'addCenter'
        ],
        'right' => [
          /** @lang XML */
          '<page><rightcol><foo/></rightcol></page>',
          'addRight'
        ],
      ];
    }

    public static function provideXMLToClean() {
      return [
        'Strip Whitespace In Tag' => [
          '<foo><bar /></foo>',
          '<foo   ><bar  /></foo   >'
        ],
        'Keep XML Declaration' => [
          '<?xml version="1.0"?><foo/>',
          '<?xml version="1.0"?><foo/>'
        ],
        'Remove XML Declaration' => [
          '<foo/>',
          '<?xml version="1.0"?><foo/>',
          Template::STRIP_XML_PI
        ],
        'Keep Empty Namespace Declaration' => [
          '<foo xmlns:foo=""/>',
          '<foo xmlns:foo=""/>'
        ],
        'Remove Empty Namespace Declaration' => [
          '<foo/>',
          '<foo xmlns:foo=""/>',
          Template::STRIP_XML_EMPTY_NAMESPACE
        ],
        'Keep Default Namespace Declaration' => [
          '<foo xmlns="urn:bar"/>',
          '<foo xmlns="urn:bar"/>',
        ],
        'Remove Default Namespace Declaration' => [
          '<foo/>',
          '<foo xmlns="urn:bar"/>',
          Template::STRIP_XML_DEFAULT_NAMESPACE
        ],
        'Remove All' => [
          '<foo/>',
          '<?xml version="1.0"?><foo xmlns="urn:bar" xmlns:foo="   "/>',
          Template::STRIP_ALL
        ],
      ];
    }
  }

  class Template_TestProxy extends Template {

    public function parse($options = self::STRIP_XML_EMPTY_NAMESPACE) {
    }

    public function clean($xml, $options) {
      return parent::clean($xml, $options);
    }
  }
}
