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
use Papaya\Database\Record\Key\Sequence;

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaDatabaseRecordKeySequenceTest extends PapayaTestCase {

  /**
  * @covers Sequence::__construct
  */
  public function testConstructor() {
    $sequence = $this->getSequenceFixture();
    $key = new Sequence($sequence);
    $this->assertAttributeSame(
      $sequence, '_sequence', $key
    );
    $this->assertEquals(
      array('id'), $key->getProperties()
    );
  }

  /**
  * @covers Sequence::__construct
  */
  public function testConstructorWithPropertyName() {
    $key = new Sequence($this->getSequenceFixture(), 'ident');
    $this->assertEquals(
      array('ident'), $key->getProperties()
    );
  }


  /**
  * @covers Sequence::assign
  * @covers Sequence::getFilter
  */
  public function testAssignAndGetFilter() {
    $key = new Sequence($this->getSequenceFixture());
    $this->assertTrue($key->assign(array('id' => 'PROVIDED_SEQUENCE_ID')));
    $this->assertEquals(
      array('id' => 'PROVIDED_SEQUENCE_ID'), $key->getFilter()
    );
  }

  /**
  * @covers Sequence::assign
  * @covers Sequence::getFilter
  */
  public function testAssignWithInvalidData() {
    $key = new Sequence($this->getSequenceFixture());
    $this->assertFalse($key->assign(array('other' => 'PROVIDED_SEQUENCE_ID')));
    $this->assertEquals(
      array('id' => NULL), $key->getFilter()
    );
  }

  /**
  * @covers Sequence::getFilter
  */
  public function testGetFilterWithoutAssign() {
    $key = new Sequence($this->getSequenceFixture());
    $this->assertEquals(
      array('id' => NULL), $key->getFilter()
    );
  }

  /**
  * @covers Sequence::getFilter
  */
  public function testGetFilterWithoutAssignCreatingId() {
    $sequence =$this->getSequenceFixture();
    $sequence
      ->expects($this->once())
      ->method('next')
      ->will($this->returnValue('CREATED_SEQUENCE_ID'));
    $key = new Sequence($sequence);
    $this->assertEquals(
      array('id' => 'CREATED_SEQUENCE_ID'),
      $key->getFilter(Key::ACTION_CREATE)
    );
  }

  /**
  * @covers Sequence::exists
  */
  public function testExistsExpectingTrue() {
    $key = new Sequence($this->getSequenceFixture());
    $key->assign(array('id' => 'PROVIDED_SEQUENCE_ID'));
    $this->assertTrue($key->exists());
  }

  /**
  * @covers Sequence::exists
  */
  public function testExistsExpectingFalse() {
    $key = new Sequence($this->getSequenceFixture());
    $this->assertFalse($key->exists());
  }

  /**
  * @covers Sequence::getQualities
  */
  public function testGetQualities() {
    $key = new Sequence($this->getSequenceFixture());
    $this->assertEquals(Key::CLIENT_GENERATED, $key->getQualities());
  }

  /**
  * @covers Sequence::__toString
  */
  public function testMagicToString() {
    $key = new Sequence($this->getSequenceFixture());
    $key->assign(array('id' => 'PROVIDED_SEQUENCE_ID'));
    $this->assertSame('PROVIDED_SEQUENCE_ID', (string)$key);
  }

  /**
  * @covers Sequence::clear
  */
  public function testClear() {
    $key = new Sequence($this->getSequenceFixture());
    $key->assign(array('id' => 42));
    $key->clear();
    $this->assertSame('', (string)$key);
  }

  /**
  * @covers Sequence::getProperties
  */
  public function testGetProperties() {
    $key = new Sequence($this->getSequenceFixture());
    $this->assertEquals(array('id'), $key->getProperties());
  }

  /**
   * @return PHPUnit_Framework_MockObject_MockObject|PapayaDatabaseSequence
   */
  private function getSequenceFixture() {
    $sequence = $this
      ->getMockBuilder(PapayaDatabaseSequence::class)
      ->disableOriginalConstructor()
      ->getMock();
    return $sequence;
  }
}
