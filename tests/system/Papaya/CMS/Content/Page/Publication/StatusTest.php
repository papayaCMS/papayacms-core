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

namespace Papaya\CMS\Content\Page\Publication;

require_once __DIR__.'/../../../../../../bootstrap.php';

class StatusTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\CMS\Content\Page\Publication\Status::load
   */
  public function testLoadReadingFromCache() {
    $cache = $this->createMock(\Papaya\Cache\Service::class);
    $cache
      ->expects($this->once())
      ->method('read')
      ->with('pages', 'status', 42, 0)
      ->will(
        $this->returnValue(
          serialize(
            array(
              'id' => 42,
              'session_mode' => \Papaya\Session::ACTIVATION_DYNAMIC
            )
          )
        )
      );

    $status = new Status();
    $status->papaya($this->mockPapaya()->application());
    $status->cache($cache);

    $this->assertTrue($status->load(42));
    $this->assertEquals(
      array(
        'id' => 42,
        'session_mode' => \Papaya\Session::ACTIVATION_DYNAMIC
      ),
      $status->toArray()
    );
  }

  /**
   * @covers \Papaya\CMS\Content\Page\Publication\Status::load
   */
  public function testLoadWritingCache() {
    $cache = $this->createMock(\Papaya\Cache\Service::class);
    $cache
      ->expects($this->once())
      ->method('read')
      ->withAnyParameters()
      ->will($this->returnValue(NULL));
    $cache
      ->expects($this->once())
      ->method('write')
      ->with(
        'pages',
        'status',
        42,
        serialize(
          array(
            'id' => 42,
            'session_mode' => \Papaya\Session::ACTIVATION_DYNAMIC
          )
        ),
        0
      );

    $status = new Status();
    $status->papaya($this->mockPapaya()->application());
    $status->cache($cache);
    $status->setDatabaseAccess(
      $this->getDatabaseAccessFixture(
        array(
          'topic_id' => 42,
          'topic_sessionmode' => \Papaya\Session::ACTIVATION_DYNAMIC
        )
      )
    );

    $this->assertTrue($status->load(42));
    $this->assertEquals(
      array(
        'id' => 42,
        'session_mode' => \Papaya\Session::ACTIVATION_DYNAMIC
      ),
      $status->toArray()
    );

  }

  /**
   * @covers \Papaya\CMS\Content\Page\Publication\Status::cache
   */
  public function testCacheGetAfterSet() {
    $cache = $this->createMock(\Papaya\Cache\Service::class);
    $status = new Status();
    $status->cache($cache);
    $this->assertSame($cache, $status->cache());
  }

  /**
   * @covers \Papaya\CMS\Content\Page\Publication\Status::cache
   */
  public function testCacheGetImplicitCreate() {
    $status = new Status();
    $status->papaya(
      $this->mockPapaya()->application(
        array(
          'options' => $this->mockPapaya()->options(
            array(
              'PAPAYA_CACHE_DATA' => TRUE
            )
          )
        )
      )
    );
    $this->assertInstanceOf(\Papaya\Cache\Service::class, $status->cache());
  }

  /****************
   * Fixtures
   ****************/

  /**
   * @param array $recordData
   * @return \Papaya\Database\Access|\PHPUnit_Framework_MockObject_MockObject
   */
  public function getDatabaseAccessFixture(array $recordData) {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->atLeastOnce())
      ->method('fetchRow')
      ->with(\Papaya\Database\Result::FETCH_ASSOC)
      ->will(
        $this->onConsecutiveCalls(
          $recordData,
          FALSE
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with($this->isType('array'))
      ->will($this->returnValue('>>CONDITION<<'));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'))
      ->will($this->returnValue($databaseResult));
    return $databaseAccess;
  }
}
