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

namespace Papaya\Email;

require_once __DIR__.'/../../../bootstrap.php';

class AddressTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Email\Address::__construct
   */
  public function testConstructorWithAddress() {
    $address = new Address('John Doe <john.doe@local.tld>');
    $this->assertEquals('John Doe <john.doe@local.tld>', (string)$address);
  }

  /**
   * @covers \Papaya\Email\Address::__construct
   * @covers \Papaya\Email\Address::__set
   * @covers \Papaya\Email\Address::__get
   * @covers \Papaya\Email\Address::setAddress
   */
  public function testPropertyAddress() {
    $address = new Address();
    $address->address = 'John Doe <john.doe@local.tld>';
    $this->assertEquals('John Doe <john.doe@local.tld>', $address->address);
    $this->assertEquals('John Doe', $address->name);
    $this->assertEquals('john.doe@local.tld', $address->email);
  }

  /**
   * @covers \Papaya\Email\Address::__set
   * @covers \Papaya\Email\Address::__get
   * @covers \Papaya\Email\Address::setName
   */
  public function testPropertyName() {
    $address = new Address();
    $address->name = 'John Doe';
    $this->assertEquals('John Doe', $address->name);
  }

  /**
   * @covers \Papaya\Email\Address::__set
   * @covers \Papaya\Email\Address::__get
   * @covers \Papaya\Email\Address::setAddress
   */
  public function testPropertyEmail() {
    $address = new Address();
    $address->email = 'john.doe@local.tld';
    $this->assertEquals('john.doe@local.tld', $address->email);
  }

  /**
   * @covers \Papaya\Email\Address::__toString
   */
  public function testMagicMethodToString() {
    $address = new Address();
    $address->name = 'John Doe';
    $address->email = 'john.doe@local.tld';
    $this->assertEquals('John Doe <john.doe@local.tld>', (string)$address);
  }

  /**
   * @covers \Papaya\Email\Address::__toString
   */
  public function testMagicMethodToStringWithEmailOnly() {
    $address = new Address();
    $address->email = 'john.doe@local.tld';
    $this->assertEquals('john.doe@local.tld', (string)$address);
  }

  /**
   * @covers \Papaya\Email\Address::__set
   */
  public function testSetUnknownPropertyExpectingException() {
    $address = new Address();
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('InvalidArgumentException: Unknown property "unknown".');
    /** @noinspection PhpUndefinedFieldInspection */
    $address->unknown = 'test';
  }

  /**
   * @covers \Papaya\Email\Address::__get
   */
  public function testGetUnknownPropertyExpectingException() {
    $address = new Address();
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('InvalidArgumentException: Unknown property "unknown".');
    /** @noinspection PhpUndefinedFieldInspection */
    $address->unknown;
  }
}
