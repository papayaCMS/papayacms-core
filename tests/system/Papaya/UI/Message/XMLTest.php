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

namespace Papaya\UI\Message;
require_once __DIR__.'/../../../../bootstrap.php';

class XMLTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\UI\Message\Text::appendTo
   */
  public function testAppendTo() {
    $message = new XML(\Papaya\UI\Message::SEVERITY_ERROR, 'sample', 'content', TRUE);
    $this->assertEquals(
    /** @lang XML */
      '<error event="sample" occurred="yes" occured="yes">content</error>', $message->getXML()
    );
  }

  /**
   * @covers \Papaya\UI\Message\XML::appendTo
   */
  public function testAppendToWithXmlElements() {
    $message = new XML(
      \Papaya\UI\Message::SEVERITY_ERROR, 'sample', /** @lang XML */
      '<b>foo</b>', TRUE
    );
    $this->assertEquals(
    /** @lang XML */
      '<error event="sample" occurred="yes" occured="yes"><b>foo</b></error>', $message->getXML()
    );
  }
}
