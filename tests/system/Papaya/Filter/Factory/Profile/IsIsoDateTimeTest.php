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

namespace Papaya\Filter\Factory\Profile;
require_once __DIR__.'/../../../../../bootstrap.php';

class IsIsoDateTimeTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\Filter\Factory\Profile\IsIsoDateTime::getFilter
   * @dataProvider provideValidDatetimeStrings
   * @param string $datetime
   * @throws \Papaya\Filter\Exception
   */
  public function testGetFilterExpectTrue($datetime) {
    $profile = new IsIsoDateTime();
    $this->assertTrue($profile->getFilter()->validate($datetime));
  }

  /**
   * @covers \Papaya\Filter\Factory\Profile\IsIsoDateTime::getFilter
   */
  public function testGetFilterExpectException() {
    $profile = new IsIsoDateTime();
    $this->expectException(\Papaya\Filter\Exception::class);
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
