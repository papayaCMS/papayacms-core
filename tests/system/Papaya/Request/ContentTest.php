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

namespace Papaya\Request;
require_once __DIR__.'/../../../bootstrap.php';

class ContentTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Request\Content
   */
  public function testReadStream() {
    $stream = fopen('data://text/plain,'.urlencode('TEST'), 'rb');
    $content = new Content($stream);
    $this->assertEquals('TEST', (string)$content);
  }

  /**
   * @covers \Papaya\Request\Content
   */
  public function testReadLengthStream() {
    $content = new Content(NULL, 42);
    $this->assertEquals(42, $content->length());
  }

  /**
   * @covers \Papaya\Request\Content
   * @backupGlobals enabled
   */
  public function testReadLengthFromEnvironment() {
    $_SERVER['HTTP_CONTENT_LENGTH'] = 42;
    $content = new Content();
    $this->assertEquals(42, $content->length());
  }

}
