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

use Papaya\Cache\Identifier\Definition\Values;
use Papaya\Cache\Identifier\Definition;

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaCacheIdentifierDefinitionValuesTest extends \PapayaTestCase {

  /**
   * @covers Values
   */
  public function testGetStatus() {
    $definition = new Values('21', '42');
    $this->assertEquals(
      array(Values::class => array('21', '42')),
      $definition->getStatus()
    );
  }


  /**
   * @covers Values
   */
  public function testGetStatusWithoutValuesExpectingTrue() {
    $definition = new Values();
    $this->assertTrue(
      $definition->getStatus()
    );
  }

  /**
   * @covers Values
   */
  public function testGetSources() {
    $definition = new Values();
    $this->assertEquals(
      Definition::SOURCE_VARIABLES,
      $definition->getSources()
    );
  }
}
