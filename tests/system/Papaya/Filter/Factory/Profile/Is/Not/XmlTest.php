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

class PapayaFilterFactoryProfileIsNotXmlTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Filter\Factory\Profile\IsNotXml::getFilter
   * @dataProvider provideNotXmlStrings
   * @param string $string
   */
  public function testGetFilterExpectTrue($string) {
    $profile = new \Papaya\Filter\Factory\Profile\IsNotXml();
    $this->assertTrue($profile->getFilter()->validate($string));
  }

  /**
   * @covers \Papaya\Filter\Factory\Profile\IsNotXml::getFilter
   * @dataProvider provideXmlStrings
   * @param string $string
   */
  public function testGetFilterExpectException($string) {
    $profile = new \Papaya\Filter\Factory\Profile\IsNotXml();
    $this->expectException(\Papaya\Filter\Exception::class);
    $profile->getFilter()->validate($string);
  }

  public static function provideNotXmlStrings() {
    return array(
      array('foo'),
      array('foo "bar"')
    );
  }

  public static function provideXmlStrings() {
    return array(
      array('<'),
      array('>'),
      array('&')
    );
  }
}
