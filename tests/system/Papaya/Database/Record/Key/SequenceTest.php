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

require_once __DIR__.'/../../../../../bootstrap.php';

class PapayaDatabaseRecordKeySequenceTest extends PapayaTestCase {

  /**
  * @covers \Papaya\Database\Record\Key\Sequence::__construct
  */
  public function testConstructor() {
    $sequence = $this->getSequenceFixture();
    $key = new \Papaya\Database\Record\Key\Sequence($sequence);
    $this->assertAttributeSame(
      $sequence, '_sequence', $key
    );
    $this->assertEquals(
      array('id'), $key->getProperties()
    );
  }

  /**
  * @covers \Papaya\Database\Record\Key\Sequence::__construct
  */
  public function testConstructorWithPropertyName() {
    $key = new \Papaya\Database\Record\Key\Sequence($this->getSequenceFixture(), 'ident');
    $this->assertEquals(
      array('ident'), $key->getProperties()
    );
  }


  /**
  * @covers \Papaya\Database\Record\Key\Sequence::assign
  * @covers \Papaya\Database\Record\Key\Sequence::getFilter
  */
  public function testAssignAndGetFilter() {
    $key = new \Papaya\Database\Record\Key\Sequence($this->getSequenceFixture());
    $this->assertTrue($key->assign(array('id' => 'PROVIDED_SEQUENCE_ID')));
    $this->assertEquals(
      array('id' => 'PROVIDED_SEQUENCE_ID'), $key->getFilter()
    );
  }

  /**
  * @covers \Papaya\Database\Record\Key\Sequence::assign
  * @covers \Papaya\Database\Record\Key\Sequence::getFilter
  */
  public function testAssignWithInvalidData() {
    $key = new \Papaya\Database\Record\Key\Sequence($this->getSequenceFixture());
    $this->assertFalse($key->assign(array('other' => 'PROVIDED_SEQUENCE_ID')));
    $this->assertEquals(
      array('id' => NULL), $key->getFilter()
    );
  }

  /**
  * @covers \Papaya\Database\Record\Key\Sequence::getFilter
  */
  public function testGetFilterWithoutAssign() {
    $key = new \Papaya\Database\Record\Key\Sequence($this->getSequenceFixture());
    $this->assertEquals(
      array('id' => NULL), $key->getFilter()
    );
  }

  /**
  * @covers \Papaya\Database\Record\Key\Sequence::getFilter
  */
  public function testGetFilterWithoutAssignCreatingId() {
    $sequence =$this->getSequenceFixture();
    $sequence
      ->expects($this->once())
      ->method('next')
      ->will($this->returnValue('CREATED_SEQUENCE_ID'));
    $key = new \Papaya\Database\Record\Key\Sequence($sequence);
    $this->assertEquals(
      array('id' => 'CREATED_SEQUENCE_ID'),
      $key->getFilter(\Papaya\Database\Interfaces\Key::ACTION_CREATE)
    );
  }

  /**
  * @covers \Papaya\Database\Record\Key\Sequence::exists
  */
  public function testExistsExpectingTrue() {
    $key = new \Papaya\Database\Record\Key\Sequence($this->getSequenceFixture());
    $key->assign(array('id' => 'PROVIDED_SEQUENCE_ID'));
    $this->assertTrue($key->exists());
  }

  /**
  * @covers \Papaya\Database\Record\Key\Sequence::exists
  */
  public function testExistsExpectingFalse() {
    $key = new \Papaya\Database\Record\Key\Sequence($this->getSequenceFixture());
    $this->assertFalse($key->exists());
  }

  /**
  * @covers \Papaya\Database\Record\Key\Sequence::getQualities
  */
  public function testGetQualities() {
    $key = new \Papaya\Database\Record\Key\Sequence($this->getSequenceFixture());
    $this->assertEquals(\Papaya\Database\Interfaces\Key::CLIENT_GENERATED, $key->getQualities());
  }

  /**
  * @covers \Papaya\Database\Record\Key\Sequence::__toString
  */
  public function testMagicToString() {
    $key = new \Papaya\Database\Record\Key\Sequence($this->getSequenceFixture());
    $key->assign(array('id' => 'PROVIDED_SEQUENCE_ID'));
    $this->assertSame('PROVIDED_SEQUENCE_ID', (string)$key);
  }

  /**
  * @covers \Papaya\Database\Record\Key\Sequence::clear
  */
  public function testClear() {
    $key = new \Papaya\Database\Record\Key\Sequence($this->getSequenceFixture());
    $key->assign(array('id' => 42));
    $key->clear();
    $this->assertSame('', (string)$key);
  }

  /**
  * @covers \Papaya\Database\Record\Key\Sequence::getProperties
  */
  public function testGetProperties() {
    $key = new \Papaya\Database\Record\Key\Sequence($this->getSequenceFixture());
    $this->assertEquals(array('id'), $key->getProperties());
  }

  /**
   * @return \PHPUnit_Framework_MockObject_MockObject|\Papaya\Database\Sequence
   */
  private function getSequenceFixture() {
    $sequence = $this
      ->getMockBuilder(\Papaya\Database\Sequence::class)
      ->disableOriginalConstructor()
      ->getMock();
    return $sequence;
  }
}
