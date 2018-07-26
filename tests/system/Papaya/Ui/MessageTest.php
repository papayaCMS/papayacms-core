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

class PapayaUiMessageTest extends \PapayaTestCase {

  /**
  * @covers \PapayaUiMessage::__construct
  */
  public function testConstructor() {
    $message = new \PapayaUiMessage_TestProxy(\PapayaUiMessage::SEVERITY_ERROR, 'sample');
    $this->assertEquals(
      \PapayaUiMessage::SEVERITY_ERROR, $message->severity
    );
    $this->assertEquals(
      'sample', $message->event
    );
  }

  /**
  * @covers \PapayaUiMessage::__construct
  */
  public function testConstructorWithOptionalParameters() {
    $message = new \PapayaUiMessage_TestProxy(\PapayaUiMessage::SEVERITY_ERROR, 'sample', TRUE);
    $this->assertTrue($message->occured);
  }

  /**
   * @covers \PapayaUiMessage::appendMessageElement
   * @covers \PapayaUiMessage::getTagName
   * @dataProvider provideTestMessages
   * @param string $expectedXml
   * @param int $severity
   * @param string $event
   * @param bool $occurred
   */
  public function testAppendTo($expectedXml, $severity, $event, $occurred = FALSE) {
    $message = new \PapayaUiMessage_TestProxy($severity, $event, $occurred);
    $this->assertEquals(
      $expectedXml,
      $message->getXml()
    );
  }

  /**
  * @covers \PapayaUiMessage::setSeverity
  */
  public function testSeverityGetAfterSet() {
    $message = new \PapayaUiMessage_TestProxy(\PapayaUiMessage::SEVERITY_ERROR, 'sample');
    $message->severity = \PapayaUiMessage::SEVERITY_WARNING;
    $this->assertEquals(
      \PapayaUiMessage::SEVERITY_WARNING, $message->severity
    );
  }

  /**
  * @covers \PapayaUiMessage::setSeverity
  */
  public function testSeverityWithInvalidValueExpectingException() {
    $message = new \PapayaUiMessage_TestProxy(\PapayaUiMessage::SEVERITY_ERROR, 'sample');
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid severity for message.');
    $message->severity = 99;
  }

  /**
  * @covers \PapayaUiMessage::setEvent
  */
  public function testEventGetAfterSet() {
    $message = new \PapayaUiMessage_TestProxy(\PapayaUiMessage::SEVERITY_ERROR, 'sample');
    $message->event = 'success';
    $this->assertEquals(
      'success', $message->event
    );
  }

  /**
  * @covers \PapayaUiMessage::setOccured
  */
  public function testOccurredGetAfterSet() {
    $message = new \PapayaUiMessage_TestProxy(\PapayaUiMessage::SEVERITY_ERROR, 'sample');
    $message->occured = TRUE;
    $this->assertTrue(
      $message->occured
    );
  }

  /********************************
  * Data provider
  ********************************/

  public static function provideTestMessages() {
    return array(
      'sample error, not occurred' => array(
        /** @lang XML */ '<error event="sample" occured="no"/>',
        \PapayaUiMessage::SEVERITY_ERROR,
        'sample',
        FALSE
      ),
      'test information, occurred' => array(
        /** @lang XML */ '<information event="test" occured="yes"/>',
        \PapayaUiMessage::SEVERITY_INFORMATION,
        'test',
        TRUE
      ),
    );
  }
}

/**
 * @property mixed severity
 */
class PapayaUiMessage_TestProxy extends \PapayaUiMessage {

  public function appendTo(\PapayaXmlElement $parent) {
    return parent::appendMessageElement($parent);
  }
}
