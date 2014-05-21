<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaTemplateXsltTest extends PapayaTestCase {

  /**
   * @covers PapayaTemplateXslt
   */
  public function testConstructorWithXslFileArgument() {
    $template = new PapayaTemplateXslt('test.xsl');
    $this->assertEquals('test.xsl', $template->getXslFile());
  }

  /**
   * @covers PapayaTemplateXslt
   */
  public function testSetXslFile() {
    $template = new PapayaTemplateXslt();
    $template->setXsl('test.xsl');
    $this->assertEquals('test.xsl', $template->getXslFile());
  }

  /**
   * @covers PapayaTemplateXslt
   */
  public function testEngineGetAfterSet() {
    $engine = $this->getMock('PapayaTemplateEngineXsl');
    $template = new PapayaTemplateXslt();
    $template->engine($engine);
    $this->assertSame($engine, $template->engine());
  }

  /**
   * @covers PapayaTemplateXslt
   */
  public function testEngineGetImplicitCreate() {
    $template = new PapayaTemplateXslt();
    $template->papaya($this->mockPapaya()->application());
    $this->assertInstanceOf('PapayaTemplateEngineXsl', $template->engine());
  }

  /**
   * @covers PapayaTemplateXslt
   */
  public function testParseExpectingFalse() {
    $engine = $this->getEngineFixture(FALSE);
    $template = new PapayaTemplateXslt('test.xsl');
    $template->papaya($this->mockPapaya()->application());
    $template->engine($engine);
    $this->assertFalse($template->parse());
  }

  /**
   * @covers PapayaTemplateXslt
   */
  public function testParseExpectingText() {
    $engine = $this->getEngineFixture('success');
    $template = new PapayaTemplateXslt('test.xsl');
    $template->papaya($this->mockPapaya()->application());
    $template->engine($engine);
    $this->assertEquals('success', $template->parse());
  }

  /**
   * @covers PapayaTemplateXslt
   */
  public function testParseExpectingXml() {
    $engine = $this->getEngineFixture(
      '<?xml version="1.0"?><test xmlns="urn:default" xmlns:empty="" xmlns:foo="urn:bar"/>'
    );
    $template = new PapayaTemplateXslt('test.xsl');
    $template->papaya($this->mockPapaya()->application());
    $template->engine($engine);
    $this->assertEquals(
      '<?xml version="1.0"?><test xmlns="urn:default" xmlns:foo="urn:bar"/>',
      $template->parse()
    );
  }

  /**
   * @covers PapayaTemplateXslt
   */
  public function testParseExpectingXmlRemoveXmlPi() {
    $engine = $this->getEngineFixture(
      '<?xml version="1.0"?><test xmlns:empty="" xmlns:foo="urn:bar"/>'
    );
    $template = new PapayaTemplateXslt('test.xsl');
    $template->papaya($this->mockPapaya()->application());
    $template->engine($engine);
    $this->assertEquals(
      '<test xmlns:empty="" xmlns:foo="urn:bar"/>',
      $template->parse(PapayaTemplateXslt::STRIP_XML_PI)
    );
  }

  /**
   * @covers PapayaTemplateXslt
   */
  public function testParseExpectingXmlRemoveXmlNamespaces() {
    $engine = $this->getEngineFixture(
      '<?xml version="1.0"?><test xmlns:empty="" xmlns:foo="urn:bar"/>'
    );
    $template = new PapayaTemplateXslt('test.xsl');
    $template->papaya($this->mockPapaya()->application());
    $template->engine($engine);
    $this->assertEquals(
      '<?xml version="1.0"?><test xmlns:foo="urn:bar"/>',
      $template->parse(PapayaTemplateXslt::STRIP_XML_EMPTY_NAMESPACE)
    );
  }

  /**
   * @covers PapayaTemplateXslt
   */
  public function testParseWithProfiling() {
    $messages = $this->getMock('PapayaMessageManager');
    $messages
      ->expects($this->exactly(2))
      ->method('log');
    $engine = $this->getEngineFixture(FALSE);
    $template = new PapayaTemplateXslt('test.xsl');
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
   * @covers PapayaTemplateXslt
   */
  public function testGetOutput() {
    $engine = $this->getEngineFixture('success');
    $template = new PapayaTemplateXslt('test.xsl');
    $template->papaya($this->mockPapaya()->application());
    $template->engine($engine);
    $this->assertEquals('success', $template->getOutput());
  }

  /**
   * @covers PapayaTemplateXslt
   */
  public function testGetOutputExpectingFalse() {
    $engine = $this->getEngineFixture(FALSE);
    $template = new PapayaTemplateXslt('test.xsl');
    $template->papaya($this->mockPapaya()->application());
    $template->engine($engine);
    $this->assertFalse($template->getOutput());
  }

  /**
   * @covers PapayaTemplateXslt
   */
  public function testGetOutputExpectingXmlOutput() {
    $response = $this->getMock('PapayaResponse');
    $response
      ->expects($this->once())
      ->method('setContentType')
      ->with('text/xml', 'utf-8');
    $response
      ->expects($this->once())
      ->method('content')
      ->with($this->isInstanceOf('PapayaResponseContentString'));
    $response
      ->expects($this->once())
      ->method('send')
      ->with(TRUE);

    $template = new PapayaTemplateXslt('test.xsl');
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
   * @covers PapayaTemplateXslt
   */
  public function testXhtml() {
    $engine = $this->getEngineFixture(FALSE);
    $template = new PapayaTemplateXslt('test.xsl');
    $template->papaya($this->mockPapaya()->application());
    $template->engine($engine);
    $this->assertFalse($template->xhtml());
  }

  /**********************
   * Fixtures
   *********************/

  public function getEngineFixture($result) {
    $parameters = $this->getMock('PapayaTemplateParameters');
    $parameters
      ->expects($this->once())
      ->method('assign');

    $engine = $this->getMock('PapayaTemplateEngineXsl');
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
      ->with($this->isInstanceOf('DOMDocument'));
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