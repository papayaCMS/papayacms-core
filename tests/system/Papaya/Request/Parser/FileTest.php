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

class PapayaRequestParserFileTest extends PapayaTestCase {

  /**
   * @covers \PapayaRequestParserFile::parse
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
    $parser = new \PapayaRequestParserFile();
    $this->assertSame(
      $expected,
      $parser->parse($url)
    );
  }

  /**
  * @covers \PapayaRequestParserFile::isLast
  */
  public function testIsLast() {
    $parser = new \PapayaRequestParserFile();
    $this->assertFalse($parser->isLast());
  }

  /*************************************
  * Data Provider
  *************************************/

  public static function parseDataProvider() {
    return array(
      array(
        '',
        FALSE
      ),
      array(
        '/',
        array(
          'file_path' => '/',
        )
      ),
      array(
        '/index.html',
        array(
          'file_path' => '/',
          'file_name' => 'index.html',
          'file_title' => 'index',
          'file_extension' => 'html',
        )
      ),
      array(
        '/sidb57dae676543ce5717fa20ed6c3d5476/index.5.en.html',
        array(
          'file_path' => '/',
          'file_name' => 'index.5.en.html',
          'file_title' => 'index',
          'file_extension' => 'html',
        )
      ),
      array(
        '/sidb57dae676543ce5717fa20ed6c3d5476/index.5.en.html.preview',
        array(
          'file_path' => '/',
          'file_name' => 'index.5.en.html.preview',
          'file_title' => 'index',
          'file_extension' => 'html',
        )
      ),
    );
  }
}

