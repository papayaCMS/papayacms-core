<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaFilterIpV4Test extends PapayaTestCase {

  /**
  * @covers PapayaFilterIpV4::__construct
  */
  public function testConstructSuccess() {
    $filter = new PapayaFilterIpV4(
      PapayaFilterIpV4::ALLOW_LINK_LOCAL | PapayaFilterIpV4::ALLOW_LOOPBACK
    );
    $this->assertAttributeEquals(
      PapayaFilterIpV4::ALLOW_LINK_LOCAL | PapayaFilterIpV4::ALLOW_LOOPBACK,
      '_configuration',
      $filter
    );
  }

  /**
  * @covers PapayaFilterIpV4::__construct
  */
  public function testConstructInvalidArgumentException() {
    $this->setExpectedException('InvalidArgumentException');
    $filter = new PapayaFilterIpV4('InvalidConfiguration');
  }

  /**
  * @covers PapayaFilterIpV4::__construct
  * @dataProvider getConfigurationOutOfRangeDataProvider
  */
  public function testConstructOutOfBoundsException($config) {
    $this->setExpectedException('OutOfRangeException');
    $filter = new PapayaFilterIpV4($config);
  }

  static public function getConfigurationOutOfRangeDataProvider() {
    return array(
      array(-1),
      array(32767)
    );
  }

  /**
  * @covers PapayaFilterIpV4::validate
  * @dataProvider getValidateDataProvider
  */
  public function testValidate($ip, $config = 15) {
    $filter = new PapayaFilterIpV4($config);
    $this->assertTrue($filter->validate($ip));
  }

  static public function getValidateDataProvider() {
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
  * @covers PapayaFilterIpV4::validate
  * @dataProvider getValidateExceptionCountMismatchDataProvider
  */
  public function testValidateExceptionCountMismatch($ip) {
    $filter = new PapayaFilterIpV4();
    $this->setExpectedException('PapayaFilterExceptionCountMismatch');
    $filter->validate($ip);
  }

  static public function getValidateExceptionCountMismatchDataProvider() {
    return array(
      array(''),
      array('1.1.1'),
      array('1.1.1.1.1'),
      array('1'),
      array('-1'),
    );
  }

  /**
  * @covers PapayaFilterIpV4::validate
  * @dataProvider getValidateExceptionPartInvalidDataProvider
  */
  public function testValidateExceptionPartInvalid($ip) {
    $filter = new PapayaFilterIpV4();
    $this->setExpectedException('PapayaFilterExceptionPartInvalid');
    $filter->validate($ip);
  }

  static public function getValidateExceptionPartInvalidDataProvider() {
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
  * @covers PapayaFilterIpV4::validate
  * @dataProvider getValidateInvalidArgumentExceptionDataProvider
  */
  public function testValidateInvalidArgumentException($ip, $conf) {
    $filter = new PapayaFilterIpV4($conf);
    $this->setExpectedException('InvalidArgumentException');
    $filter->validate($ip);
  }

  static public function getValidateInvalidArgumentExceptionDataProvider() {
    return array(
      array('0.0.0.0', 0),
      array('255.255.255.255', 1),
      array('127.0.0.1', 3),
      array('172.16.0.1', 7)
    );
  }

  /**
  * @covers PapayaFilterIpV4::filter
  * @dataProvider getFilterDataProvider
  */
  public function testFilter($expected, $input, $config = 15) {
    $filter = new PapayaFilterIpV4($config);
    $this->assertEquals($expected, $filter->filter($input));
  }

  static public function getFilterDataProvider() {
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
