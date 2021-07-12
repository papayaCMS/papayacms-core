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
 * @covers \Papaya\Message\Context\Runtime
 */
class RuntimeTest extends \Papaya\TestFramework\TestCase {

  public function testAsStringInGlobalMode() {
    $context = new Runtime();
    Runtime::setStartTime(23);
    $context->setTimeValues(42, 77);
    $this->assertEquals(
      'Time: 54s 0ms (+35s 0ms)',
      $context->asString()
    );
  }

  public function testAsStringInSingleMode() {
    $context = new Runtime(42, 77);
    $this->assertEquals(
      'Time needed: 35s 0ms',
      $context->asString()
    );
  }

  /*************************************
   * Data Provider
   *************************************/

  public static function setTimeValuesDataProvider() {
    return array(
      'integers' => array(19, 42, 23, 42),
      'strings' => array(19.5, 42.7, '0.2 23', '0.7 42'),
      'floats' => array(19.5, 42.7, 23.2, 42.7)
    );
  }
}
