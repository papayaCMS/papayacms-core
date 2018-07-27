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

class PapayaFilterFactoryProfileIsServerAddressTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Filter\Factory\Profile\IsServerAddress::getFilter
   * @dataProvider provideServerAddressStrings
   * @param string $string
   */
  public function testGetFilterExpectTrue($string) {
    $profile = new \Papaya\Filter\Factory\Profile\IsServerAddress();
    $this->assertTrue($profile->getFilter()->validate($string));
  }

  /**
   * @covers \Papaya\Filter\Factory\Profile\IsServerAddress::getFilter
   * @dataProvider provideInvalidStrings
   * @param string $string
   */
  public function testGetFilterExpectException($string) {
    $profile = new \Papaya\Filter\Factory\Profile\IsServerAddress();
    $this->expectException(\Papaya\Filter\Exception::class);
    $profile->getFilter()->validate($string);
  }

  public static function provideServerAddressStrings() {
    return array(
      array('localhost'),
      array('www.sample.tld:8080'),
      array('127.0.0.1')
    );
  }

  public static function provideInvalidStrings() {
    return array(
      array(''),
      array(' foo ')
    );
  }
}
