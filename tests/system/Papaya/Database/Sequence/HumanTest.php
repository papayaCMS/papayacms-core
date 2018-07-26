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

use Papaya\Database\Sequence\Human;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaDatabaseSequenceHumanTest extends \PapayaTestCase {

  /**
  * @covers Human::__construct
  */
  public function testConstructor() {
    $sequence = new Human('table', 'field');
    $this->assertAttributeEquals(
      10, '_length', $sequence
    );
  }

  /**
  * @covers Human::__construct
  */
  public function testConstructorWithByteLength() {
    $sequence = new Human('table', 'field', 42);
    $this->assertAttributeEquals(
      42, '_length', $sequence
    );
  }

  /**
  * @covers Human::create
  * @covers Human::getRandomCharacters
  */
  public function testCreate5Bytes() {
    $sequence = new Human('table', 'field', 5);
    $this->assertRegExp(
      '(^[a-z2-7]{5}$)D', $sequence->create()
    );
  }

  /**
  * @covers Human::create
  */
  public function testCreate7Bytes() {
    $sequence = new Human('table', 'field');
    $this->assertRegExp(
      '(^[a-z2-7]{10}$)', $sequence->create()
    );
  }

  /**
  * @covers Human::create
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
