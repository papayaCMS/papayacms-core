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

namespace Papaya\Cache\Identifier\Definition;

require_once __DIR__.'/../../../../../bootstrap.php';

class BooleanValueTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\Cache\Identifier\Definition\BooleanValue
   */
  public function testGetStatusForBooleanReturningTrue() {
    $definition = new BooleanValue(TRUE);
    $this->assertTrue($definition->getStatus());
  }

  /**
   * @covers \Papaya\Cache\Identifier\Definition\BooleanValue
   */
  public function testGetStatusForBooleanReturningFalse() {
    $definition = new BooleanValue(FALSE);
    $this->assertFalse($definition->getStatus());
  }

  /**
   * @covers \Papaya\Cache\Identifier\Definition\BooleanValue
   */
  public function testGetStatusForCallableReturningTrue() {
    $definition = new BooleanValue(function () {
      return TRUE;
    });
    $this->assertTrue($definition->getStatus());
  }

  /**
   * @covers \Papaya\Cache\Identifier\Definition\BooleanValue
   */
  public function testGetStatusForCallableReturningFalse() {
    $definition = new BooleanValue(function () {
      return FALSE;
    });
    $this->assertFalse($definition->getStatus());
  }

  /**
   * @covers \Papaya\Cache\Identifier\Definition\BooleanValue
   */
  public function testGetSources() {
    $definition = new BooleanValue(TRUE);
    $this->assertEquals(
      \Papaya\Cache\Identifier\Definition::SOURCE_VARIABLES,
      $definition->getSources()
    );
  }
}
