<?php
require_once(dirname(__FILE__).'/../../../../../bootstrap.php');

class PapayaContentPagePublicationStatusTest extends PapayaTestCase {

  /**
  * @covers PapayaContentPagePublicationStatus::load
  */
  public function testLoadReadingFromCache() {
    $cache = $this->getMock('PapayaCacheService');
    $cache
      ->expects($this->once())
      ->method('read')
      ->with('pages', 'status', 42, 0)
      ->will(
        $this->returnValue(
          serialize(
            array(
              'id' => 42,
              'session_mode' => PapayaSession::ACTIVATION_DYNAMIC
            )
          )
        )
      );

    $status = new PapayaContentPagePublicationStatus();
    $status->papaya($this->mockPapaya()->application());
    $status->cache($cache);

    $this->assertTrue($status->load(42));
    $this->assertEquals(
      array(
        'id' => 42,
        'session_mode' => PapayaSession::ACTIVATION_DYNAMIC
      ),
      $status->toArray()
    );
  }

  /**
  * @covers PapayaContentPagePublicationStatus::load
  */
  public function testLoadWritingCache() {
    $cache = $this->getMock('PapayaCacheService');
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
            'session_mode' => PapayaSession::ACTIVATION_DYNAMIC
          )
        ),
        0
      );

    $status = new PapayaContentPagePublicationStatus();
    $status->papaya($this->mockPapaya()->application());
    $status->cache($cache);
    $status->setDatabaseAccess(
      $this->getDatabaseAccessFixture(
        array(
          'topic_id' => 42,
          'topic_sessionmode' => PapayaSession::ACTIVATION_DYNAMIC
        )
      )
    );

    $this->assertTrue($status->load(42));
    $this->assertEquals(
      array(
        'id' => 42,
        'session_mode' => PapayaSession::ACTIVATION_DYNAMIC
      ),
      $status->toArray()
    );

  }

  /**
  * @covers PapayaContentPagePublicationStatus::cache
  */
  public function testCacheGetAfterSet() {
    $cache = $this->getMock('PapayaCacheService');
    $status = new PapayaContentPagePublicationStatus();
    $status->cache($cache);
    $this->assertSame($cache, $status->cache());
  }

  /**
  * @covers PapayaContentPagePublicationStatus::cache
  */
  public function testCacheGetImplicitCreate() {
    $status = new PapayaContentPagePublicationStatus();
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
    $this->assertInstanceOf('PapayaCacheService', $status->cache());
  }

  /****************
  * Fixtures
  ****************/

  public function getDatabaseAccessFixture($recordData) {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->atLeastOnce())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will(
        $this->onConsecutiveCalls(
          $recordData,
          FALSE
        )
      );
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('getSqlCondition', 'queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with($this->isType('array'))
      ->will($this->returnValue(">>CONDITION<<"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'))
      ->will($this->returnValue($databaseResult));
    return $databaseAccess;
  }
}