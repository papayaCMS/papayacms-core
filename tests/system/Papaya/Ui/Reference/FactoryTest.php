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

class PapayaUiReferenceFactoryTest extends \PapayaTestCase {

  /**
   * @covers \PapayaUiReferenceFactory
   * @dataProvider provideStringsAndExpectedUrls
   * @param string $expected
   * @param string $string
   */
  public function testByString($expected, $string) {
    $factory = new \PapayaUiReferenceFactory();
    $factory->papaya($this->mockPapaya()->application());
    $reference = $factory->byString($string);
    $this->assertEquals($expected, (string)$reference);
  }

  /*******************************
   * Data Provider
   ******************************/

  public static function provideStringsAndExpectedUrls() {
    return array(
      array('http://www.test.tld/test.html', ''),
      array('http://www.papaya-cms.com', 'http://www.papaya-cms.com'),
      array('http://www.test.tld/foo/bar', '/foo/bar'),
      array('http://www.test.tld/foo/bar', 'foo/bar'),
      array('http://www.test.tld/index.42.html', '42'),
      array('http://www.test.tld/index.21.42.html', '21.42'),
      array('http://www.test.tld/index.21.42.en.html', '21.42.en'),
      array('http://www.test.tld/index.21.42.en.atom', '21.42.en.atom'),
      array('http://www.test.tld/21.42.en.atom?foo=bar', '/21.42.en.atom?foo=bar')
    );
  }
}
