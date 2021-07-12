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

namespace Papaya\Database {

  require_once __DIR__.'/../../../bootstrap.php';

  class SequenceTest extends \Papaya\TestFramework\TestCase {

    /**
     * @covers \Papaya\Database\Sequence::__construct
     */
    public function testConstructor() {
      $sequence = new Sequence_TestProxy('table', 'field');
      $this->assertEquals(
        'table', $sequence->getTable()
      );
      $this->assertEquals(
        'field', $sequence->getField()
      );
    }

    /**
     * @covers \Papaya\Database\Sequence::__construct
     */
    public function testConstructorWithEmptyTableExpectingException() {
      $this->expectException(\InvalidArgumentException::class);
      new Sequence_TestProxy('', 'field');
    }

    /**
     * @covers \Papaya\Database\Sequence::__construct
     */
    public function testConstructorWithEmptyFieldExpectingException() {
      $this->expectException(\InvalidArgumentException::class);
      new Sequence_TestProxy('table', '');
    }

    /**
     * @covers \Papaya\Database\Sequence::next
     * @covers \Papaya\Database\Sequence::createIdentifiers
     * @covers \Papaya\Database\Sequence::checkIdentifiers
     */
    public function testNext() {
      $sequence = new Sequence_TestProxy('table', 'field');
      $databaseResult = $this->createMock(Result::class);
      $databaseResult
        ->expects($this->exactly(3))
        ->method('fetchRow')
        ->will($this->onConsecutiveCalls(array(1), array(2), NULL));
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('getSqlCondition')
        ->with('field', array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10))
        ->will($this->returnValue("'1', '2', '3', '4', '5', '6', '7', '8', '9', '10'"));
      $databaseAccess
        ->expects($this->once())
        ->method('queryFmt')
        ->with($this->isType('string'), array('field', 'table'))
        ->will($this->returnValue($databaseResult));
      $sequence->setDatabaseAccess($databaseAccess);
      $this->assertEquals(
        3, $sequence->next()
      );
    }

    /**
     * @covers \Papaya\Database\Sequence::next
     * @covers \Papaya\Database\Sequence::createIdentifiers
     * @covers \Papaya\Database\Sequence::checkIdentifiers
     */
    public function testNextAllInDatabaseFirstTime() {
      $sequence = new Sequence_TestProxy('table', 'field');
      $databaseResultOne = $this->createMock(Result::class);
      $databaseResultOne
        ->expects($this->any())
        ->method('fetchRow')
        ->will(
          $this->onConsecutiveCalls(
            array(1),
            array(2),
            array(3),
            array(4),
            array(5),
            array(6),
            array(7),
            array(8),
            array(9),
            array(10),
            FALSE
          )
        );
      $databaseResultTwo = $this->createMock(Result::class);
      $databaseResultTwo
        ->expects($this->any())
        ->method('fetchRow')
        ->will(
          $this->onConsecutiveCalls(
            array(11),
            array(12),
            array(13),
            FALSE
          )
        );
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->any())
        ->method('getSqlCondition')
        ->with('field', $this->isType('array'))
        ->will(
          $this->onConsecutiveCalls(
            $this->returnValue("'1', '2', '3', '4', '5', '6', '7', '8', '9', '10'"),
            $this->returnValue("'11', '12', '13', '14', '15', '16', '17', '18', '19', '20'")
          )
        );
      $databaseAccess
        ->expects($this->any())
        ->method('queryFmt')
        ->with($this->isType('string'), array('field', 'table'))
        ->will(
          $this->onConsecutiveCalls(
            $this->returnValue($databaseResultOne),
            $this->returnValue($databaseResultTwo)
          )
        );
      $sequence->setDatabaseAccess($databaseAccess);
      $this->assertEquals(
        14, $sequence->next()
      );
    }

    /**
     * @covers \Papaya\Database\Sequence::next
     * @covers \Papaya\Database\Sequence::checkIdentifiers
     */
    public function testNextDatabaseQueryFailed() {
      $sequence = new Sequence_TestProxy('table', 'field');
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('getSqlCondition')
        ->with('field', array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10))
        ->will($this->returnValue("'1', '2', '3', '4', '5', '6', '7', '8', '9', '10'"));
      $databaseAccess
        ->expects($this->once())
        ->method('queryFmt')
        ->with($this->isType('string'), array('field', 'table'))
        ->will($this->returnValue(FALSE));
      $sequence->setDatabaseAccess($databaseAccess);
      $this->assertFalse(
        $sequence->next()
      );
    }

    /**
     * @covers \Papaya\Database\Sequence::next
     * @covers \Papaya\Database\Sequence::checkIdentifiers
     */
    public function testNextBrokenCreateMethod() {
      $sequence = new \Papaya\Database\Sequence_TestProxyBroken('table', 'field');
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('getSqlCondition')
        ->with('field', array())
        ->will($this->returnValue(''));
      $sequence->setDatabaseAccess($databaseAccess);

      $this->expectException(\InvalidArgumentException::class);
      $sequence->next();
    }
  }

  class Sequence_TestProxy extends Sequence {

    public $idCounter = 1;

    public function create() {
      return $this->idCounter++;
    }

    public function checkIdentifiers(array $identifiers) {
      return parent::checkIdentifiers($identifiers);
    }

    public function createIdentifiers($count) {
      return parent::createIdentifiers($count);
    }

    public function getTable(): string {
      return $this->_table;
    }

    public function getField(): string {
      return $this->_field;
    }
  }

  class Sequence_TestProxyBroken extends Sequence_TestProxy {

    public function create() {
      return NULL;
    }
  }
}
