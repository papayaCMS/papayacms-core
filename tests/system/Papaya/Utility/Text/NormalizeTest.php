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

namespace Papaya\Utility\Text;
require_once __DIR__.'/../../../../bootstrap.php';

class NormalizeTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\Utility\Text\Normalize::toHTTPHeaderName
   * @dataProvider toHttpHeaderNameDataProvider
   * @param string $string
   * @param string $expected
   */
  public function testToHttpHeaderName($string, $expected) {
    $this->assertEquals(
      $expected,
      \Papaya\Utility\Text\Normalize::toHTTPHeaderName($string)
    );
  }

  /*********************************
   * Data Provider
   *********************************/

  public static function toHttpHeaderNameDataProvider() {
    return array(
      'lower case' => array('content-type', 'Content-Type'),
      'upper case' => array('CONTENT-TYPE', 'Content-Type'),
      'mixed case' => array('CoNtEnT-TyPe', 'Content-Type'),
      'single word' => array('cache', 'Cache')
    );
  }
}

