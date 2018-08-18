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

namespace Papaya\Database\Result;

require_once __DIR__.'/../../../../bootstrap.php';

class IteratorTest extends \Papaya\TestCase {

  /**
   * @covers \Papaya\Database\Result\Iterator::__construct
   */
  public function testConstructor() {
    $iterator = new Iterator(
      $databaseResult = $this->createMock(\Papaya\Database\Result::class)
    );
    $this->assertAttributeSame(
      $databaseResult, '_databaseResult', $iterator
    );
  }

  /**
   * @covers \Papaya\Database\Result\Iterator::__construct
   */
  public function testConstructorWithAllParameters() {
    $iterator = new Iterator(
      $this->createMock(\Papaya\Database\Result::class),
      \Papaya\Database\Result::FETCH_ORDERED
    );
    $this->assertAttributeSame(
      \Papaya\Database\Result::FETCH_ORDERED, '_fetchMode', $iterator
    );
  }

  /**
   * @covers \Papaya\Database\Result\Iterator::rewind
   * @covers \Papaya\Database\Result\Iterator::key
   * @covers \Papaya\Database\Result\Iterator::current
   * @covers \Papaya\Database\Result\Iterator::next
   * @covers \Papaya\Database\Result\Iterator::valid
   */
  public function testIterate() {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with(\Papaya\Database\Result::FETCH_ASSOC)
      ->will(
        $this->onConsecutiveCalls(
          array('id' => 21),
          array('id' => 42),
          FALSE
        )
      );
    $databaseResult
      ->expects($this->any())
      ->method('seek')
      ->with(0);
    $iterator = new Iterator($databaseResult);
    $this->assertEquals(
      array(
        0 => array('id' => 21),
        1 => array('id' => 42)
      ),
      iterator_to_array($iterator)
    );
  }

  /**
   * @covers \Papaya\Database\Result\Iterator::current
   */
  public function testIterateWithMapping() {
    $mapping = $this->createMock(\Papaya\Database\Interfaces\Mapping::class);
    $mapping
      ->expects($this->any())
      ->method('mapFieldsToProperties')
      ->with($this->isType('array'))
      ->will($this->returnCallback(array($this, 'callbackMapFieldsToProperties')));
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with(\Papaya\Database\Result::FETCH_ASSOC)
      ->will(
        $this->onConsecutiveCalls(
          array('id' => 21),
          FALSE
        )
      );
    $databaseResult
      ->expects($this->any())
      ->method('seek')
      ->with(0);
    $iterator = new Iterator($databaseResult);
    $iterator->setMapping($mapping);
    $this->assertEquals(
      array(
        0 => array('identifier' => 21)
      ),
      iterator_to_array($iterator)
    );
  }

  public function callbackMapFieldsToProperties($record) {
    return array('identifier' => $record['id']);
  }

  /**
   * @covers \Papaya\Database\Result\Iterator::rewind
   */
  public function testRewindAfterIteration() {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with(\Papaya\Database\Result::FETCH_ASSOC)
      ->will(
        $this->onConsecutiveCalls(
          array('id' => 21),
          array('id' => 42),
          FALSE,
          array('id' => 23),
          FALSE
        )
      );
    $databaseResult
      ->expects($this->any())
      ->method('seek')
      ->with(0);
    $iterator = new Iterator($databaseResult);
    iterator_to_array($iterator);
    $this->assertEquals(
      array(
        0 => array('id' => 23)
      ),
      iterator_to_array($iterator)
    );
  }

  /**
   * @covers \Papaya\Database\Result\Iterator::setMapping
   * @covers \Papaya\Database\Result\Iterator::getMapping
   */
  public function testSetMappingGetAfterSet() {
    $iterator = new Iterator($this->createMock(\Papaya\Database\Result::class));
    $iterator->setMapping($mapping = $this->createMock(\Papaya\Database\Interfaces\Mapping::class));
    $this->assertSame(
      $mapping, $iterator->getMapping()
    );
  }
}
