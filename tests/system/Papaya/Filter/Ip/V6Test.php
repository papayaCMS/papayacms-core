<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaFilterIpV6Test extends PapayaTestCase {
  /**
  * @covers PapayaFilterIpV6::validate
  * @dataProvider getIpV6ValidDataProvider
  */
  public function testValidateValid($ip) {
    $ipV6 = new PapayaFilterIpV6();
    $this->assertTrue($ipV6->validate($ip));
  }

  /**
  * @covers PapayaFilterIpV6::validate
  * @dataProvider getIpV6CountMismatchProvider
  */
  public function testValidateExpectingCountMismatch($ip) {
    $ipV6 = new PapayaFilterIpV6();
    $this->setExpectedException('PapayaFilterExceptionCountMismatch');
    $ipV6->validate($ip);
  }

  /**
  * @covers PapayaFilterIpV6::validate
  * @dataProvider getIpV6CountMismatchProvider
  */
  public function testValidateExpectingEmptyException() {
    $ipV6 = new PapayaFilterIpV6();
    $this->setExpectedException('PapayaFilterExceptionEmpty');
    $ipV6->validate('');
  }

  /**
  * @covers PapayaFilterIpV6::validate
  * @dataProvider getIpV6PartInvalidProvider
  */
  public function testValidateExpectingPartInvalid($ip) {
    $ipV6 = new PapayaFilterIpV6();
    $this->setExpectedException('PapayaFilterExceptionPartInvalid');
    $ipV6->validate($ip);
  }

  /**
  * @covers PapayaFilterIpV6::filter
  * @dataProvider getFilterProvider
  */
  public function testFilter($expected, $ip) {
    $ipV6 = new PapayaFilterIpV6();
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