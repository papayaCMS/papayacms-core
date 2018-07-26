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

class PapayaUrlTransformerCleanupTest extends \PapayaTestCase {

  /**
   * @covers \PapayaUrlTransformerCleanup::transform
   * @covers \PapayaUrlTransformerCleanup::_calculateRealPath
   * @dataProvider transformDataProvider
   * @param string $expected
   * @param string $targetUrl
   */
  public function testTransform($expected, $targetUrl) {
    $transformer = new \PapayaUrlTransformerCleanup();
    $this->assertSame(
      $expected,
      $transformer->transform(
        $targetUrl
      )
    );
  }

  /*************************************
  * Data Providers
  *************************************/

  public static function transformDataProvider() {
    return array(
      array(
        '/',
        '/'
      ),
      array(
        '/some/location.html',
        '/some/location.html'
      ),
      array(
        '/some/location.html?foo',
        '/some/location.html?foo'
      ),
      array(
        '/some/location.html#bar',
        '/some/location.html#bar'
      ),
      array(
        '/some/location/',
        '/some/location/'
      ),
      array(
        'http://www.example.com/some/location.html',
        'http://www.example.com/some/location.html'
      ),
      array(
        'http://www.example.com:80/some/location.html',
        'http://www.example.com:80/some/location.html'
      ),
      array(
        'http://user@www.example.com:80/some/location.html',
        'http://user@www.example.com:80/some/location.html'
      ),
      array(
        'http://user:pass@www.example.com/some/location.html',
        'http://user:pass@www.example.com/some/location.html'
      ),
      array(
        '/some/location.html',
        '/some//////////////location.html'
      ),
      array(
        '/location.html',
        '/some/../location.html'
      ),
      array(
        '/some/location.html',
        '/some/path/../path/../location.html'
      ),
      array(
        '/some/location.html',
        '/some/path/path/..//../location.html'
      ),
      array(
        'http://www.example.com/some/location.html',
        'http://www.example.com/some//path/path/..//../location.html'
      ),
    );
  }
}
