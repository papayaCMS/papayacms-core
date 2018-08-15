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

class CallbackTest extends \PapayaTestCase {

  /**
   * @covers Callback
   */
  public function testGetStatus() {
    $definition = new Callback(function () {
      return 'success';
    });
    $this->assertEquals(
      array(
        Callback::class => 'success'
      ),
      $definition->getStatus()
    );
  }

  /**
   * @covers Callback
   */
  public function testGetStatusExpectingFalse() {
    $definition = new Callback(function () {
      return FALSE;
    });
    $this->assertFalse($definition->getStatus());
  }

  /**
   * @covers Callback
   */
  public function testGetSources() {
    $definition = new Callback(function () {
      return FALSE;
    });
    $this->assertEquals(
      \Papaya\Cache\Identifier\Definition::SOURCE_VARIABLES,
      $definition->getSources()
    );
  }
}
