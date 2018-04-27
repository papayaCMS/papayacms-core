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

require_once __DIR__.'/../../../../../../../bootstrap.php';

class PapayaFilterFactoryProfileIsGermanZipTest extends PapayaTestCase {

  /**
   * @covers PapayaFilterFactoryProfileIsGermanZip::getFilter
   * @dataProvider provideValidZips
   * @param string $zip
   */
  public function testGetFilterExpectTrue($zip) {
    $profile = new PapayaFilterFactoryProfileIsGermanZip();
    $this->assertTrue($profile->getFilter()->validate($zip));
  }

  /**
   * @covers PapayaFilterFactoryProfileIsGermanZip::getFilter
   */
  public function testGetFilterExpectException() {
    $profile = new PapayaFilterFactoryProfileIsGermanZip();
    $this->expectException(PapayaFilterException::class);
    $profile->getFilter()->validate('foo');
  }

  public static function provideValidZips() {
    return array(
      array('50670'),
      array('D-50670')
    );
  }
}
