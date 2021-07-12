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

/**
 * @covers \Papaya\Message\Context\Memory
 */
class MemoryTest extends \Papaya\TestFramework\TestCase {

  public function testRememberMemoryUsage() {
    $context = new Memory();
    $context->rememberMemoryUsage(42);
    $this->assertEquals(
      42,
      $context->getPreviousUsage()
    );
  }

  public function testSetMemoryUsage() {
    $context = new Memory();
    $context->rememberMemoryUsage(2);
    $context->setMemoryUsage(23, 42);
    $this->assertEquals(
      23,
      $context->getCurrentUsage()
    );
    $this->assertEquals(
      42,
      $context->getPeakUsage()
    );
    $this->assertEquals(
      21,
      $context->getUsageDifference()
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
