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

namespace Papaya\Utility;
require_once __DIR__.'/../../../bootstrap.php';

class RandomTest extends \PapayaTestCase {

  /**
   * @covers \Papaya\Utility\Random::rand
   */
  public function testRand() {
    $random = Random::rand();
    $this->assertGreaterThanOrEqual(0, $random);
  }

  /**
   * @covers \Papaya\Utility\Random::rand
   */
  public function testRandWithLimits() {
    $random = Random::rand(1, 1);
    $this->assertGreaterThanOrEqual(1, $random);
  }

  /**
   * @covers \Papaya\Utility\Random::getId
   */
  public function testGetId() {
    $idOne = Random::getId();
    $idTwo = Random::getId();
    $this->assertNotEquals($idOne, $idTwo);
  }
}
