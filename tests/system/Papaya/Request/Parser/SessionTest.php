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

use Papaya\Url;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaRequestParserSessionTest extends PapayaTestCase {

  /**
   * @covers PapayaRequestParserSession::parse
   * @dataProvider parseDataProvider
   * @param string $path
   * @param array|FALSE $expected
   */
  public function testParse($path, $expected) {
    /** @var PHPUnit_Framework_MockObject_MockObject|Url $url */
    $url = $this
      ->getMockBuilder(Url::class)
      ->setMethods(array('getPath'))
      ->getMock();
    $url
      ->expects($this->once())
      ->method('getPath')
      ->will($this->returnValue($path));
    $parser = new PapayaRequestParserSession();
    $this->assertSame(
      $expected,
      $parser->parse($url)
    );
  }

  /**
  * @covers PapayaRequestParserSession::isLast
  */
  public function testIsLast() {
    $parser = new PapayaRequestParserSession();
    $this->assertFalse($parser->isLast());
  }

  /*************************************
  * Data Provider
  *************************************/

  public static function parseDataProvider() {
    return array(
      array(
        '/index.html',
        FALSE
      ),
      array(
        '/sid01234567890123456789012345678901/index.html',
        array(
          'session' => 'sid01234567890123456789012345678901'
        )
      ),
      array(
        '/sid01234567890123456789012345678901/',
        array(
          'session' => 'sid01234567890123456789012345678901'
        )
      ),
      array(
        '/sidadmin01234567890123456789012345678901/',
        array(
          'session' => 'sidadmin01234567890123456789012345678901'
        )
      ),
    );
  }
}

