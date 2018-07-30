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

class PapayaXmlErrorsTest extends \PapayaTestCase {

  public function setUp() {
    if (!extension_loaded('dom')) {
      $this->markTestSkipped('No dom xml extension found.');
    }
    libxml_use_internal_errors(TRUE);
    libxml_clear_errors();
  }

  public function tearDown() {
    libxml_use_internal_errors(FALSE);
  }

  /**
  * @covers \PapayaXmlErrors::activate
  */
  public function testActivate() {
    libxml_use_internal_errors(FALSE);
    $errors = new \PapayaXmlErrors();
    $errors->activate();
    $this->assertTrue(
      libxml_use_internal_errors()
    );
  }

  /**
  * @covers \PapayaXmlErrors::deactivate
  */
  public function testDeactivate() {
    libxml_use_internal_errors(FALSE);
    $errors = new \PapayaXmlErrors();
    $errors->activate();
    $errors->deactivate();
    $this->assertFalse(
      libxml_use_internal_errors()
    );
  }

  /**
  * @covers \PapayaXmlErrors::emit
  */
  public function testEmit() {
    $messages = $this->createMock(\PapayaMessageManager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(\PapayaMessageLogable::class));
    $errors = new \PapayaXmlErrors();
    $errors->papaya(
      $this->mockPapaya()->application(
        array(
          'Messages' => $messages
        )
      )
    );
    $document = new DOMDocument('1.0', 'UTF-8');
    $document->loadHTML(/** @lang XML */'<foo/>');
    $errors->emit();
  }

  /**
  * @covers \PapayaXmlErrors::omit
  */
  public function testOmit() {
    $messages = $this->createMock(\PapayaMessageManager::class);
    $messages
      ->expects($this->once())
      ->method('dispatch')
      ->with($this->isInstanceOf(\PapayaMessageLogable::class));
    $errors = new \PapayaXmlErrors();
    $errors->papaya(
      $this->mockPapaya()->application(
        array(
          'Messages' => $messages
        )
      )
    );
    $document = new DOMDocument('1.0', 'UTF-8');
    $document->loadHTML(/** @lang XML */'<foo/>');
    /** @noinspection PhpDeprecationInspection */
    $errors->omit();
  }

  /**
  * @covers \PapayaXmlErrors::emit
  */
  public function testEmitIgnoringNonFatal() {
    $messages = $this->createMock(\PapayaMessageManager::class);
    $messages
      ->expects($this->never())
      ->method('dispatch');
    $errors = new \PapayaXmlErrors();
    $errors->papaya(
      $this->mockPapaya()->application(
        array(
          'Messages' => $messages
        )
      )
    );
    $document = new DOMDocument('1.0', 'UTF-8');
    $document->loadHTML(/** @lang XML */'<foo/>');
    $errors->emit(TRUE);
  }

  /**
  * @covers \PapayaXmlErrors::emit
  */
  public function testEmitWithFatalError() {
    $errors = new \PapayaXmlErrors();
    $document = new DOMDocument('1.0', 'UTF-8');
    $document->loadXML(/** @lang Text */'<foo>');
    $this->expectException(\PapayaXmlException::class);
    $errors->emit();
  }

  /**
  * @covers \PapayaXmlErrors::getMessageFromError
  */
  public function testGetMessageFromError() {
    $error = new libXMLError();
    $error->level = LIBXML_ERR_WARNING;
    $error->code = 42;
    $error->message = 'Test';
    $error->file = '';
    $error->line = 23;
    $error->column = 21;
    $errors = new \PapayaXmlErrors();
    $message = $errors->getMessageFromError($error);
    $this->assertEquals(
      \PapayaMessageLogable::GROUP_SYSTEM, $message->getGroup()
    );
    $this->assertEquals(
      \Papaya\Message::SEVERITY_WARNING, $message->getType()
    );
    $this->assertEquals(
      '42: Test in line 23 at char 21', $message->getMessage()
    );
  }

  /**
  * @covers \PapayaXmlErrors::getMessageFromError
  */
  public function testGetMessageFromErrorWithFile() {
    $error = new libXMLError();
    $error->level = LIBXML_ERR_WARNING;
    $error->code = 42;
    $error->message = 'Test';
    $error->file = __FILE__;
    $error->line = 23;
    $error->column = 21;
    $errors = new \PapayaXmlErrors();
    $context = $errors->getMessageFromError($error)->context();
    $this->assertInstanceOf(
      \Papaya\Message\Context\File::class, $context->current()
    );
  }

  /**
  * @covers \PapayaXmlErrors::encapsulate
  */
  public function testEncapsulateWithoutError() {
    $errors = new \PapayaXmlErrors();
    $this->assertTrue($errors->encapsulate(array($this, 'callbackReturnTrue')));
  }

  public function callbackReturnTrue() {
    return TRUE;
  }

  /**
  * @covers \PapayaXmlErrors::encapsulate
  */
  public function testEncapsulateWithError() {
    $messages = $this->createMock(\PapayaMessageManager::class);
    $messages
      ->expects($this->once())
      ->method('log');
    $errors = new \PapayaXmlErrors();
    $errors->papaya(
      $this->mockPapaya()->application(
        array('messages' => $messages)
      )
    );
    $this->assertNull(
      $errors->encapsulate(array($this, 'callbackThrowXmlError'))
    );
  }

  /**
  * @covers \PapayaXmlErrors::encapsulate
  */
  public function testEncapsulateWithErrorNotEmitted() {
    $messages = $this->createMock(\PapayaMessageManager::class);
    $messages
      ->expects($this->never())
      ->method('log');
    $errors = new \PapayaXmlErrors();
    $errors->papaya(
      $this->mockPapaya()->application(
        array('messages' => $messages)
      )
    );
    $this->assertNull(
      $errors->encapsulate(array($this, 'callbackThrowXmlError'), array(), FALSE)
    );
  }
  /**
  * @covers \PapayaXmlErrors::encapsulate
  */
  public function testEncapsulateWithNonFatalNotEmitted() {
    $messages = $this->createMock(\PapayaMessageManager::class);
    $messages
      ->expects($this->never())
      ->method('dispatch');
    $errors = new \PapayaXmlErrors();
    $errors->papaya(
      $this->mockPapaya()->application(
        array(
          'Messages' => $messages
        )
      )
    );
    $document = new DOMDocument('1.0', 'UTF-8');
    $this->assertTrue(
      $errors->encapsulate(
        function($html) use ($document) {
          return $document->loadHTML($html);
        },
        array(/** @lang XML */'<foo/>'),
        FALSE
      )
    );
  }

  public function callbackThrowXmlError() {
    $error = new libXMLError();
    $error->level = LIBXML_ERR_ERROR;
    $error->code = 42;
    $error->message = 'Test';
    $error->file = __FILE__;
    $error->line = 23;
    $error->column = 21;
    throw new \PapayaXmlException($error);
  }
}
