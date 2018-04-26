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
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('InvalidArgumentException: Unknown property "unknown".');
    /** @noinspection PhpUndefinedFieldInspection */
    $address->unknown = 'test';
  }

  /**
  * @covers PapayaEmailAddress::__get
  */
  public function testGetUnknownPropertyExpectingException() {
    $address = new PapayaEmailAddress();
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('InvalidArgumentException: Unknown property "unknown".');
    /** @noinspection PhpUndefinedFieldInspection */
    $address->unknown;
  }
}
