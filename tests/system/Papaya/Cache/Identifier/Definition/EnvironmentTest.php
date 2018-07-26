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

use Papaya\Cache\Identifier\Definition\Environment;
use Papaya\Cache\Identifier\Definition;

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaCacheIdentifierDefinitionEnvironmentTest extends \PapayaTestCase {

  /**
   * @covers Environment
   */
  public function testGetStatus() {
    $_SERVER['TEST_VARIABLE'] = 'success';
    $definition = new Environment('TEST_VARIABLE');
    $this->assertEquals(
      array(Environment::class => array('TEST_VARIABLE' => 'success')),
      $definition->getStatus()
    );
    unset($_SERVER['TEST_VARIABLE']);
  }

  /**
   * @covers Environment
   */
  public function testGetStatusWithUnknownVariableExpectingTrue() {
    $definition = new Environment('UNKNOWN_TEST_VARIABLE');
    $this->assertTrue(
      $definition->getStatus()
    );
  }

  /**
   * @covers Environment
   */
  public function testGetSources() {
    $definition = new Environment('X');
    $this->assertEquals(
      Definition::SOURCE_REQUEST,
      $definition->getSources()
    );
  }

}
