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

namespace Papaya\Message\Context;
require_once __DIR__.'/../../../../bootstrap.php';

class MemoryTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Message\Context\Memory::__construct
   */
  public function testContructor() {
    $context = new Memory();
    $this->assertAttributeGreaterThan(
      0,
      '_currentUsage',
      $context
    );
    $this->assertAttributeGreaterThan(
      0,
      '_peakUsage',
      $context
    );
  }

  /**
   * @covers \Papaya\Message\Context\Memory::rememberMemoryUsage
   */
  public function testRememberMemoryUsage() {
    $context = new Memory();
    $context->rememberMemoryUsage(42);
    $this->assertAttributeEquals(
      42,
      '_previousUsage',
      Memory::class
    );
  }

  /**
   * @covers \Papaya\Message\Context\Memory::setMemoryUsage
   */
  public function testSetMemoryUsage() {
    $context = new Memory();
    $context->rememberMemoryUsage(2);
    $context->setMemoryUsage(23, 42);
    $this->assertAttributeEquals(
      23,
      '_currentUsage',
      $context
    );
    $this->assertAttributeEquals(
      42,
      '_peakUsage',
      $context
    );
    $this->assertAttributeEquals(
      21,
      '_diffUsage',
      $context
    );
  }

  /**
   * @covers \Papaya\Message\Context\Memory::asString
   */
  public function testAsStringWithIncreasingUsage() {
    $context = new Memory();
    $context->rememberMemoryUsage(23);
    $context->setMemoryUsage(3117, 4221);
    $this->assertEquals(
      'Memory Usage: 3,117 bytes (+3,094 bytes) | Peak Usage: 4,221 Bytes',
      $context->asString()
    );
  }

  /**
   * @covers \Papaya\Message\Context\Memory::asString
   */
  public function testAsStringWithDecreasingUsage() {
    $context = new Memory();
    $context->rememberMemoryUsage(3117);
    $context->setMemoryUsage(23, 4221);
    $this->assertEquals(
      'Memory Usage: 23 bytes (-3,094 bytes) | Peak Usage: 4,221 Bytes',
      $context->asString()
    );
  }
}
