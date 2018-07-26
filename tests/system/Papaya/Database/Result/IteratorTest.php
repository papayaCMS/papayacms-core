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

use Papaya\Database\Interfaces\Mapping;
use Papaya\Database\Result\Iterator;

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaDatabaseResultIteratorTest extends PapayaTestCase {

  /**
  * @covers Iterator::__construct
  */
  public function testConstructor() {
    $iterator = new Iterator(
      $databaseResult = $this->createMock(PapayaDatabaseResult::class)
    );
    $this->assertAttributeSame(
      $databaseResult, '_databaseResult', $iterator
    );
  }

  /**
  * @covers Iterator::__construct
  */
  public function testConstructorWithAllParameters() {
    $iterator = new Iterator(
      $this->createMock(PapayaDatabaseResult::class),
      \PapayaDatabaseResult::FETCH_ORDERED
    );
    $this->assertAttributeSame(
      \PapayaDatabaseResult::FETCH_ORDERED, '_fetchMode', $iterator
    );
  }

  /**
  * @covers Iterator::rewind
  * @covers Iterator::key
  * @covers Iterator::current
  * @covers Iterator::next
  * @covers Iterator::valid
  */
  public function testIterate() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
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
  * @covers Iterator::current
  */
  public function testIterateWithMapping() {
    $mapping = $this->createMock(Mapping::class);
    $mapping
      ->expects($this->any())
      ->method('mapFieldsToProperties')
      ->with($this->isType('array'))
      ->will($this->returnCallback(array($this, 'callbackMapFieldsToProperties')));
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
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
  * @covers Iterator::rewind
  */
  public function testRewindAfterIteration() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
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
  * @covers Iterator::setMapping
  * @covers Iterator::getMapping
  */
  public function testSetMappingGetAfterSet() {
    $iterator = new Iterator($this->createMock(PapayaDatabaseResult::class));
    $iterator->setMapping($mapping = $this->createMock(Mapping::class));
    $this->assertSame(
      $mapping, $iterator->getMapping()
    );
  }
}
