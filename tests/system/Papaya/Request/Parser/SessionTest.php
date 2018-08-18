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

namespace Papaya\Request\Parser;

require_once __DIR__.'/../../../../bootstrap.php';

class SessionTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Request\Parser\Session::parse
   * @dataProvider parseDataProvider
   * @param string $path
   * @param array|FALSE $expected
   */
  public function testParse($path, $expected) {
    /** @var \PHPUnit_Framework_MockObject_MockObject|\Papaya\URL $url */
    $url = $this
      ->getMockBuilder(\Papaya\URL::class)
      ->setMethods(array('getPath'))
      ->getMock();
    $url
      ->expects($this->once())
      ->method('getPath')
      ->will($this->returnValue($path));
    $parser = new Session();
    $this->assertSame(
      $expected,
      $parser->parse($url)
    );
  }

  /**
   * @covers \Papaya\Request\Parser\Session::isLast
   */
  public function testIsLast() {
    $parser = new Session();
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

