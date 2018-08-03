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

class PapayaUiMessageTextTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\UI\Message\Text::__construct
  */
  public function testConstructor() {
    $message = new \Papaya\UI\Message\Text(\Papaya\UI\Message::SEVERITY_ERROR, 'sample', 'content');
    $this->assertEquals(
      'content', $message->content
    );
  }

  /**
  * @covers \Papaya\UI\Message\Text::appendTo
  */
  public function testAppendTo() {
    $message = new \Papaya\UI\Message\Text(\Papaya\UI\Message::SEVERITY_ERROR, 'sample', 'content', TRUE);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */'<error event="sample" occured="yes">content</error>', $message->getXML()
    );
  }

  /**
  * @covers \Papaya\UI\Message\Text::appendTo
  */
  public function testAppendToWithSpecialChars() {
    $message = new \Papaya\UI\Message\Text(\Papaya\UI\Message::SEVERITY_ERROR, 'sample', '<b>foo', TRUE);
    $this->assertXmlStringEqualsXmlString(
      /** @lang XML */'<error event="sample" occured="yes">&lt;b&gt;foo</error>', $message->getXML()
    );
  }

  /**
  * @covers \Papaya\UI\Message\Text::getContent
  * @covers \Papaya\UI\Message\Text::setContent
  */
  public function testGetXmlAfterSetXml() {
    $message = new \Papaya\UI\Message\Text(\Papaya\UI\Message::SEVERITY_ERROR, 'sample', '');
    $message->content = 'content';
    $this->assertEquals(
      'content', $message->content
    );
  }

}
