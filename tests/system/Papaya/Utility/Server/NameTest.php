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

namespace Papaya\Utility\Server;
require_once __DIR__.'/../../../../bootstrap.php';

class NameTest extends \Papaya\TestCase {

  private $_server;

  public function setUp(): void {
    $this->_server = $_SERVER;
  }

  public function tearDown(): void {
    $_SERVER = $this->_server;
  }

  /**
   * @covers \Papaya\Utility\Server\Name::get
   */
  public function testGetFromHttpHost() {
    $_SERVER['HTTP_HOST'] = 'www.test.tld';
    $this->assertEquals(
      'www.test.tld', Name::get()
    );
  }

  /**
   * @covers \Papaya\Utility\Server\Name::get
   */
  public function testGetFromServerName() {
    $_SERVER['SERVER_NAME'] = 'www.test.tld';
    $this->assertEquals(
      'www.test.tld', Name::get()
    );
  }

  /**
   * @covers \Papaya\Utility\Server\Name::get
   */
  public function testGetExpectingEmptyString() {
    $this->assertEquals(
      '', Name::get()
    );
  }
}
