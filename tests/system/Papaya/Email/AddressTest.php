<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaEmailAddressTest extends PapayaTestCase {

  /**
  * @covers PapayaEmailAddress::__construct
  */
  public function testConstructorWithAddress() {
    $address = new PapayaEmailAddress('John Doe <john.doe@local.tld>');
    $this->assertEquals('John Doe <john.doe@local.tld>', (string)$address);
  }

  /**
  * @covers PapayaEmailAddress::__construct
  * @covers PapayaEmailAddress::__set
  * @covers PapayaEmailAddress::__get
  * @covers PapayaEmailAddress::setAddress
  */
  public function testPropertyAddress() {
    $address = new PapayaEmailAddress();
    $address->address = 'John Doe <john.doe@local.tld>';
    $this->assertEquals('John Doe <john.doe@local.tld>', $address->address);
    $this->assertEquals('John Doe', $address->name);
    $this->assertEquals('john.doe@local.tld', $address->email);
  }

  /**
  * @covers PapayaEmailAddress::__set
  * @covers PapayaEmailAddress::__get
  * @covers PapayaEmailAddress::setName
  */
  public function testPropertyName() {
    $address = new PapayaEmailAddress();
    $address->name = 'John Doe';
    $this->assertEquals('John Doe', $address->name);
  }

  /**
  * @covers PapayaEmailAddress::__set
  * @covers PapayaEmailAddress::__get
  * @covers PapayaEmailAddress::setAddress
  */
  public function testPropertyEmail() {
    $address = new PapayaEmailAddress();
    $address->email = 'john.doe@local.tld';
    $this->assertEquals('john.doe@local.tld', $address->email);
  }

  /**
  * @covers PapayaEmailAddress::__toString
  */
  public function testMagicMethodToString() {
    $address = new PapayaEmailAddress();
    $address->name = 'John Doe';
    $address->email = 'john.doe@local.tld';
    $this->assertEquals('John Doe <john.doe@local.tld>', (string)$address);
  }

  /**
  * @covers PapayaEmailAddress::__toString
  */
  public function testMagicMethodToStringWithEmailOnly() {
    $address = new PapayaEmailAddress();
    $address->email = 'john.doe@local.tld';
    $this->assertEquals('john.doe@local.tld', (string)$address);
  }

  /**
  * @covers PapayaEmailAddress::__set
  */
  public function testSetUnknownPropertyExpectingException() {
    $address = new PapayaEmailAddress();
    $this->setExpectedException(
      'InvalidArgumentException',
      'InvalidArgumentException: Unknown property "unknown".'
    );
    $address->unknown = 'test';
  }

  /**
  * @covers PapayaEmailAddress::__get
  */
  public function testGetUnknownPropertyExpectingException() {
    $address = new PapayaEmailAddress();
    $this->setExpectedException(
      'InvalidArgumentException',
      'InvalidArgumentException: Unknown property "unknown".'
    );
    $dummy = $address->unknown;
  }
}
