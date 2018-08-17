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

namespace Papaya\Template;

/** @noinspection UnknownInspectionInspection */
/** @noinspection XmlUnusedNamespaceDeclaration */
require_once __DIR__.'/../../../bootstrap.php';

class XSLTTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Template\XSLT
   */
  public function testConstructorWithXslFileArgument() {
    $template = new XSLT('test.xsl');
    $this->assertEquals('test.xsl', $template->getXslFile());
  }

  /**
   * @covers \Papaya\Template\XSLT
   */
  public function testSetXslFile() {
    $template = new XSLT();
    $template->setXsl('test.xsl');
    $this->assertEquals('test.xsl', $template->getXslFile());
  }

  /**
   * @covers \Papaya\Template\XSLT
   */
  public function testEngineGetAfterSet() {
    $engine = $this->createMock(Engine\XSLT::class);
    $template = new XSLT();
    $template->engine($engine);
    $this->assertSame($engine, $template->engine());
  }

  /**
   * @covers \Papaya\Template\XSLT
   */
  public function testEngineGetImplicitCreate() {
    $template = new XSLT();
    $template->papaya($this->mockPapaya()->application());
    $this->assertInstanceOf(Engine\XSLT::class, $template->engine());
  }

  /**
   * @covers \Papaya\Template\XSLT
   */
  public function testParseExpectingFalse() {
    $engine = $this->getEngineFixture(FALSE);
    $template = new XSLT('test.xsl');
    $template->papaya($this->mockPapaya()->application());
    $template->engine($engine);
    $this->assertFalse($template->parse());
  }

  /**
   * @covers \Papaya\Template\XSLT
   */
  public function testParseExpectingText() {
    $engine = $this->getEngineFixture('success');
    $template = new XSLT('test.xsl');
    $template->papaya($this->mockPapaya()->application());
    $template->engine($engine);
    $this->assertEquals('success', $template->parse());
  }

  /**
   * @covers \Papaya\Template\XSLT
   */
  public function testParseExpectingXml() {
    $engine = $this->getEngineFixture(
    /** @lang XML */
      '<?xml version="1.0"?><test xmlns="urn:default" xmlns:empty="" xmlns:foo="urn:bar"/>'
    );
    $template = new XSLT('test.xsl');
    $template->papaya($this->mockPapaya()->application());
    $template->engine($engine);
    $this->assertEquals(
    /** @lang XML */
      '<?xml version="1.0"?><test xmlns="urn:default" xmlns:foo="urn:bar"/>',
      $template->parse()
    );
  }

  /**
   * @covers \Papaya\Template\XSLT
   */
  public function testParseExpectingXmlRemoveXmlPi() {
    $engine = $this->getEngineFixture(
    /** @lang XML */
      '<?xml version="1.0"?><test xmlns:empty="" xmlns:foo="urn:bar"/>'
    );
    $template = new XSLT('test.xsl');
    $template->papaya($this->mockPapaya()->application());
    $template->engine($engine);
    $this->assertEquals(
    /** @lang XML */
      '<test xmlns:empty="" xmlns:foo="urn:bar"/>',
      $template->parse(XSLT::STRIP_XML_PI)
    );
  }

  /**
   * @covers \Papaya\Template\XSLT
   */
  public function testParseExpectingXmlRemoveXmlNamespaces() {
    $engine = $this->getEngineFixture(
    /** @lang XML */
      '<?xml version="1.0"?><test xmlns:empty="" xmlns:foo="urn:bar"/>'
    );
    $template = new XSLT('test.xsl');
    $template->papaya($this->mockPapaya()->application());
    $template->engine($engine);
    $this->assertEquals(
    /** @lang XML */
      '<?xml version="1.0"?><test xmlns:foo="urn:bar"/>',
      $template->parse(XSLT::STRIP_XML_EMPTY_NAMESPACE)
    );
  }

  /**
   * @covers \Papaya\Template\XSLT
   */
  public function testParseWithProfiling() {
    $messages = $this->createMock(\Papaya\Message\Manager::class);
    $messages
      ->expects($this->exactly(2))
      ->method('log');
    $engine = $this->getEngineFixture(FALSE);
    $template = new XSLT('test.xsl');
    $template->papaya(
      $this->mockPapaya()->application(
        array(
          'options' => $this->mockPapaya()->options(array('PAPAYA_LOG_RUNTIME_TEMPLATE' => TRUE)),
          'messages' => $messages
        )
      )
    );
    $template->engine($engine);
    $template->parse();
  }

  /**
   * @covers \Papaya\Template\XSLT
   */
  public function testGetOutput() {
    $engine = $this->getEngineFixture('success');
    $template = new XSLT('test.xsl');
    $template->papaya($this->mockPapaya()->application());
    $template->engine($engine);
    $this->assertEquals('success', $template->getOutput());
  }

  /**
   * @covers \Papaya\Template\XSLT
   */
  public function testGetOutputExpectingFalse() {
    $engine = $this->getEngineFixture(FALSE);
    $template = new XSLT('test.xsl');
    $template->papaya($this->mockPapaya()->application());
    $template->engine($engine);
    $this->assertFalse($template->getOutput());
  }

  /**
   * @covers \Papaya\Template\XSLT
   */
  public function testGetOutputExpectingXmlOutput() {
    $response = $this->createMock(\Papaya\Response::class);
    $response
      ->expects($this->once())
      ->method('setContentType')
      ->with('text/xml', 'utf-8');
    $response
      ->expects($this->once())
      ->method('content')
      ->with($this->isInstanceOf(\Papaya\Response\Content\Text::class));
    $response
      ->expects($this->once())
      ->method('send')
      ->with(TRUE);

    $template = new XSLT('test.xsl');
    $template->papaya(
      $this->mockPapaya()->application(
        array(
          'request' => $this->mockPapaya()->request(array('XML' => TRUE)),
          'response' => $response,
          'administrationUser' => $this->mockPapaya()->user(TRUE)
        )
      )
    );
    $this->assertFalse($template->getOutput());
  }

  /**
   * @covers \Papaya\Template\XSLT
   */
  public function testXhtml() {
    $engine = $this->getEngineFixture(FALSE);
    $template = new XSLT('test.xsl');
    $template->papaya($this->mockPapaya()->application());
    $template->engine($engine);
    /** @noinspection PhpDeprecationInspection */
    $this->assertFalse($template->xhtml());
  }

  /**********************
   * Fixtures
   *********************/

  /**
   * @param $result
   * @return \PHPUnit_Framework_MockObject_MockObject|Engine\XSLT
   */
  public function getEngineFixture($result) {
    $parameters = $this->createMock(Parameters::class);
    $parameters
      ->expects($this->once())
      ->method('assign');

    $engine = $this->createMock(Engine\XSLT::class);
    $engine
      ->expects($this->once())
      ->method('setTemplateFile')
      ->with('test.xsl');
    $engine
      ->expects($this->any())
      ->method('__get')
      ->with('parameters')
      ->will($this->returnValue($parameters));
    $engine
      ->expects($this->once())
      ->method('values')
      ->with($this->isInstanceOf(\DOMDocument::class));
    $engine
      ->expects($this->once())
      ->method('prepare');
    $engine
      ->expects($this->once())
      ->method('run');
    $engine
      ->expects($this->once())
      ->method('getResult')
      ->will($this->returnValue($result));
    return $engine;
  }
}
