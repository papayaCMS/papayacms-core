<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaContentPagesTest extends PapayaTestCase {

  /**
  * @covers PapayaContentPages
  */
  public function testLoadWithTranslationNeeded() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'topic_id' => 42,
            'prev' => 21,
            'prev_path' => ';0;21;',
            'topic_protocol' => PapayaUtilServerProtocol::HTTP,
            'linktype_id' => 1,
            'topic_title' => 'sample'
          ),
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
      ->method('queryFmt')
      ->with(
        $this->logicalAnd($this->isType('string'), $this->stringContains('INNER JOIN')),
        array(
          PapayaContentTables::PAGES,
          PapayaContentTables::PAGE_TRANSLATIONS,
          1,
          PapayaContentTables::PAGE_PUBLICATIONS,
          PapayaContentTables::VIEWS,
          PapayaContentTables::VIEW_CONFIGURATIONS,
          23,
          PapayaContentTables::AUTHENTICATION_USERS
        )
      )
      ->will($this->returnValue($databaseResult));
    $pages = new PapayaContentPages(TRUE);
    $pages->setDatabaseAccess($databaseAccess);
    $this->assertTrue($pages->load(array('language_id' => 1, 'viewmode_id' => 23)));
    $this->assertEquals(
      array(
        42 => array(
          'id' => 42,
          'parent' => 21,
          'path' => array(0, 21),
          'title' => 'sample',
          'link_type_id' => 1,
          'scheme' => PapayaUtilServerProtocol::HTTP
        )
      ),
      $pages->toArray()
    );
  }

  /**
  * @covers PapayaContentPages
  */
  public function testLoadWithEmptyFilter() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'topic_id' => 42,
            'prev' => 21,
            'prev_path' => ';0;21;',
            'topic_protocol' => PapayaUtilServerProtocol::HTTP,
            'linktype_id' => 1,
            'topic_title' => NULL
          ),
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
      ->method('queryFmt')
      ->with(
        $this->logicalAnd($this->isType('string'), $this->stringContains('LEFT JOIN')),
        array(
          PapayaContentTables::PAGES,
          PapayaContentTables::PAGE_TRANSLATIONS,
          0,
          PapayaContentTables::PAGE_PUBLICATIONS,
          PapayaContentTables::VIEWS,
          PapayaContentTables::VIEW_CONFIGURATIONS,
          0,
          PapayaContentTables::AUTHENTICATION_USERS
        )
      )
      ->will($this->returnValue($databaseResult));
    $pages = new PapayaContentPages(FALSE);
    $pages->setDatabaseAccess($databaseAccess);
    $this->assertTrue($pages->load(array()));
    $this->assertEquals(
      array(
        42 => array(
          'id' => 42,
          'parent' => 21,
          'path' => array(0, 21),
          'title' => NULL,
          'link_type_id' => 1,
          'scheme' => PapayaUtilServerProtocol::HTTP
        )
      ),
      $pages->toArray()
    );
  }

  /**
  * @covers PapayaContentPages
  */
  public function testLoadWithId() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'topic_id' => 42,
            'prev' => 21,
            'prev_path' => ';0;21;',
            'topic_protocol' => PapayaUtilServerProtocol::HTTP,
            'linktype_id' => 1,
            'topic_title' => 'sample'
          ),
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
      ->with(array('t.topic_id' => 42))
      ->will($this->returnValue(" t.topic_id = '42'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->logicalAnd($this->isType('string'), $this->stringContains('LEFT JOIN')),
        array(
          PapayaContentTables::PAGES,
          PapayaContentTables::PAGE_TRANSLATIONS,
          1,
          PapayaContentTables::PAGE_PUBLICATIONS,
          PapayaContentTables::VIEWS,
          PapayaContentTables::VIEW_CONFIGURATIONS,
          0,
          PapayaContentTables::AUTHENTICATION_USERS
        )
      )
      ->will($this->returnValue($databaseResult));
    $pages = new PapayaContentPages();
    $pages->setDatabaseAccess($databaseAccess);
    $this->assertTrue($pages->load(array('id' => 42, 'language_id' => 1)));
    $this->assertEquals(
      array(
        42 => array(
          'id' => 42,
          'parent' => 21,
          'path' => array(0, 21),
          'title' => 'sample',
          'link_type_id' => 1,
          'scheme' => PapayaUtilServerProtocol::HTTP
        )
      ),
      $pages->toArray()
    );
  }

  /**
  * @covers PapayaContentPages
  */
  public function testLoadWithStatus() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'topic_id' => 42,
            'prev' => 21,
            'prev_path' => ';0;21;',
            'topic_protocol' => PapayaUtilServerProtocol::HTTP,
            'linktype_id' => 1,
            'topic_title' => 'sample'
          ),
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
      ->with(array('t.prev' => 42))
      ->will($this->returnValue(" t.topic_id = '42'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array(
          PapayaContentTables::PAGES,
          PapayaContentTables::PAGE_TRANSLATIONS,
          1,
          PapayaContentTables::PAGE_PUBLICATIONS,
          PapayaContentTables::VIEWS,
          PapayaContentTables::VIEW_CONFIGURATIONS,
          0,
          PapayaContentTables::AUTHENTICATION_USERS
        )
      )
      ->will($this->returnValue($databaseResult));
    $pages = new PapayaContentPages();
    $pages->setDatabaseAccess($databaseAccess);
    $this->assertTrue(
      $pages->load(
        array('parent' => 42, 'language_id' => 1, 'status' => 'modified')
      )
    );
    $this->assertEquals(
      array(
        42 => array(
          'id' => 42,
          'parent' => 21,
          'path' => array(0, 21),
          'title' => 'sample',
          'link_type_id' => 1,
          'scheme' => PapayaUtilServerProtocol::HTTP
        )
      ),
      $pages->toArray()
    );
  }

  /**
  * @covers PapayaContentPages
  */
  public function testLoadWithParentId() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'topic_id' => 42,
            'prev' => 21,
            'prev_path' => ';0;21;',
            'topic_protocol' => PapayaUtilServerProtocol::HTTP,
            'linktype_id' => 1,
            'topic_title' => 'sample'
          ),
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
      ->with(array('t.prev' => 42))
      ->will($this->returnValue(" t.topic_id = '42'"));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array(
          PapayaContentTables::PAGES,
          PapayaContentTables::PAGE_TRANSLATIONS,
          1,
          PapayaContentTables::PAGE_PUBLICATIONS,
          PapayaContentTables::VIEWS,
          PapayaContentTables::VIEW_CONFIGURATIONS,
          0,
          PapayaContentTables::AUTHENTICATION_USERS
        )
      )
      ->will($this->returnValue($databaseResult));
    $pages = new PapayaContentPages();
    $pages->setDatabaseAccess($databaseAccess);
    $this->assertTrue($pages->load(array('parent' => 42, 'language_id' => 1)));
    $this->assertEquals(
      array(
        42 => array(
          'id' => 42,
          'parent' => 21,
          'path' => array(0, 21),
          'title' => 'sample',
          'link_type_id' => 1,
          'scheme' => PapayaUtilServerProtocol::HTTP
        )
      ),
      $pages->toArray()
    );
  }

  /**
  * @covers PapayaContentPages
  */
  public function testMappingImplicitCreateAttachesCallback() {
    $pages = new PapayaContentPages();
    $this->assertTrue(isset($pages->mapping()->callbacks()->onMapValue));
  }

  /**
  * @covers PapayaContentPages
  */
  public function testMapValueReturnsValueByDefault() {
    $pages = new PapayaContentPages();
    $this->assertEquals(
      'success',
      $pages->mapValue(
        new stdClass,
        PapayaDatabaseRecordMapping::FIELD_TO_PROPERTY,
        'id',
        'topic_id',
        'success'
      )
    );
  }

  /**
  * @covers PapayaContentPages
  */
  public function testMapValueDecodesPath() {
    $pages = new PapayaContentPages();
    $this->assertEquals(
      array(21, 42),
      $pages->mapValue(
        new stdClass,
        PapayaDatabaseRecordMapping::FIELD_TO_PROPERTY,
        'path',
        'prev_path',
        ';21;42;'
      )
    );
  }

  /**
  * @covers PapayaContentPages
  */
  public function testMapValueEncodesPath() {
    $pages = new PapayaContentPages();
    $this->assertEquals(
      ';21;42;',
      $pages->mapValue(
        new stdClass,
        PapayaDatabaseRecordMapping::PROPERTY_TO_FIELD,
        'path',
        'prev_path',
        array(21, 42)
      )
    );
  }

  /**
  * @covers PapayaContentPages
  */
  public function testIsPublicExpectingFalse() {
    $pages = new PapayaContentPages();
    $this->assertFalse($pages->isPublic());
  }
}
