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

namespace Papaya\Template\Engine {

  use Papaya\TestCase;
  use Papaya\XML\Document;
  use Papaya\XML\Errors as XMLErrors;
  use Papaya\XML\Exception as XMLException;

  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\Template\Engine\XSLT
   */
  class XSLTTest extends TestCase {

    private $_internalErrors;

    public function tearDown() {
      if (NULL !== $this->_internalErrors) {
        libxml_use_internal_errors($this->_internalErrors);
      }
    }

    public function testSetTemplateString() {
      $engine = new XSLT();
      $engine->setTemplateString($string = file_get_contents(__DIR__.'/TestData/valid.xsl'));
      $this->assertXmlStringEqualsXmlString(
        $string,
        $engine->getTemplateDocument()->saveXML()
      );
      $this->assertFalse($engine->useCache());
    }

    public function testSetTemplateFile() {
      $engine = new XSLT();
      $string = file_get_contents(__DIR__.'/TestData/valid.xsl');
      $engine->setTemplateFile(__DIR__.'/TestData/valid.xsl');
      $this->assertXmlStringEqualsXmlString(
        $string,
        $engine->getTemplateDocument()->saveXML()
      );
    }

    public function testSetTemplateDocument() {
      $document = new Document();
      $document->load(__DIR__.'/TestData/valid.xsl');
      $engine = new XSLT();
      $engine->setTemplateDocument($document);
      $this->assertSame(
        $document,
        $engine->getTemplateDocument()
      );
    }

    public function testSetTemplateFileWithInvalidFileNameExpectingException() {
      $engine = new XSLT();
      $this->expectException(\InvalidArgumentException::class);
      $engine->setTemplateFile('NON_EXISTING_FILENAME.XSL');
    }

    public function testUseCacheSetToTrue() {
      $engine = new XSLT();
      $this->assertTrue(
        $engine->useCache(TRUE)
      );
      $this->assertAttributeEquals(
        TRUE,
        '_useCache',
        $engine
      );
    }

    public function testUseCacheSetToFalse() {
      $engine = new XSLT();
      $this->assertFalse(
        $engine->useCache(FALSE)
      );
      $this->assertAttributeEquals(
        FALSE,
        '_useCache',
        $engine
      );
    }

    public function testUseCacheSetToTrueWithXsltProcessorObject() {
      $engine = new XSLT();
      $engine->setProcessor($this->getProcessorMock());
      $engine->useCache(TRUE);
      $this->assertAttributeNotInstanceOf(
        'XsltProcessor',
        '_processor',
        $engine
      );
    }

    public function testUseCacheSetToFalseWithXsltCacheObject() {
      $engine = new XSLT();
      $engine->setProcessor($this->getProcessorMock('XsltCache'));
      $engine->useCache(FALSE);
      $this->assertAttributeNotInstanceOf(
        'XsltCache',
        '_processor',
        $engine
      );
    }

    public function testSetProcessorWithXsltProcessor() {
      $processor = $this->getProcessorMock('XsltProcessor');
      $engine = new XSLT();
      $engine->setProcessor($processor);
      $this->assertAttributeSame(
        $processor,
        '_processor',
        $engine
      );
    }

    public function testSetProcessorWithXsltCache() {
      $processor = $this->getProcessorMock('XsltCache');
      $engine = new XSLT();
      $engine->setProcessor($processor);
      $this->assertAttributeSame(
        $processor,
        '_processor',
        $engine
      );
    }

    public function testSetProcessorWithInvalidProcessorExpectingException() {
      $engine = new XSLT();
      $this->expectException(\UnexpectedValueException::class);
      /** @noinspection PhpParamsInspection */
      $engine->setProcessor(new \stdClass);
    }

    public function testGetProcessor() {
      $processor = $this->getProcessorMock();
      $engine = new XSLT();
      $engine->setProcessor($processor);
      $this->assertSame(
        $processor,
        $engine->getProcessor()
      );
    }

    public function testGetProcessorWithImplicitCreateXsltProcessor() {
      $engine = new XSLT();
      $engine->useCache(FALSE);
      $this->assertInstanceOf(
        'XsltProcessor',
        $engine->getProcessor()
      );
    }

    public function testGetProcessorWithImplicitCreateXsltCache() {
      $engine = new XSLT();
      $engine->useCache(TRUE);
      $this->assertInstanceOf(
        'XsltCache',
        $engine->getProcessor()
      );
    }

