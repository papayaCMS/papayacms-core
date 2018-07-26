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

use Papaya\Database\Interfaces\Key;
use Papaya\Database\Record\Key\Autoincrement;

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaDatabaseRecordKeyAutoincrementTest extends PapayaTestCase {

  /**
  * @covers Autoincrement::__construct
  */
  public function testConstructor() {
    $key = new Autoincrement();
    $this->assertEquals(
      array('id'), $key->getProperties()
    );
  }

  /**
  * @covers Autoincrement::__construct
  * @covers Autoincrement::getProperties
  */
  public function testConstructorWithPropertyParameter() {
    $key = new Autoincrement('other');
    $this->assertEquals(
      array('other'), $key->getProperties()
    );
  }

  /**
  * Papaya\Database\Record\Key\PapayaDatabaseRecordKeyAutoincrement::assign
  * Papaya\Database\Record\Key\PapayaDatabaseRecordKeyAutoincrement::getFilter
  */
  public function testAssignAndGetFilter() {
    $key = new Autoincrement();
    $this->assertTrue($key->assign(array('id' => 42)));
    $this->assertEquals(
      array('id' => 42), $key->getFilter()
    );
  }

  /**
  * @covers Autoincrement::assign
  * @covers Autoincrement::getFilter
  */
  public function testAssignWithInvalidData() {
    $key = new Autoincrement();
    $this->assertFalse($key->assign(array('other' => 42)));
    $this->assertEquals(
      array('id' => NULL), $key->getFilter()
    );
  }

  /**
  * @covers Autoincrement::getFilter
  */
  public function testGetFilterWithoutAssign() {
    $key = new Autoincrement();
    $this->assertEquals(
      array('id' => NULL), $key->getFilter()
    );
  }

  /**
  * @covers Autoincrement::exists
  */
  public function testExistsExpectingTrue() {
    $key = new Autoincrement();
    $key->assign(array('id' => 42));
    $this->assertTrue($key->exists());
  }

  /**
  * @covers Autoincrement::exists
  */
  public function testExistsExpectingFalse() {
    $key = new Autoincrement();
    $this->assertFalse($key->exists());
  }

  /**
  * @covers Autoincrement::getQualities
  */
  public function testGetQualities() {
    $key = new Autoincrement();
    $this->assertEquals(Key::DATABASE_PROVIDED, $key->getQualities());
  }

  /**
  * @covers Autoincrement::__toString
  */
  public function testMagicToString() {
    $key = new Autoincrement();
    $key->assign(array('id' => 42));
    $this->assertSame('42', (string)$key);
  }

  /**
  * @covers Autoincrement::clear
  */
  public function testClear() {
    $key = new Autoincrement();
    $key->assign(array('id' => 42));
    $key->clear();
    $this->assertSame('', (string)$key);
  }
}
