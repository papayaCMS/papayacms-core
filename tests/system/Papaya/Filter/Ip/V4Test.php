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

class PapayaFilterIpV4Test extends \PapayaTestCase {

  /**
   * @covers \PapayaFilterIpV4::__construct
   */
  public function testConstructSuccess() {
    $filter = new \PapayaFilterIpV4(
      \PapayaFilterIpV4::ALLOW_LINK_LOCAL | \PapayaFilterIpV4::ALLOW_LOOPBACK
    );
    $this->assertAttributeEquals(
      \PapayaFilterIpV4::ALLOW_LINK_LOCAL | \PapayaFilterIpV4::ALLOW_LOOPBACK,
      '_configuration',
      $filter
    );
  }

  /**
   * @covers \PapayaFilterIpV4::__construct
   */
  public function testConstructInvalidArgumentException() {
    $this->expectException(InvalidArgumentException::class);
    new \PapayaFilterIpV4('InvalidConfiguration');
  }

  /**
   * @covers \PapayaFilterIpV4::__construct
   * @dataProvider getConfigurationOutOfRangeDataProvider
   * @param int $configuration
   */
  public function testConstructOutOfBoundsException($configuration) {
    $this->expectException(OutOfRangeException::class);
    new \PapayaFilterIpV4($configuration);
  }

  public static function getConfigurationOutOfRangeDataProvider() {
    return array(
      array(-1),
      array(32767)
    );
  }

  /**
   * @covers \PapayaFilterIpV4::validate
   * @dataProvider getValidateDataProvider
   * @param string $ip
   * @param int $config
   * @throws \Papaya\Filter\Exception\InvalidCount
   * @throws \Papaya\Filter\Exception\InvalidPart
   */
  public function testValidate($ip, $config = 15) {
    $filter = new \PapayaFilterIpV4($config);
    $this->assertTrue($filter->validate($ip));
  }

  public static function getValidateDataProvider() {
    return array(
      array('0.0.0.0'),
      array('1.1.1.1'),
      array('255.255.255.255'),
      array('127.0.0.1'),
      array('192.168.0.1'),
      array('10.0.0.0'),
      array('172.16.0.1'),
      array('127.0.0.1', 7)
    );
  }

  /**
   * @covers \PapayaFilterIpV4::validate
   * @param string $ip
   * @throws \Papaya\Filter\Exception\InvalidCount
   * @throws \Papaya\Filter\Exception\InvalidPart
   * @dataProvider getValidateExceptionCountMismatchDataProvider
   */
  public function testValidateExceptionCountMismatch($ip) {
    $filter = new \PapayaFilterIpV4();
    $this->expectException(\Papaya\Filter\Exception\InvalidCount::class);
    $filter->validate($ip);
  }

  public static function getValidateExceptionCountMismatchDataProvider() {
    return array(
      array(''),
      array('1.1.1'),
      array('1.1.1.1.1'),
      array('1'),
      array('-1'),
    );
  }

  /**
   * @covers \PapayaFilterIpV4::validate
   * @dataProvider getValidateExceptionPartInvalidDataProvider
   * @param string $ip
   * @throws \Papaya\Filter\Exception\InvalidCount
   * @throws \Papaya\Filter\Exception\InvalidPart
   */
  public function testValidateExceptionPartInvalid($ip) {
    $filter = new \PapayaFilterIpV4();
    $this->expectException(\Papaya\Filter\Exception\InvalidPart::class);
    $filter->validate($ip);
  }

  public static function getValidateExceptionPartInvalidDataProvider() {
    return array(
      array('1...1'),
      array('1.1a.1.1'),
      array('a1.1.1.1'),
      array('1. 1.1.1'),
      array('1.1.257.1'),
      array('1.-1.1.1'),
    );
  }

  /**
   * @covers \PapayaFilterIpV4::validate
   * @dataProvider getValidateInvalidArgumentExceptionDataProvider
   * @param string $ip
   * @param int $configuration
   * @throws \Papaya\Filter\Exception\InvalidCount
   * @throws \Papaya\Filter\Exception\InvalidPart
   */
  public function testValidateInvalidArgumentException($ip, $configuration) {
    $filter = new \PapayaFilterIpV4($configuration);
    $this->expectException(InvalidArgumentException::class);
    $filter->validate($ip);
  }

  public static function getValidateInvalidArgumentExceptionDataProvider() {
    return array(
      array('0.0.0.0', 0),
      array('255.255.255.255', 1),
      array('127.0.0.1', 3),
      array('172.16.0.1', 7)
    );
  }

  /**
   * @covers \PapayaFilterIpV4::filter
   * @dataProvider getFilterDataProvider
   * @param string $expected
   * @param string $input
   * @param int $configuration
   */
  public function testFilter($expected, $input, $configuration = 15) {
    $filter = new \PapayaFilterIpV4($configuration);
    $this->assertEquals($expected, $filter->filter($input));
  }

  public static function getFilterDataProvider() {
    return array(
      array('1.1.1.1', ' 1.1.1.1'),
      array('1.1.1.1', '1.1.1.1 '),
      array('1.1.1.1', ' 1.1.1.1 '),
      array('1.1.1.1', '1.1.1.1'),
      array(NULL, ' 1. 1.1.1'),
      array(NULL, '0.0.0.0', 0)
    );
  }
}