    public function testSetErrorHandler() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|XMLErrors $errors */
      $errors = $this->createMock(XMLErrors::class);
      $engine = new XSLT();
      $engine->setErrorHandler($errors);
      $this->assertAttributeSame(
        $errors,
        '_errorHandler',
        $engine
      );
    }

    public function testGetErrorHandler() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|XMLErrors $errors */
      $errors = $this->createMock(XMLErrors::class);
      $engine = new XSLT();
      $engine->setErrorHandler($errors);
      $this->assertSame(
        $errors,
        $engine->getErrorHandler()
      );
    }

    public function testGetErrorHandlerWithImplicitCreate() {
      $engine = new XSLT();
      $this->assertInstanceOf(
        XMLErrors::class,
        $engine->getErrorHandler()
      );
    }

    /**
     * @throws XMLException
     */
    public function testPrepareWithXsltCache() {
      $templateFile = __DIR__.'/TestData/valid.xsl';
      $processor = $this->getProcessorMock('XsltCache');
      $processor
        ->expects($this->once())
        ->method('importStylesheet')
        ->with($this->equalTo($templateFile), $this->equalTo(TRUE))
        ->willReturn(TRUE);
      /** @var \PHPUnit_Framework_MockObject_MockObject|XMLErrors $errors */
      $errors = $this->createMock(XMLErrors::class);
      $errors
        ->expects($this->once())
        ->method('activate');
      $errors
        ->expects($this->once())
        ->method('deactivate');
      $engine = new XSLT();
      $engine->setProcessor($processor);
      $engine->setErrorHandler($errors);
      $engine->setTemplateFile($templateFile);
      $this->assertTrue(
        $engine->prepare()
      );
    }

    /**
     * @throws XMLException
     */
    public function testPrepareWithXsltProcessorOnFile() {
      $templateFile = __DIR__.'/TestData/valid.xsl';
      $processor = $this->getProcessorMock('XsltProcessor');
      $processor
        ->expects($this->once())
        ->method('importStylesheet')
        ->with($this->isInstanceOf(\DOMDocument::class))
        ->willReturn(TRUE);
      /** @var \PHPUnit_Framework_MockObject_MockObject|XMLErrors $errors */
      $errors = $this->createMock(XMLErrors::class);
      $errors
        ->expects($this->once())
        ->method('activate');
      $errors
        ->expects($this->once())
        ->method('deactivate');
      $engine = new XSLT();
      $engine->setProcessor($processor);
      $engine->setErrorHandler($errors);
      $engine->setTemplateFile($templateFile);
      $this->assertTrue(
        $engine->prepare()
      );
    }

    /**
     * @throws XMLException
     */
    public function testPrepareWithXsltProcessorOnString() {
      $templateString = file_get_contents(__DIR__.'/TestData/valid.xsl');
      $processor = $this->getProcessorMock('XsltProcessor');
      $processor
        ->expects($this->once())
        ->method('importStylesheet')
        ->with($this->isInstanceOf(\DOMDocument::class))
        ->willReturn(TRUE);
      /** @var \PHPUnit_Framework_MockObject_MockObject|XMLErrors $errors */
      $errors = $this->createMock(XMLErrors::class);
      $errors
        ->expects($this->once())
        ->method('activate');
      $errors
        ->expects($this->once())
        ->method('deactivate');
      $engine = new XSLT();
      $engine->setProcessor($processor);
      $engine->setErrorHandler($errors);
      $engine->setTemplateString($templateString);
      $this->assertTrue(
        $engine->prepare()
      );
    }

    /**
     * @throws XMLException
     */
    public function testPrepareWithXsltProcessorAndEmptyFileExpectingException() {
      $this->_internalErrors = libxml_use_internal_errors(TRUE);
      $templateFile = __DIR__.'/TestData/empty.txt';
      $processor = $this->getProcessorMock('XsltProcessor');
      /** @var \PHPUnit_Framework_MockObject_MockObject|XMLErrors $errors */
      $errors = $this->createMock(XMLErrors::class);
      $errors
        ->expects($this->once())
        ->method('activate');
      $errors
        ->expects($this->once())
        ->method('emit')
        ->willReturnCallback([$this, 'throwXmlException']);
      $engine = new XSLT();
      $engine->setProcessor($processor);
      $engine->setErrorHandler($errors);
      $engine->setTemplateFile($templateFile);

      $this->expectException(XMLException::class);
      $engine->prepare();
    }

    /**
     * @throws XMLException
     */
    public function testRunSuccessful() {
      $processor = $this->getProcessorMock('XsltProcessor');
      $processor
        ->expects($this->once())
        ->method('setParameter')
        ->with($this->equalTo(''), $this->equalTo('SAMPLE'), $this->equalTo(42))
        ->willReturn(TRUE);
      $processor
        ->expects($this->once())
        ->method('transformToXML')
        ->with($this->isInstanceOf(\DOMDocument::class))
        ->willReturn('success');
      /** @var \PHPUnit_Framework_MockObject_MockObject|XMLErrors $errors */
      $errors = $this->createMock(XMLErrors::class);
      $errors
        ->expects($this->once())
        ->method('activate');
      $errors
        ->expects($this->once())
        ->method('emit');
      $errors
        ->expects($this->once())
        ->method('deactivate');
      $engine = new XSLT();
      $engine->parameters(array('SAMPLE' => 42));
      $engine->setProcessor($processor);
      $engine->setErrorHandler($errors);
      $this->assertTrue(
        $engine->run()
      );
      $this->assertEquals(
        'success',
        $engine->getResult()
      );
    }

    /**
     * @throws XMLException
     */
    public function testRunExpectingException() {
      $processor = $this->getProcessorMock('XsltProcessor');
      $processor
        ->expects($this->once())
        ->method('transformToXML')
        ->with($this->isInstanceOf(\DOMDocument::class))
        ->willReturnCallback([$this, 'throwXmlException']);
      /** @var \PHPUnit_Framework_MockObject_MockObject|XMLErrors $errors */
      $errors = $this->createMock(XMLErrors::class);
      $errors
        ->expects($this->once())
        ->method('activate');
      $errors
        ->expects($this->once())
        ->method('emit');
      $errors
        ->expects($this->once())
        ->method('deactivate');
      $engine = new XSLT();
      $engine->setProcessor($processor);
      $engine->setErrorHandler($errors);
      $this->assertFalse(
        $engine->run()
      );
      $this->assertEquals(
        '',
        $engine->getResult()
      );
    }

    /**
     * @throws XMLException
     */
    public function throwXmlException() {
      $error = new \libXMLError();
      $error->level = LIBXML_ERR_WARNING;
      $error->code = 42;
      $error->message = 'Test';
      $error->file = '';
      $error->line = 23;
      $error->column = 21;
      throw new XMLException($error);
    }

    /**
     * @param string $class
     * @return \PHPUnit_Framework_MockObject_MockObject|\XSLTProcessor
     */
    private function getProcessorMock($class = \XSLTProcessor::class) {
      $result = $this
        ->getMockBuilder($class)
        ->setMethods(
          array(
            'importStylesheet', 'transformToXML', 'setParameter'
          )
        )
        ->getMock();
      return $result;
    }

    /**
     * @param $expectedValue
     * @param $value
     * @testWith
     *  ["bar", "bar"]
     *  ["single quote '", "single quote '"]
     *  ["double quote \"", "double quote \""]
     *  ["both quotes \"’", "both quotes \"'"]
     * @throws XMLException
     */
    public function testSetParameterInRun($expectedValue, $value) {
      $processor = $this->getProcessorMock('XsltProcessor');
      $processor
        ->expects($this->once())
        ->method('setParameter')
        ->with('', 'SAMPLE', $expectedValue)
        ->willReturn(TRUE);
      $processor
        ->method('transformToXML')
        ->with($this->isInstanceOf(\DOMDocument::class))
        ->willReturn('success');
      /** @var \PHPUnit_Framework_MockObject_MockObject|XMLErrors $errors */
      $errors = $this->createMock(XMLErrors::class);
      $errors
        ->method('activate');
      $errors
        ->method('emit');
      $errors
        ->method('deactivate');

      $engine = new XSLT();
      $engine->parameters(array('SAMPLE' => $value));
      $engine->setProcessor($processor);
      $engine->setErrorHandler($errors);
      $this->assertTrue($engine->run());
    }

    public function testParseXMLCallback() {
      $result = XSLT::parseXML('<foo/>');
      $this->assertXmlStringEqualsXmlString(
        '<foo/>',
        $result->saveXML()
      );
    }
  }
}

namespace {

  if (!class_exists('XsltCache', FALSE)) {
    class XsltCache {
      public function importStylesheet($fileName, $useCache = TRUE) {
      }

      public function transformToXML(\DOMNode $context = NULL) {
      }

      public function setParameter($namespace, $name, $value) {
      }

      public function registerPHPFunctions(array $functions = NULL) {
      }
    }
  }
}
