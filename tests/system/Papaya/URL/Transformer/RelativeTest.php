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

namespace Papaya\URL\Transformer;
require_once __DIR__.'/../../../../bootstrap.php';

class RelativeTest extends \Papaya\TestCase {

  /**
   * get mock for \Papaya\URL from url string
   *
   * @param string $url
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\URL
   */
  private function getURLMockFixture($url) {
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
      ->getMockBuilder(\Papaya\URL::class)
      ->setMethods(array_keys($mapping))
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
    return $urlObject;
  }

  /**
   * @covers \Papaya\URL\Transformer\Relative::transform
   * @covers \Papaya\URL\Transformer\Relative::_comparePorts
   * @dataProvider transformDataProvider
   * @param string $currentUrl
   * @param string $targetUrl
   * @param string $expected
   */
  public function testTransform($currentUrl, $targetUrl, $expected) {
    $transformer = new Relative();
    $this->assertSame(
      $expected,
      $transformer->transform(
        $this->getURLMockFixture($currentUrl),
        $this->getURLMockFixture($targetUrl)
      )
    );
  }

  /**
   * @covers \Papaya\URL\Transformer\Relative::transform
   * @covers \Papaya\URL\Transformer\Relative::_comparePorts
   * @dataProvider transformDataProvider
   * @param string $currentUrl
   * @param string $targetUrl
   * @param string $expected
   */
  public function testTransformWithStringArguments($currentUrl, $targetUrl, $expected) {
    $transformer = new Relative();
    $this->assertSame(
      $expected,
      $transformer->transform(
        $currentUrl,
        $targetUrl
      )
    );
  }

  /**
   * @covers \Papaya\URL\Transformer\Relative::getRelativePath
   * @dataProvider getRelativePathDataProvider
   * @param string $currentPath
   * @param string $targetPath
   * @param string $expected
   */
  public function testGetRelativePath($currentPath, $targetPath, $expected) {
    $transformer = new Relative();
    $this->assertEquals(
      $expected,
      $transformer->getRelativePath($currentPath, $targetPath)
    );
  }

  /*************************************
   * Data Providers
   *************************************/

  public static function transformDataProvider() {
    return array(
      'Valid: Full URL' => array(
        'http://www.sample.tld:80/foo',
        'http://www.sample.tld/foo?arg=1#fragment',
        'foo?arg=1#fragment'
      ),
      'Valid: Port 80 - Default Port' => array(
        'http://www.sample.tld:80/foo',
        'http://www.sample.tld/foo',
        'foo'
      ),
      'Valid: Default Port - Port 80' => array(
        'http://www.sample.tld:80/foo',
        'http://www.sample.tld/foo',
        'foo'
      ),
      'Invalid: Empty Target Host' => array(
        'http://www.sample.tld/foo',
        '',
        NULL
      ),
      'Invalid: Different scheme' => array(
        'http://www.sample.tld/foo',
        'https://www.sample.tld/foo',
        NULL
      ),
      'Invalid: Different host' => array(
        'http://www.sample.tld/foo',
        'http://www.sample2.tld/foo',
        NULL
      ),
      'Invalid: Different port' => array(
        'http://www.sample.tld/foo',
        'http://www.sample.tld:8080/foo',
        NULL
      ),
      'Invalid: Authentication needed' => array(
        'http://www.sample.tld/foo',
        'http://user:pass@www.sample.tld/foo',
        NULL
      ),
    );
  }

  public static function getRelativePathDataProvider() {
    return array(
      array(
        '',
        '/foo',
        'foo'
      ),
      array(
        '/foo',
        '/',
        './'
      ),
      array(
        '/foo',
        '/bar',
        'bar'
      ),
      array(
        '/foo/foo',
        '/foo/bar',
        'bar'
      ),
      array(
        '/foo/foo',
        '/bar/bar',
        '../bar/bar'
      ),
      array(
        '/foo/',
        '/bar/bar',
        '../bar/bar'
      ),
      array(
        '/papaya/topic.php',
        '/papaya-2.media.preview.2698dc5d16244caddcd3bb1992afa140.png',
        '../papaya-2.media.preview.2698dc5d16244caddcd3bb1992afa140.png',
      )
    );
  }
}
