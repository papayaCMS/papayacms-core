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

class MediaTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Request\Parser\Media::parse
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
    $parser = new Media();
    $this->assertSame(
      $expected,
      $parser->parse($url)
    );
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
        '/index.media.01234567890123456789012345678901',
        array(
          'mode' => 'media',
          'media_id' => '01234567890123456789012345678901',
          'media_uri' => '01234567890123456789012345678901'
        )
      ),
      array(
        '/index.thumb.01234567890123456789012345678901',
        array(
          'mode' => 'media',
          'media_id' => '01234567890123456789012345678901',
          'media_uri' => '01234567890123456789012345678901'
        )
      ),
      array(
        '/index.download.01234567890123456789012345678901',
        array(
          'mode' => 'download',
          'media_id' => '01234567890123456789012345678901',
          'media_uri' => '01234567890123456789012345678901'
        )
      ),
      array(
        '/index.media.01234567890123456789012345678901.jpg',
        array(
          'mode' => 'media',
          'media_id' => '01234567890123456789012345678901',
          'media_uri' => '01234567890123456789012345678901.jpg'
        )
      ),
      array(
        '/index.media.01234567890123456789012345678901v23.jpg',
        array(
          'mode' => 'media',
          'media_id' => '01234567890123456789012345678901',
          'media_uri' => '01234567890123456789012345678901v23.jpg',
          'media_version' => 23
        )
      ),
      array(
        '/sid2fb2e3bd142f20e238d39b64eb6d3195/index.media.01234567890123456789012345678901v23.jpg',
        array(
          'mode' => 'media',
          'media_id' => '01234567890123456789012345678901',
          'media_uri' => '01234567890123456789012345678901v23.jpg',
          'media_version' => 23
        )
      ),
      array(
        '/hn999sramon-7esrp-tours5.download.preview.d7e21e7a82c200090aa0e29327ad4581v23',
        array(
          'mode' => 'download',
          'preview' => TRUE,
          'media_id' => 'd7e21e7a82c200090aa0e29327ad4581',
          'media_uri' => 'd7e21e7a82c200090aa0e29327ad4581v23',
          'media_version' => 23
        )
      ),
      array(
        '/test-mp3.download.preview.dd68030bbe132f36922f855c48e71172v23.mp3',
        array(
          'mode' => 'download',
          'preview' => TRUE,
          'media_id' => 'dd68030bbe132f36922f855c48e71172',
          'media_uri' => 'dd68030bbe132f36922f855c48e71172v23.mp3',
          'media_version' => 23
        )
      )
    );
  }
}

