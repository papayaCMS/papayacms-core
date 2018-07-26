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

class PapayaUtilServerNameTest extends \PapayaTestCase {

  private $_server;

  public function setUp() {
    $this->_server = $_SERVER;
  }

  public function tearDown() {
    $_SERVER = $this->_server;
  }

  /**
  * @covers \PapayaUtilServerName::get
  */
  public function testGetFromHttpHost() {
    $_SERVER['HTTP_HOST'] = 'www.test.tld';
    $this->assertEquals(
      'www.test.tld', \PapayaUtilServerName::get()
    );
  }

  /**
  * @covers \PapayaUtilServerName::get
  */
  public function testGetFromServerName() {
    $_SERVER['SERVER_NAME'] = 'www.test.tld';
    $this->assertEquals(
      'www.test.tld', \PapayaUtilServerName::get()
    );
  }

  /**
  * @covers \PapayaUtilServerName::get
  */
  public function testGetExpectingEmptyString() {
    $this->assertEquals(
      '', \PapayaUtilServerName::get()
    );
  }
}
