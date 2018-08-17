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

namespace Papaya\Utility\Text\Ascii;
require_once __DIR__.'/../../../../../bootstrap.php';

class ArtworkTest extends \PapayaTestCase {

  /**
   * @covers       \Papaya\Utility\Text\Ascii\Artwork::get
   * @dataProvider getDataProvider
   * @param string $string
   * @param string $fileName
   */
  public function testGet($string, $fileName) {
    $this->assertStringEqualsFile(
      __DIR__.'/TestData/'.$fileName,
      Artwork::get($string)
    );
  }

  /*********************************
   * Data Provider
   *********************************/

  public static function getDataProvider() {
    return array(
      'ascii' => array('ascii', 'ascii.txt'),
      'numbers' => array('0123456789', 'numbers.txt'),
      'letters' => array('abcdefghijklmnopqrstuvwxyz', 'letters.txt'),
      'special chars' => array('-+:', 'special.txt')
    );
  }
}
