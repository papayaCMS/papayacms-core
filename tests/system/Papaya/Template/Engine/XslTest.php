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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaTemplateEngineXslTest extends \PapayaTestCase {

  private $_internalErrors;

  public function tearDown() {
    if (NULL !== $this->_internalErrors) {
      libxml_use_internal_errors($this->_internalErrors);
    }
  }

  /**
  * @covers \Papaya\Template\Engine\Xsl::setTemplateString
  */
  public function testSetTemplateString() {
    $engine = new \Papaya\Template\Engine\Xsl();
    $engine->setTemplateString($string = file_get_contents(__DIR__.'/TestData/valid.xsl'));
    $this->assertAttributeEquals(
      $string, '_template', $engine
    );
    $this->assertAttributeEquals(
      FALSE, '_templateFile', $engine
    );
    $this->assertFalse($engine->useCache());
  }

  /**
  * @covers \Papaya\Template\Engine\Xsl::setTemplateFile
  */
  public function testSetTemplateFile() {
    $engine = new \Papaya\Template\Engine\Xsl();
    $engine->setTemplateFile(__DIR__.'/TestData/valid.xsl');
    $this->assertAttributeEquals(
      __DIR__.'/TestData/valid.xsl',
      '_templateFile',
      $engine
    );
  }

  /**
  * @covers \Papaya\Template\Engine\Xsl::setTemplateFile
  */
  public function testSetTemplateFileWithInvalidFileNameExpectingException() {
    $engine = new \Papaya\Template\Engine\Xsl();
    $this->expectException(InvalidArgumentException::class);
    $engine->setTemplateFile('NONEXISTING_FILENAME.XSL');
  }

  /**
  * @covers \Papaya\Template\Engine\Xsl::useCache
  */
  public function testUseCacheSetToTrue() {
    $engine = new \Papaya\Template\Engine\Xsl();
    $this->assertTrue(
      $engine->useCache(TRUE)
    );
    $this->assertAttributeEquals(
      TRUE,
      '_useCache',
      $engine
    );
  }

  /**
  * @covers \Papaya\Template\Engine\Xsl::useCache
  */
  public function testUseCacheSetToFalse() {
    $engine = new \Papaya\Template\Engine\Xsl();
    $this->assertFalse(
      $engine->useCache(FALSE)
    );
    $this->assertAttributeEquals(
      FALSE,
      '_useCache',
      $engine
    );
  }

  /**
  * @covers \Papaya\Template\Engine\Xsl::useCache
  */
  public function testUseCacheSetToTrueWithXsltProcessorObject() {
    $engine = new \Papaya\Template\Engine\Xsl();
    $engine->setProcessor($this->getProcessorMock());
    $engine->useCache(TRUE);
    $this->assertAttributeNotInstanceOf(
      'XsltProcessor',
      '_processor',
      $engine
    );
  }

  /**
  * @covers \Papaya\Template\Engine\Xsl::useCache
  */
  public function testUseCacheSetToFalseWithXsltCacheObject() {
    $engine = new \Papaya\Template\Engine\Xsl();
    $engine->setProcessor($this->getProcessorMock('XsltCache'));
    $engine->useCache(FALSE);
    $this->assertAttributeNotInstanceOf(
      'XsltCache',
      '_processor',
      $engine
    );
  }

  /**
  * @covers \Papaya\Template\Engine\Xsl::setProcessor
  */
  public function testSetProcessorWithXsltProcessor() {
    $processor = $this->getProcessorMock('XsltProcessor');
    $engine = new \Papaya\Template\Engine\Xsl();
    $engine->setProcessor($processor);
    $this->assertAttributeSame(
      $processor,
      '_processor',
      $engine
    );
  }

  /**
  * @covers \Papaya\Template\Engine\Xsl::setProcessor
  */
  public function testSetProcessorWithXsltCache() {
    $processor = $this->getProcessorMock('XsltCache');
    $engine = new \Papaya\Template\Engine\Xsl();
    $engine->setProcessor($processor);
    $this->assertAttributeSame(
      $processor,
      '_processor',
      $engine
    );
  }

  /**
  * @covers \Papaya\Template\Engine\Xsl::setProcessor
  */
  public function testSetProcessorWithInvalidProcessorExpectingException() {
    $engine = new \Papaya\Template\Engine\Xsl();
    $this->expectException(UnexpectedValueException::class);
    /** @noinspection PhpParamsInspection */
    $engine->setProcessor(new stdClass);
  }

  /**
  * @covers \Papaya\Template\Engine\Xsl::getProcessor
  */
  public function testGetProcessor() {
    $processor = $this->getProcessorMock();
    $engine = new \Papaya\Template\Engine\Xsl();
    $engine->setProcessor($processor);
    $this->assertSame(
      $processor,
      $engine->getProcessor()
    );
  }

  /**
  * @covers \Papaya\Template\Engine\Xsl::getProcessor
  */
  public function testGetProcessorWithImplizitCreateXsltProccessor() {
    $engine = new \Papaya\Template\Engine\Xsl();
    $engine->useCache(FALSE);
    $this->assertInstanceOf(
      'XsltProcessor',
      $engine->getProcessor()
    );
  }

  /**
  * @covers \Papaya\Template\Engine\Xsl::getProcessor
  */
  public function testGetProcessorWithImplizitCreateXsltCache() {
    $engine = new \Papaya\Template\Engine\Xsl();
    $engine->useCache(TRUE);
    $this->assertInstanceOf(
      'XsltCache',
      $engine->getProcessor()
    );
  }

  /**
  * @covers \Papaya\Template\Engine\Xsl::setErrorHandler
  */
  public function testSetErrorHandler() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\XML\Errors $errors */
    $errors = $this->createMock(\Papaya\XML\Errors::class);
    $engine = new \Papaya\Template\Engine\Xsl();
    $engine->setErrorHandler($errors);
    $this->assertAttributeSame(
      $errors,
      '_errorHandler',
      $engine
    );
  }

  /**
  * @covers \Papaya\Template\Engine\Xsl::getErrorHandler
  */
  public function testGetErrorHandler() {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\XML\Errors $errors */
    $errors = $this->createMock(\Papaya\XML\Errors::class);
    $engine = new \Papaya\Template\Engine\Xsl();
    $engine->setErrorHandler($errors);
    $this->assertSame(
      $errors,
      $engine->getErrorHandler()
    );
  }

  /**
  * @covers \Papaya\Template\Engine\Xsl::getErrorHandler
  */
  public function testGetErrorHandlerWithImplicitCreate() {
    $engine = new \Papaya\Template\Engine\Xsl();
    $this->assertInstanceOf(
      \Papaya\XML\Errors::class,
      $engine->getErrorHandler()
    );
  }

  /**
  * @covers \Papaya\Template\Engine\Xsl::prepare
  */
  public function testPrepareWithXsltCache() {
    $templateFile = __DIR__.'/TestData/valid.xsl';
    $processor = $this->getProcessorMock('XsltCache');
    $processor
      ->expects($this->once())
      ->method('importStylesheet')
      ->with($this->equalTo($templateFile), $this->equalTo(TRUE))
      ->will($this->returnValue(TRUE));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\XML\Errors $errors */
    $errors = $this->createMock(\Papaya\XML\Errors::class);
    $errors
      ->expects($this->once())
      ->method('activate');
    $errors
      ->expects($this->once())
      ->method('deactivate');
    $engine = new \Papaya\Template\Engine\Xsl();
    $engine->setProcessor($processor);
    $engine->setErrorHandler($errors);
    $engine->setTemplateFile($templateFile);
    $this->assertTrue(
      $engine->prepare()
    );
  }

  /**
  * @covers \Papaya\Template\Engine\Xsl::prepare
  */
  public function testPrepareWithXsltProcessorOnFile() {
    $templateFile = __DIR__.'/TestData/valid.xsl';
    $processor = $this->getProcessorMock('XsltProcessor');
    $processor
      ->expects($this->once())
      ->method('importStylesheet')
      ->with($this->isInstanceOf(DOMDocument::class))
      ->will($this->returnValue(TRUE));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\XML\Errors $errors */
    $errors = $this->createMock(\Papaya\XML\Errors::class);
    $errors
      ->expects($this->once())
      ->method('activate');
    $errors
      ->expects($this->once())
      ->method('deactivate');
    $engine = new \Papaya\Template\Engine\Xsl();
    $engine->setProcessor($processor);
    $engine->setErrorHandler($errors);
    $engine->setTemplateFile($templateFile);
    $this->assertTrue(
      $engine->prepare()
    );
  }

  /**
  * @covers \Papaya\Template\Engine\Xsl::prepare
  */
  public function testPrepareWithXsltProcessorOnString() {
    $templateString = file_get_contents(__DIR__.'/TestData/valid.xsl');
    $processor = $this->getProcessorMock('XsltProcessor');
    $processor
      ->expects($this->once())
      ->method('importStylesheet')
      ->with($this->isInstanceOf(DOMDocument::class))
      ->will($this->returnValue(TRUE));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\XML\Errors $errors */
    $errors = $this->createMock(\Papaya\XML\Errors::class);
    $errors
      ->expects($this->once())
      ->method('activate');
    $errors
      ->expects($this->once())
      ->method('deactivate');
    $engine = new \Papaya\Template\Engine\Xsl();
    $engine->setProcessor($processor);
    $engine->setErrorHandler($errors);
    $engine->setTemplateString($templateString);
    $this->assertTrue(
      $engine->prepare()
    );
  }

  /**
  * @covers \Papaya\Template\Engine\Xsl::prepare
  */
  public function testPrepareWithXsltProcessorAndEmptyFileExpectingException() {
    $this->_internalErrors = libxml_use_internal_errors(TRUE);
    $templateFile = __DIR__.'/TestData/empty.txt';
    $processor = $this->getProcessorMock('XsltProcessor');
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\XML\Errors $errors */
    $errors = $this->createMock(\Papaya\XML\Errors::class);
    $errors
      ->expects($this->once())
      ->method('activate');
    $errors
      ->expects($this->once())
      ->method('emit')
      ->will($this->returnCallback(array($this, 'throwXmlException')));
    $engine = new \Papaya\Template\Engine\Xsl();
    $engine->setProcessor($processor);
    $engine->setErrorHandler($errors);
    $engine->setTemplateFile($templateFile);

    $this->expectException(\Papaya\XML\Exception::class);
    $engine->prepare();
  }

  /**
  * @covers \Papaya\Template\Engine\Xsl::run
  * @covers \Papaya\Template\Engine\Xsl::getResult
  */
  public function testRunSuccessful() {
    $processor = $this->getProcessorMock('XsltProcessor');
    $processor
      ->expects($this->once())
      ->method('setParameter')
      ->with($this->equalTo(''), $this->equalTo('SAMPLE'), $this->equalTo(42))
      ->will($this->returnValue(TRUE));
    $processor
      ->expects($this->once())
      ->method('transformToXML')
      ->with($this->isInstanceOf(DOMDocument::class))
      ->will($this->returnValue('success'));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\XML\Errors $errors */
    $errors = $this->createMock(\Papaya\XML\Errors::class);
    $errors
      ->expects($this->once())
      ->method('activate');
    $errors
      ->expects($this->once())
      ->method('emit');
    $errors
      ->expects($this->once())
      ->method('deactivate');
    $engine = new \Papaya\Template\Engine\Xsl();
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
  * @covers \Papaya\Template\Engine\Xsl::run
  * @covers \Papaya\Template\Engine\Xsl::getResult
  */
  public function testRunExpectingException() {
    $processor = $this->getProcessorMock('XsltProcessor');
    $processor
      ->expects($this->once())
      ->method('transformToXML')
      ->with($this->isInstanceOf(DOMDocument::class))
      ->will($this->returnCallback(array($this, 'throwXmlException')));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\XML\Errors $errors */
    $errors = $this->createMock(\Papaya\XML\Errors::class);
    $errors
      ->expects($this->once())
      ->method('activate');
    $errors
      ->expects($this->once())
      ->method('emit');
    $errors
      ->expects($this->once())
      ->method('deactivate');
    $engine = new \Papaya\Template\Engine\Xsl();
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

  public function throwXmlException() {
    $error = new libXMLError();
    $error->level = LIBXML_ERR_WARNING;
    $error->code = 42;
    $error->message = 'Test';
    $error->file = '';
    $error->line = 23;
    $error->column = 21;
    throw new \Papaya\XML\Exception($error);
  }

  /**
   * @param string $class
   * @return PHPUnit_Framework_MockObject_MockObject|XsltProcessor|XsltCache
   */
  private function getProcessorMock($class = 'XsltProcessor') {
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
   */
  public function testSetParameterInRun($expectedValue, $value) {
    $processor = $this->getProcessorMock('XsltProcessor');
    $processor
      ->expects($this->once())
      ->method('setParameter')
      ->with('', 'SAMPLE', $expectedValue)
      ->will($this->returnValue(TRUE));
    $processor
      ->expects($this->any())
      ->method('transformToXML')
      ->with($this->isInstanceOf(DOMDocument::class))
      ->will($this->returnValue('success'));
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\XML\Errors $errors */
    $errors = $this->createMock(\Papaya\XML\Errors::class);
    $errors
      ->expects($this->any())
      ->method('activate');
    $errors
      ->expects($this->any())
      ->method('emit');
    $errors
      ->expects($this->any())
      ->method('deactivate');

    $engine = new \Papaya\Template\Engine\Xsl();
    $engine->parameters(array('SAMPLE' => $value));
    $engine->setProcessor($processor);
    $engine->setErrorHandler($errors);
    $this->assertTrue($engine->run());
  }
}

if (!class_exists('XsltCache', FALSE)) {
  class XsltCache {
    public function importStylesheet($fileName, $useCache = TRUE) {
    }
    public function transformToXML(DOMNode $context = NULL) {
    }
    public function setParameter($namespace, $name, $value) {
    }
    public function registerPHPFunctions(array $functions = NULL) {
    }
  }
}
