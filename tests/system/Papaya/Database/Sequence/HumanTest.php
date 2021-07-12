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

namespace Papaya\Database\Sequence;

require_once __DIR__.'/../../../../bootstrap.php';

class HumanTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\Database\Sequence\Human::__construct
   */
  public function testConstructor() {
    $sequence = new Human('table', 'field');
    $this->assertEquals(
      10, $sequence->getLength()
    );
  }

  /**
   * @covers \Papaya\Database\Sequence\Human::__construct
   */
  public function testConstructorWithByteLength() {
    $sequence = new Human('table', 'field', 42);
    $this->assertEquals(
      42, $sequence->getLength()
    );
  }

  /**
   * @covers \Papaya\Database\Sequence\Human::create
   * @covers \Papaya\Database\Sequence\Human::getRandomCharacters
   */
  public function testCreate5Bytes() {
    $sequence = new Human('table', 'field', 5);
    $this->assertRegExp(
      '(^[a-z2-7]{5}$)D', $sequence->create()
    );
  }

  /**
   * @covers \Papaya\Database\Sequence\Human::create
   */
  public function testCreate7Bytes() {
    $sequence = new Human('table', 'field');
    $this->assertRegExp(
      '(^[a-z2-7]{10}$)', $sequence->create()
    );
  }

  /**
   * @covers \Papaya\Database\Sequence\Human::create
   */
  public function testCreateIsRandom() {
    $sequence = new Human('table', 'field');
    $idOne = $sequence->create();
    $idTwo = $sequence->create();
    $this->assertNotEquals(
      $idOne, $idTwo
    );
  }
}
