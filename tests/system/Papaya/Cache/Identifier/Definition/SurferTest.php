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

use Papaya\Cache\Identifier\Definition\Surfer;
use Papaya\Cache\Identifier\Definition;

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaCacheIdentifierDefinitionSurferTest extends \PapayaTestCase {

  /**
   * @covers Surfer
   */
  public function testGetStatus() {
    $surfer = new \stdClass();
    $surfer->isValid = TRUE;
    $surfer->id = '012345678901234567890123456789ab';
    $definition = new Surfer();
    $definition->papaya(
      $this->mockPapaya()->application(
        array(
          'surfer' => $surfer
        )
      )
    );
    $this->assertEquals(
      array(Surfer::class => '012345678901234567890123456789ab'),
      $definition->getStatus()
    );
  }

  /**
   * @covers Surfer
   */
  public function testGetStatusForPreviewExpectingFalse() {
    $surfer = new \stdClass();
    $surfer->isValid = FALSE;
    $definition = new Surfer();
    $definition->papaya(
      $this->mockPapaya()->application(
        array(
          'surfer' => $surfer
        )
      )
    );
    $this->assertTrue($definition->getStatus());
  }

  /**
   * @covers Surfer
   */
  public function testGetSources() {
    $definition = new Surfer();
    $this->assertEquals(
      Definition::SOURCE_REQUEST,
      $definition->getSources()
    );
  }
}
