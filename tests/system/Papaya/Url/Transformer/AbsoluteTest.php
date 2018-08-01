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

class PapayaUrlTransformerAbsoluteTest extends \PapayaTestCase {

  /**
  * get mock for \Papaya\PapayaUrl from url string
  *
  * @param string $url
  * @return PHPUnit_Framework_MockObject_MockObject|Url
  */
  public function getPapayaUrlMockFixture($url) {
    $mapping = array(
      'getScheme' => 'scheme',
      'getUser' => 'user',
      'getPassword' => 'pass',
      'getHost' => 'host',
      'getPort' => 'port',
      'getPath' => 'path',
      'getQuery' => 'query',
      'getFragment' => 'fragment',
    );
    $urlObject = $this
      ->getMockBuilder(Url::class)
      ->setMethods(array_merge(array('getHostUrl'), array_keys($mapping)))
      ->getMock();
    if (empty($url)) {
      $urlData = array();
    } else {
      $urlData = parse_url($url);
    }
    foreach ($mapping as $methodName => $arrayKey) {
      $urlObject
        ->expects($this->any())
        ->method($methodName)
        ->will(
          $this->returnValue(
            empty($urlData[$arrayKey]) ? NULL : $urlData[$arrayKey]
          )
        );
    }
    $urlObject
      ->expects($this->any())
      ->method('getHostUrl')
      ->will($this->returnValue('http://www.example.com'));
    return $urlObject;
  }

  /**
   * @covers \Papaya\Url\Transformer\Absolute::transform
   * @covers \Papaya\Url\Transformer\Absolute::_calculateRealPath
   * @dataProvider transformDataProvider
   * @param string $currentUrl
   * @param string $targetPath
   * @param string $expected
   */
  public function testTransform($currentUrl, $targetPath, $expected) {
    $transformer = new Url\Transformer\Absolute();
    $this->assertSame(
      $expected,
      $transformer->transform(
        $this->getPapayaUrlMockFixture($currentUrl),
        $targetPath
      )
    );
  }

  /*************************************
  * Data Providers
  *************************************/

  public static function transformDataProvider() {
    return array(
      'Valid: Full path files' => array(
        'http://www.example.com/a/loc/ation.html',
        '/some/other/location.html',
        'http://www.example.com/some/other/location.html'
      ),
      'Valid: Full path folders' => array(
        'http://www.example.com/a/loc/ation/',
        '/some/other/',
        'http://www.example.com/some/other/'
      ),
      'Valid: Full path file to folder' => array(
        'http://www.example.com/a/loc/ation.html',
        '/some/other/',
        'http://www.example.com/some/other/'
      ),
      'Valid: Full path folder to file' => array(
        'http://www.example.com/a/loc/ation/',
        '/some/other/location.html',
        'http://www.example.com/some/other/location.html'
      ),
      'Valid: Relative path from folder' => array(
        'http://www.example.com/a/loc/ation/',
        '../../some/other/location.html',
        'http://www.example.com/a/some/other/location.html'
      ),
      'Valid: Relative path from file' => array(
        'http://www.example.com/a/loc/ation/test.html',
        '../../some/./../other/location.html',
        'http://www.example.com/a/other/location.html'
      ),
      'Valid: .. overflow' => array(
        'http://www.example.com/a/location/',
        '../../../../test.html',
        'http://www.example.com/test.html'
      ),
      'Valid: some //es' => array(
        'http://www.example.com/a/location/',
        '../../my/path//is///here//here//../test.html',
        'http://www.example.com/my/path/is/here/test.html'
      ),
      'Valid: another .. test' => array(
        'http://www.example.com/',
        '/this/is//a/../an/example/path/just/to/.././to/../test/some/stuff',
        'http://www.example.com/this/is/an/example/path/just/test/some/stuff'
      ),
      'Valid: full url' => array(
        'http://www.example.com/',
        'http://www.test.tld/',
        'http://www.test.tld/'
      ),
      'Valid: once up to host' => array(
        'http://www.example.com/path/file.html',
        '../',
        'http://www.example.com/'
      ),
      'Valid: several up to host' => array(
        'http://www.example.com/path/subpath/file.html',
        '/',
        'http://www.example.com/'
      ),
    );
  }
}
