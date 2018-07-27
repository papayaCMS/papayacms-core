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

require_once __DIR__.'/../../../../../../../../bootstrap.php';

class PapayaFilterFactoryProfileIsIsoDateTimeTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Filter\Factory\Profile\Is\Iso\Date\Time::getFilter
   * @dataProvider provideValidDatetimeStrings
   * @param string $datetime
   */
  public function testGetFilterExpectTrue($datetime) {
    $profile = new \Papaya\Filter\Factory\Profile\Is\Iso\Date\Time();
    $this->assertTrue($profile->getFilter()->validate($datetime));
  }

  /**
   * @covers \Papaya\Filter\Factory\Profile\Is\Iso\Date\Time::getFilter
   */
  public function testGetFilterExpectException() {
    $profile = new \Papaya\Filter\Factory\Profile\Is\Iso\Date\Time();
    $this->expectException(\PapayaFilterException::class);
    $profile->getFilter()->validate('foo');
  }

  public static function provideValidDatetimeStrings() {
    return array(
      array('2012-08-15 13:37'),
      array('2012-08-15T13:37'),
      array('2012-08-15T13:37+0200')
    );
  }
}
