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

class PapayaRequestParserThumbnailTest extends PapayaTestCase {

  /**
   * @covers PapayaRequestParserThumbnail::parse
   * @dataProvider parseDataProvider
   * @param string $path
   * @param array|FALSE $expected
   */
  public function testParse($path, $expected) {
    /** @var PHPUnit_Framework_MockObject_MockObject|PapayaUrl $url */
    $url = $this
      ->getMockBuilder(PapayaUrl::class)
      ->setMethods(array('getPath'))
      ->getMock();
    $url
      ->expects($this->once())
      ->method('getPath')
      ->will($this->returnValue($path));
    $parser = new PapayaRequestParserThumbnail();
    $this->assertSame(
      $expected,
      $parser->parse($url)
    );
  }

  /*************************************
  * Data Provider
  *************************************/

  public static function parseDataProvider() {
    // @codingStandardsIgnoreStart
    return array(
      array(
        '/index.html',
        FALSE
      ),
      array(
        '/title.thumb.1897806da87c1b264444a5e685e76c3d_max_510x480.png',
        array(
          'mode' => 'thumbnail',
          'media_id' => '1897806da87c1b264444a5e685e76c3d',
          'media_uri' => '1897806da87c1b264444a5e685e76c3d_max_510x480.png',
          'thumbnail_mode' => 'max',
          'thumbnail_size' => '510x480',
          'thumbnail_format' => 'png'
        )
      ),
      array(
        '/title.thumb.1897806da87c1b264444a5e685e76c3dv23_max_510x480.png',
        array(
          'mode' => 'thumbnail',
          'media_id' => '1897806da87c1b264444a5e685e76c3d',
          'media_uri' => '1897806da87c1b264444a5e685e76c3dv23_max_510x480.png',
          'media_version' => 23,
          'thumbnail_mode' => 'max',
          'thumbnail_size' => '510x480',
          'thumbnail_format' => 'png'
        )
      ),
      array(
        '/title.thumb.1897806da87c1b264444a5e685e76c3dv23_max_510x480_b3535db83dc50e27c1bb1392364c95a2.png',
        array(
          'mode' => 'thumbnail',
          'media_id' => '1897806da87c1b264444a5e685e76c3d',
          'media_uri' => '1897806da87c1b264444a5e685e76c3dv23_max_510x480_b3535db83dc50e27c1bb1392364c95a2.png',
          'media_version' => 23,
          'thumbnail_mode' => 'max',
          'thumbnail_size' => '510x480',
          'thumbnail_params' => 'b3535db83dc50e27c1bb1392364c95a2',
          'thumbnail_format' => 'png'
        )
      ),
      array(
        '/hn999sramon-7esrp-tours5.thumb.preview.d7e21e7a82c200090aa0e29327ad4581v23_max_200x150_b3535db83dc50e27c1bb1392364c95a2.png',
        array(
          'mode' => 'thumbnail',
          'preview' => TRUE,
          'media_id' => 'd7e21e7a82c200090aa0e29327ad4581',
          'media_uri' => 'd7e21e7a82c200090aa0e29327ad4581v23_max_200x150_b3535db83dc50e27c1bb1392364c95a2.png',
          'media_version' => 23,
          'thumbnail_mode' => 'max',
          'thumbnail_size' => '200x150',
          'thumbnail_params' => 'b3535db83dc50e27c1bb1392364c95a2',
          'thumbnail_format' => 'png'
        )
      )
    );
    // @codingStandardsIgnoreEnd
  }
}

