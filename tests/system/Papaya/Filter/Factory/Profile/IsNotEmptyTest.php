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

class IsNotEmptyTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Filter\Factory\Profile\IsNotEmpty::getFilter
   * @dataProvider provideNotEmptyStrings
   * @param string $string
   * @throws \Papaya\Filter\Exception
   */
  public function testGetFilterExpectTrue($string) {
    $profile = new IsNotEmpty();
    $this->assertTrue($profile->getFilter()->validate($string));
  }

  /**
   * @covers \Papaya\Filter\Factory\Profile\IsNotEmpty::getFilter
   * @dataProvider provideEmptyStrings
   * @param string $string
   * @throws \Papaya\Filter\Exception
   */
  public function testGetFilterExpectException($string) {
    $profile = new IsNotEmpty();
    $this->expectException(\Papaya\Filter\Exception::class);
    $profile->getFilter()->validate($string);
  }

  public static function provideNotEmptyStrings() {
    return array(
      array('0'),
      array('42'),
      array('foo'),
      array(' bar '),
      array('bla blub'),
    );
  }

  public static function provideEmptyStrings() {
    return array(
      array(''),
      array(' '),
      array('  '),
      array("\t")
    );
  }
}
