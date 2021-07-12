<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2019 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

namespace Papaya\Database\Connection {

  use Papaya\Database\Connection as DatabaseConnection;
  use Papaya\Database\Result as DatabaseResult;
  use Papaya\Database\Statement as DatabaseStatement;
  use Papaya\Message\Context as MessageContext;
  use Papaya\TestFramework\TestCase;

  require_once __DIR__.'/../../../../bootstrap.php';

  /**
   * @covers \Papaya\Database\Connection\AbstractResult
   */
  class AbstractResultTest extends TestCase {

    public function testConstructor() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseConnection $connection */
      $connection = $this->createMock(DatabaseConnection::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseStatement $statement */
      $statement = $this->createMock(DatabaseStatement::class);

      $result = new AbstractResult_TestProxy($connection, $statement);
      $this->assertSame($connection, $result->getConnection());
      $this->assertSame($statement, $result->getStatement());
    }

    public function testGetIterator() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseConnection $connection */
      $connection = $this->createMock(DatabaseConnection::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseStatement $statement */
      $statement = $this->createMock(DatabaseStatement::class);

      $result = new AbstractResult_TestProxy($connection, $statement);
      $iterator = $result->getIterator();

      $this->assertInstanceOf(DatabaseResult\Iterator::class, $iterator);
    }

    public function testFetchAssoc() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseConnection $connection */
      $connection = $this->createMock(DatabaseConnection::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseStatement $statement */
      $statement = $this->createMock(DatabaseStatement::class);

      $result = new AbstractResult_TestProxy($connection, $statement);
      $result->fetchAssoc();

      $this->assertSame(
        [
          ['fetchRow', AbstractResult::FETCH_ASSOC]
        ],
        $result->calls
      );
    }

    public function testFetchFieldWithIndex() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseConnection $connection */
      $connection = $this->createMock(DatabaseConnection::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseStatement $statement */
      $statement = $this->createMock(DatabaseStatement::class);

      $result = new AbstractResult_TestProxy($connection, $statement);
      $result->record = ['one', 'two'];

      $this->assertSame('one', $result->fetchField());
      $this->assertSame('two', $result->fetchField(1));
      $this->assertSame(
        [
          ['fetchRow', AbstractResult::FETCH_ORDERED],
          ['fetchRow', AbstractResult::FETCH_ORDERED]
        ],
        $result->calls
      );
    }

    public function testFetchFieldWithName() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseConnection $connection */
      $connection = $this->createMock(DatabaseConnection::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseStatement $statement */
      $statement = $this->createMock(DatabaseStatement::class);

      $result = new AbstractResult_TestProxy($connection, $statement);
      $result->record = ['first' => 'one', 'second' => 'two'];

      $this->assertSame('one', $result->fetchField('first'));
      $this->assertSame('two', $result->fetchField('second'));
      $this->assertSame(
        [
          ['fetchRow', AbstractResult::FETCH_ASSOC],
          ['fetchRow', AbstractResult::FETCH_ASSOC]
        ],
        $result->calls
      );
    }

    public function testFetchFieldWithOtherTypeExpectingFalse() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseConnection $connection */
      $connection = $this->createMock(DatabaseConnection::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseStatement $statement */
      $statement = $this->createMock(DatabaseStatement::class);

      $result = new AbstractResult_TestProxy($connection, $statement);

      $this->assertFalse($result->fetchField(NULL));
      $this->assertSame([], $result->calls);
    }

    public function testSeekFirst() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseConnection $connection */
      $connection = $this->createMock(DatabaseConnection::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseStatement $statement */
      $statement = $this->createMock(DatabaseStatement::class);

      $result = new AbstractResult_TestProxy($connection, $statement);

      $this->assertTrue($result->seekFirst());
      $this->assertSame([['seek', 0]], $result->calls);
    }

    public function testSeekLast() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseConnection $connection */
      $connection = $this->createMock(DatabaseConnection::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseStatement $statement */
      $statement = $this->createMock(DatabaseStatement::class);

      $result = new AbstractResult_TestProxy($connection, $statement);

      $this->assertTrue($result->seekLast());
      $this->assertSame(
        [
          ['count'],
          ['seek', 23]
        ],
        $result->calls
      );
    }

    public function testSeekLastResultUnavailable() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseConnection $connection */
      $connection = $this->createMock(DatabaseConnection::class);
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseStatement $statement */
      $statement = $this->createMock(DatabaseStatement::class);

      $result = new AbstractResult_TestProxy($connection, $statement);
      $result->count = FALSE;

      $this->assertFalse($result->seekLast());
      $this->assertSame(
        [
          ['count']
        ],
        $result->calls
      );
    }

    public function testGetAbsoluteCountAfterSet() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseConnection $connection */
      $connection = $this->createMock(DatabaseConnection::class);

      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseStatement $statement */
      $statement = $this->createMock(DatabaseStatement::class);

      $result = new AbstractResult_TestProxy($connection, $statement);
      $result->setAbsoluteCount(123);

      $this->assertSame(123, $result->absCount());
    }

    public function testGetAbsoluteCount() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseResult $countResult */
      $countResult = $this->createMock(DatabaseResult::class);
      $countResult
        ->expects($this->once())
        ->method('fetchField')
        ->willReturn(42);

      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseConnection $connection */
      $connection = $this->createMock(DatabaseConnection::class);
      $connection
        ->expects($this->once())
        ->method('execute')
        ->withAnyParameters()
        ->willReturn($countResult);

      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseStatement $statement */
      $statement = $this->createMock(DatabaseStatement::class);

      $result = new AbstractResult_TestProxy($connection, $statement);

      $this->assertSame(42, $result->absCount());
    }

    public function testGetAbsoluteCountFailsAndReturnNull() {
      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseConnection $connection */
      $connection = $this->createMock(DatabaseConnection::class);
      $connection
        ->expects($this->once())
        ->method('execute')
        ->withAnyParameters()
        ->willReturn(FALSE);

      /** @var \PHPUnit_Framework_MockObject_MockObject|DatabaseStatement $statement */
      $statement = $this->createMock(DatabaseStatement::class);

      $result = new AbstractResult_TestProxy($connection, $statement);

      $this->assertNull($result->absCount());
    }
  }

  class AbstractResult_TestProxy extends AbstractResult {

    public $calls = [];
    public $record;
    public $count = 23;

    /**
     * Fetch row from result
     *
     * @param int $mode
     * @return array|NULL
     */
    public function fetchRow($mode = self::FETCH_ORDERED) {
      $this->calls[] = [__FUNCTION__, $mode];
      return $this->record;
    }

    /**
     * Seek internal pointer to the given row
     *
     * @param int $index
     * @return bool
     */
    public function seek($index) {
      $this->calls[] = [__FUNCTION__, $index];
      return TRUE;
    }

    /**
     * return count of records in compiled result with limit
     *
     * @return int
     */
    public function count() {
      $this->calls[] = [__FUNCTION__];
      return $this->count;
    }

    /**
     * Unset result data
     */
    public function free() {
      $this->calls[] = [__FUNCTION__];
    }

    /**
     * @return bool
     */
    public function isValid() {
      $this->calls[] = [__FUNCTION__];
      return TRUE;
    }

    /**
     * @return null|MessageContext\Data
     */
    public function getExplain() {
      $this->calls[] = [__FUNCTION__];
      return NULL;
    }
  }
}
