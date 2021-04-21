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
/**
 * @covers \Papaya\Email\Address
 */
class AddressTest extends \Papaya\TestCase {

  public function testConstructorWithAddress(): void {
    $address = new Address('John Doe <john.doe@local.tld>');
    $this->assertEquals('John Doe <john.doe@local.tld>', (string)$address);
  }

  public function testPropertyAddress(): void {
    $address = new Address();
    $address->address = 'John Doe <john.doe@local.tld>';
    $this->assertEquals('John Doe <john.doe@local.tld>', $address->address);
    $this->assertEquals('John Doe', $address->name);
    $this->assertEquals('john.doe@local.tld', $address->email);
  }

  public function testPropertyAddressAndName(): void {
    $address = new Address('john.doe@local.tld', 'John Doe');
    $this->assertEquals('John Doe <john.doe@local.tld>', $address->address);
    $this->assertEquals('John Doe', $address->name);
    $this->assertEquals('john.doe@local.tld', $address->email);
  }

  public function testPropertyName(): void {
    $address = new Address();
    $address->name = 'John Doe';
    $this->assertEquals('John Doe', $address->name);
  }

  public function testPropertyEmail(): void {
    $address = new Address();
    $address->email = 'john.doe@local.tld';
    $this->assertEquals('john.doe@local.tld', $address->email);
  }

  public function testMagicMethodToString(): void {
    $address = new Address();
    $address->name = 'John Doe';
    $address->email = 'john.doe@local.tld';
    $this->assertEquals('John Doe <john.doe@local.tld>', (string)$address);
  }

  public function testMagicMethodToStringWithEmailOnly(): void {
    $address = new Address();
    $address->email = 'john.doe@local.tld';
    $this->assertEquals('john.doe@local.tld', (string)$address);
  }

  public function testSetUnknownPropertyExpectingException(): void {
    $address = new Address();
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('InvalidArgumentException: Unknown property "unknown".');
    /** @noinspection PhpUndefinedFieldInspection */
    $address->unknown = 'test';
  }

  public function testGetUnknownPropertyExpectingException(): void {
    $address = new Address();
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('InvalidArgumentException: Unknown property "unknown".');
    /** @noinspection PhpUndefinedFieldInspection */
    $address->unknown;
  }
}
