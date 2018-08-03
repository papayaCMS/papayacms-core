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
  * @covers \Papaya\UI\Message::__construct
  */
  public function testConstructor() {
    $message = new \PapayaUiMessage_TestProxy(\Papaya\UI\Message::SEVERITY_ERROR, 'sample');
    $this->assertEquals(
      \Papaya\UI\Message::SEVERITY_ERROR, $message->severity
    );
    $this->assertEquals(
      'sample', $message->event
    );
  }

  /**
  * @covers \Papaya\UI\Message::__construct
  */
  public function testConstructorWithOptionalParameters() {
    $message = new \PapayaUiMessage_TestProxy(\Papaya\UI\Message::SEVERITY_ERROR, 'sample', TRUE);
    $this->assertTrue($message->occured);
  }

  /**
   * @covers \Papaya\UI\Message::appendMessageElement
   * @covers \Papaya\UI\Message::getTagName
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
  * @covers \Papaya\UI\Message::setSeverity
  */
  public function testSeverityGetAfterSet() {
    $message = new \PapayaUiMessage_TestProxy(\Papaya\UI\Message::SEVERITY_ERROR, 'sample');
    $message->severity = \Papaya\UI\Message::SEVERITY_WARNING;
    $this->assertEquals(
      \Papaya\UI\Message::SEVERITY_WARNING, $message->severity
    );
  }

  /**
  * @covers \Papaya\UI\Message::setSeverity
  */
  public function testSeverityWithInvalidValueExpectingException() {
    $message = new \PapayaUiMessage_TestProxy(\Papaya\UI\Message::SEVERITY_ERROR, 'sample');
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('Invalid severity for message.');
    $message->severity = 99;
  }

  /**
  * @covers \Papaya\UI\Message::setEvent
  */
  public function testEventGetAfterSet() {
    $message = new \PapayaUiMessage_TestProxy(\Papaya\UI\Message::SEVERITY_ERROR, 'sample');
    $message->event = 'success';
    $this->assertEquals(
      'success', $message->event
    );
  }

  /**
  * @covers \Papaya\UI\Message::setOccured
  */
  public function testOccurredGetAfterSet() {
    $message = new \PapayaUiMessage_TestProxy(\Papaya\UI\Message::SEVERITY_ERROR, 'sample');
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
        \Papaya\UI\Message::SEVERITY_ERROR,
        'sample',
        FALSE
      ),
      'test information, occurred' => array(
        /** @lang XML */ '<information event="test" occured="yes"/>',
        \Papaya\UI\Message::SEVERITY_INFORMATION,
        'test',
        TRUE
      ),
    );
  }
}

/**
 * @property mixed severity
 */
class PapayaUiMessage_TestProxy extends \Papaya\UI\Message {

  public function appendTo(\Papaya\Xml\Element $parent) {
    return parent::appendMessageElement($parent);
  }
}
