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

namespace Papaya\Filter\Ip;
require_once __DIR__.'/../../../../bootstrap.php';

class V6Test extends \Papaya\TestFramework\TestCase {
  /**
   * @covers \Papaya\Filter\Ip\V6::validate
   * @dataProvider getIpV6ValidDataProvider
   * @param string $ip
   * @throws \Papaya\Filter\Exception\InvalidCount
   * @throws \Papaya\Filter\Exception\IsEmpty
   * @throws \Papaya\Filter\Exception\InvalidPart
   */
  public function testValidateValid($ip) {
    $ipV6 = new V6();
    $this->assertTrue($ipV6->validate($ip));
  }

  /**
   * @covers \Papaya\Filter\Ip\V6::validate
   * @dataProvider getIpV6CountMismatchProvider
   * @param string $ip
   * @throws \Papaya\Filter\Exception\InvalidCount
   * @throws \Papaya\Filter\Exception\IsEmpty
   * @throws \Papaya\Filter\Exception\InvalidPart
   */
  public function testValidateExpectingCountMismatch($ip) {
    $ipV6 = new V6();
    $this->expectException(\Papaya\Filter\Exception\InvalidCount::class);
    $ipV6->validate($ip);
  }

  /**
   * @covers \Papaya\Filter\Ip\V6::validate
   * @dataProvider getIpV6CountMismatchProvider
   */
  public function testValidateExpectingEmptyException() {
    $ipV6 = new V6();
    $this->expectException(\Papaya\Filter\Exception\IsEmpty::class);
    $ipV6->validate('');
  }

  /**
   * @covers \Papaya\Filter\Ip\V6::validate
   * @dataProvider getIpV6PartInvalidProvider
   * @param string $ip
   * @throws \Papaya\Filter\Exception\InvalidCount
   * @throws \Papaya\Filter\Exception\IsEmpty
   * @throws \Papaya\Filter\Exception\InvalidPart
   */
  public function testValidateExpectingPartInvalid($ip) {
    $ipV6 = new V6();
    $this->expectException(\Papaya\Filter\Exception\InvalidPart::class);
    $ipV6->validate($ip);
  }

  /**
   * @covers \Papaya\Filter\Ip\V6::filter
   * @dataProvider getFilterProvider
   * @param string $expected
   * @param string $ip
   */
  public function testFilter($expected, $ip) {
    $ipV6 = new V6();
    $filtered = $ipV6->filter($ip);
    $this->assertEquals($expected, $filtered);
  }

  public static function getIpV6ValidDataProvider() {
    return array(
      array('AAAA:AAAA:AAAA:AAAA:AAAA:AAAA:AAAA:AAAA'),
      array('AAAA::AAAA'),
      array('::AAAA'),
      array('::1'),
      array('AAAA::'),
      array('1::'),
      array('1abc::def0')
    );
  }

  public static function getIpV6CountMismatchProvider() {
    return array(
      array('1111:1111:1111'),
      array('FFFF:FFFF:FFFF:FFFF:FFFF:FFFF:FFFF:FFFF:FFFF'),
      array(':::1'),
      array(':1:2:3:4:5:6:7')
    );
  }

  public static function getIpV6PartInvalidProvider() {
    return array(
      array('fghi::0'),
      array('!:9::0'),
      array('a::-1'),
      array('4444::33::1')
    );
  }

  public static function getFilterProvider() {
    return array(
      array('::1', ' ::1'),
      array('::1', '::1 '),
      array('::1', ' ::1 '),
      array(NULL, '::x')
    );
  }
}
