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

namespace Papaya\UI\Text;
require_once __DIR__.'/../../../../bootstrap.php';

/**
 * @covers \Papaya\UI\Text\Date
 */
class DateTest extends \Papaya\TestFramework\TestCase {

  public function testMagicMethodToString() {
    $string = new Date(strtotime('2011-08-25 16:00:00'));
    $this->assertEquals(
      '2011-08-25 16:00', (string)$string
    );
  }

  public function testMagicMethodToStringWithTime() {
    $string = new Date(
      strtotime('2011-08-25 16:00:00'),
      Date::SHOW_TIME
    );
    $this->assertEquals(
      '2011-08-25 16:00', (string)$string
    );
  }

  public function testMagicMethodToStringWithTimeAndSeconds() {
    $string = new Date(
      strtotime('2011-08-25 16:00:00'),
      Date::SHOW_TIME | Date::SHOW_SECONDS
    );
    $this->assertEquals(
      '2011-08-25 16:00:00', (string)$string
    );
  }

  public function testMagicMethodToStringWithSecondsExpectingDateOnly() {
    $string = new Date(
      strtotime('2011-08-25 16:00:00'),
      Date::SHOW_SECONDS
    );
    $this->assertEquals(
      '2011-08-25', (string)$string
    );
  }

}
