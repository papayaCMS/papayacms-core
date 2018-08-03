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

class PapayaUiStringDateTest extends \PapayaTestCase {

  /**
  * @covers \Papaya\UI\Text\Date::__construct
  */
  public function testConstructor() {
    $string = new \Papaya\UI\Text\Date(strtotime('2011-08-25 16:00:00'));
    $this->assertAttributeEquals(
      strtotime('2011-08-25 16:00:00'), '_timestamp', $string
    );
  }

  /**
  * @covers \Papaya\UI\Text\Date::__toString
  */
  public function testMagicMethodToString() {
    $string = new \Papaya\UI\Text\Date(strtotime('2011-08-25 16:00:00'));
    $this->assertEquals(
      '2011-08-25 16:00', (string)$string
    );
  }

  /**
  * @covers \Papaya\UI\Text\Date::__toString
  */
  public function testMagicMethodToStringWithTime() {
    $string = new \Papaya\UI\Text\Date(
      strtotime('2011-08-25 16:00:00'),
      \Papaya\UI\Text\Date::SHOW_TIME
    );
    $this->assertEquals(
      '2011-08-25 16:00', (string)$string
    );
  }

  /**
  * @covers \Papaya\UI\Text\Date::__toString
  */
  public function testMagicMethodToStringWithTimeAndSeconds() {
    $string = new \Papaya\UI\Text\Date(
      strtotime('2011-08-25 16:00:00'),
      \Papaya\UI\Text\Date::SHOW_TIME | \Papaya\UI\Text\Date::SHOW_SECONDS
    );
    $this->assertEquals(
      '2011-08-25 16:00:00', (string)$string
    );
  }

  /**
  * @covers \Papaya\UI\Text\Date::__toString
  */
  public function testMagicMethodToStringWithSecondsExpectingDateOnly() {
    $string = new \Papaya\UI\Text\Date(
      strtotime('2011-08-25 16:00:00'),
      \Papaya\UI\Text\Date::SHOW_SECONDS
    );
    $this->assertEquals(
      '2011-08-25', (string)$string
    );
  }

}
