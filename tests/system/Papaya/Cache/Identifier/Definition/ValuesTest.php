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

class ValuesTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\Cache\Identifier\Definition\Values
   */
  public function testGetStatus() {
    $definition = new Values('21', '42');
    $this->assertEquals(
      array(Values::class => array('21', '42')),
      $definition->getStatus()
    );
  }


  /**
   * @covers \Papaya\Cache\Identifier\Definition\Values
   */
  public function testGetStatusWithoutValuesExpectingTrue() {
    $definition = new Values();
    $this->assertTrue(
      $definition->getStatus()
    );
  }

  /**
   * @covers \Papaya\Cache\Identifier\Definition\Values
   */
  public function testGetSources() {
    $definition = new Values();
    $this->assertEquals(
      \Papaya\Cache\Identifier\Definition::SOURCE_VARIABLES,
      $definition->getSources()
    );
  }
}
