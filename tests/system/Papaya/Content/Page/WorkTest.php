<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentPageWorkTest extends PapayaTestCase {

  /**
  * @covers PapayaContentPageWork::save
  */
  public function testSaveCreateNew() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->will($this->returnCallback(array($this, 'checkInsertData')));
    $page = new PapayaContentPageWork();
    $page->papaya($this->mockPapaya()->application());
    $page->setDatabaseAccess($databaseAccess);
    $page->assign(
      array(
        'parent_id' => 21,
        'parent_path' => array(0, 11),
        'owner' => '123456789012345678901234567890ab',
        'group' => -1,
        'permissions' => '777',
        'inherit_visitor_permissions' => PapayaContentOptions::INHERIT_PERMISSIONS_OWN,
        'visitor_permissions' => array(1, 2),
        'created' => 0,
        'modified' => 0,
        'position' => 99999,
        'inherit_boxes' => TRUE,
        'default_language' => 1,
        'link_type' => 1,
        'inherit_meta_information' => TRUE,
        'change_frequency' => 50,
        'priority' => 3,
        'scheme' => PapayaContentOptions::SCHEME_SYSTEM,
        'cache_mode' => PapayaContentOptions::CACHE_SYSTEM,
        'cache_time' => 0,
        'expires_mode' => PapayaContentOptions::CACHE_SYSTEM,
        'expires_time' => 0,
        'unpublished_translations' => 0
      )
    );
    $this->assertEquals(42, (string)$page->save());
  }

  public function checkInsertData($table, $idField, $data) {
    $this->assertEquals('table_topic', $table);
    $this->assertEquals('topic_id', $idField);
    $this->assertEquals(21, $data['prev']);
    $this->assertEquals(';0;11;', $data['prev_path']);
    $this->assertEquals('123456789012345678901234567890ab', $data['author_id']);
    $this->assertEquals(-1, $data['author_group']);
    $this->assertEquals('777', $data['author_perm']);
    $this->assertEquals(
      PapayaContentOptions::INHERIT_PERMISSIONS_OWN, $data['surfer_useparent']
    );
    $this->assertEquals('1;2', $data['surfer_permids']);
    $this->assertGreaterThan(0, $data['topic_created']);
    $this->assertGreaterThan(0, $data['topic_modified']);
    $this->assertEquals(99999, $data['topic_weight']);
    $this->assertEquals(1, (int)$data['box_useparent']);
    $this->assertEquals(1, $data['topic_mainlanguage']);
    $this->assertEquals(1, $data['linktype_id']);
    $this->assertEquals(1, (int)$data['meta_useparent']);
    $this->assertEquals(50, $data['topic_changefreq']);
    $this->assertEquals(3, $data['topic_priority']);
    $this->assertEquals(PapayaContentOptions::SCHEME_SYSTEM, $data['topic_protocol']);
    $this->assertEquals(PapayaContentOptions::CACHE_SYSTEM, $data['topic_cachemode']);
    $this->assertEquals(0, $data['topic_cachetime']);
    $this->assertEquals(PapayaContentOptions::CACHE_SYSTEM, $data['topic_expiresmode']);
    $this->assertEquals(0, $data['topic_expirestime']);
    $this->assertEquals(0, $data['topic_unpublished_languages']);
    return 42;
  }

  /**
  * @covers PapayaContentPage::save
  */
  public function testInsertExpectingFalse() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->withAnyParameters()
      ->will($this->returnValue(FALSE));
    $page = new PapayaContentPageWork();
    $page->setDatabaseAccess($databaseAccess);
    $this->assertFalse($page->save());
  }

  /**
  * @covers PapayaContentPageWork::save
  */
  public function testSaveUpdateExisting() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('updateRecord')
      ->will($this->returnCallback(array($this, 'checkUpdateData')));
    $page = new PapayaContentPageWork();
    $page->setDatabaseAccess($databaseAccess);
    $page->assign(
      array(
        'id' => 42,
        'parent_id' => 21,
        'parent_path' => array(0, 11),
        'owner' => '123456789012345678901234567890ab',
        'group' => -1,
        'permissions' => '777',
        'inherit_visitor_permissions' => PapayaContentOptions::INHERIT_PERMISSIONS_OWN,
        'visitor_permissions' => array(1, 2),
        'created' => 123,
        'modified' => 0,
        'position' => 99999,
        'inherit_boxes' => TRUE,
        'default_language' => 1,
        'link_type' => 1,
        'inherit_meta_information' => TRUE,
        'change_frequency' => 50,
        'priority' => 3,
        'scheme' => PapayaContentOptions::SCHEME_SYSTEM,
        'cache_mode' => PapayaContentOptions::CACHE_SYSTEM,
        'cache_time' => 0,
        'expires_mode' => PapayaContentOptions::CACHE_SYSTEM,
        'expires_time' => 0,
        'unpublished_translations' => 0
      )
    );
    $page->key()->assign(
      array(
        'id' => 42
      )
    );
    $this->assertTrue($page->save());
  }

  public function checkUpdateData($table, $data, $filter) {
    $this->assertEquals('table_topic', $table);
    $this->assertEquals(21, $data['prev']);
    $this->assertEquals(';0;11;', $data['prev_path']);
    $this->assertEquals('123456789012345678901234567890ab', $data['author_id']);
    $this->assertEquals(-1, $data['author_group']);
    $this->assertEquals('777', $data['author_perm']);
    $this->assertEquals(PapayaContentOptions::INHERIT_PERMISSIONS_OWN, $data['surfer_useparent']);
    $this->assertEquals('1;2', $data['surfer_permids']);
    $this->assertGreaterThan(0, $data['topic_modified']);
    $this->assertEquals(99999, $data['topic_weight']);
    $this->assertEquals(1, (int)$data['box_useparent']);
    $this->assertEquals(1, $data['topic_mainlanguage']);
    $this->assertEquals(1, $data['linktype_id']);
    $this->assertEquals(1, (int)$data['meta_useparent']);
    $this->assertEquals(50, $data['topic_changefreq']);
    $this->assertEquals(3, $data['topic_priority']);
    $this->assertEquals(PapayaContentOptions::SCHEME_SYSTEM, $data['topic_protocol']);
    $this->assertEquals(PapayaContentOptions::CACHE_SYSTEM, $data['topic_cachemode']);
    $this->assertEquals(0, $data['topic_cachetime']);
    $this->assertEquals(PapayaContentOptions::CACHE_SYSTEM, $data['topic_expiresmode']);
    $this->assertEquals(0, $data['topic_expirestime']);
    $this->assertEquals(array('topic_id' => 42), $filter);
    return TRUE;
  }

  /**
  * @covers PapayaContentPageWork::createChild
  */
  public function testCreateChild() {
    $parentPage = new PapayaContentPageWork();
    $parentPage->assign(
      array(
        'id' => 21,
        'parent_id' => 11,
        'parent_path' => array(0, 11),
        'owner' => '123456789012345678901234567890ab',
        'group' => -1,
        'permissions' => '777',
        'inherit_visitor_permissions' => PapayaContentOptions::INHERIT_PERMISSIONS_OWN,
        'visitor_permissions' => array(1, 2),
        'created' => 123,
        'modified' => 456,
        'position' => 0,
        'inherit_boxes' => TRUE,
        'default_language' => 1,
        'link_type' => 1,
        'is_deleted' => FALSE,
        'inherit_meta_information' => TRUE,
        'change_frequency' => 50,
        'priority' => 3,
        'scheme' => PapayaContentOptions::SCHEME_SYSTEM,
        'cache_mode' => PapayaContentOptions::CACHE_SYSTEM,
        'cache_time' => 0,
        'expires_mode' => PapayaContentOptions::CACHE_SYSTEM,
        'expires_time' => 0,
        'unpublished_translations' => 0
      )
    );
    $page = $parentPage->createChild();
    $this->assertAttributeEquals(
      array(
        'id' => NULL,
        'parent_id' => 21,
        'parent_path' => array(0, 11, 21),
        'owner' => '123456789012345678901234567890ab',
        'group' => -1,
        'permissions' => '777',
        'created' => NULL,
        'modified' => NULL,
        'inherit_visitor_permissions' => PapayaContentOptions::INHERIT_PERMISSIONS_PARENT,
        'visitor_permissions' => array(),
        'position' => 999999,
        'inherit_boxes' => TRUE,
        'default_language' => 1,
        'link_type' => 1,
        'is_deleted' => FALSE,
        'inherit_meta_information' => TRUE,
        'change_frequency' => 50,
        'priority' => 3,
        'scheme' => PapayaContentOptions::SCHEME_SYSTEM,
        'cache_mode' => PapayaContentOptions::CACHE_SYSTEM,
        'cache_time' => 0,
        'expires_mode' => PapayaContentOptions::CACHE_SYSTEM,
        'expires_time' => 0,
        'unpublished_translations' => 0
      ),
      '_values',
      $page
    );
  }

  /**
  * @covers PapayaContentPageWork::_createPublicationObject
  */
  public function testCreatePublicationObject() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $page = new PapayaContentPageWork_TestProxy();
    $page->setDatabaseAccess($databaseAccess);
    $publication = $page->_createPublicationObject();
    $this->assertInstanceOf(
      PapayaContentPagePublication::class, $publication
    );
    $this->assertSame(
      $databaseAccess, $publication->getDatabaseAccess()
    );
  }

  /**
  * @covers PapayaContentPageWork::publish
  */
  public function testPublishWithoutIdExpectingFalse() {
    $page = new PapayaContentPageWork_TestProxy();
    $this->assertFalse($page->publish());
  }

  /**
  * @covers PapayaContentPageWork::publish
  */
  public function testPublishFailed() {
    $page = $this->getContentPageFixture();
    $publication = $this->createMock(PapayaContentPagePublication::class);
    $publication
      ->expects($this->once())
      ->method('assign')
      ->with($this->equalTo($page));
    $publication
      ->expects($this->exactly(2))
      ->method('__set')
      ->with(
        $this->logicalOr($this->equalTo('publishedFrom'), $this->equalTo('publishedTo')),
        $this->equalTo(0)
      );
    $publication
      ->expects($this->once())
      ->method('save')
      ->will($this->returnValue(FALSE));
    $page->publicationObject = $publication;
    $this->assertFalse($page->publish());
  }

  /**
  * @covers PapayaContentPageWork::publish
  * @covers PapayaContentPageWork::_publishTranslations
  */
  public function testPublishWithoutLanguagesOrPeriod() {
    $page = $this->getContentPageFixture();
    $publication = $this->createMock(PapayaContentPagePublication::class);
    $publication
      ->expects($this->once())
      ->method('assign')
      ->with($this->isInstanceOf(PapayaContentPageWork::class));
    $publication
      ->expects($this->exactly(2))
      ->method('__set')
      ->with(
        $this->logicalOr($this->equalTo('publishedFrom'), $this->equalTo('publishedTo')),
        $this->equalTo(0)
      );
    $publication
      ->expects($this->once())
      ->method('save')
      ->will($this->returnValue(TRUE));
    $page->publicationObject = $publication;
    $this->assertTrue($page->publish());
  }

  /**
  * @covers PapayaContentPageWork::publish
  * @covers PapayaContentPageWork::_publishTranslations
  */
  public function testPublishWithLanguagesPeriod() {
    $page = $this->getContentPageFixture();
    $translations = $this->createMock(PapayaContentPageTranslations::class);
    $translations
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(3));
    $page->translations($translations);

    $publicTranslations = $this->createMock(PapayaContentPageTranslations::class);
    $publicTranslations
      ->expects($this->once())
      ->method('count')
      ->will($this->returnValue(2));
    $publication = $this->createMock(PapayaContentPagePublication::class);
    $publication
      ->expects($this->once())
      ->method('assign')
      ->with($this->isInstanceOf(PapayaContentPageWork::class));
    $publication
      ->expects($this->exactly(2))
      ->method('__set')
      ->with(
        $this->logicalOr($this->equalTo('publishedFrom'), $this->equalTo('publishedTo')),
        $this->greaterThan(0)
      );
    $publication
      ->expects($this->once())
      ->method('save')
      ->will($this->returnValue(TRUE));
    $publication
      ->expects($this->once())
      ->method('translations')
      ->will($this->returnValue($publicTranslations));
    $page->publicationObject = $publication;

    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with($this->equalTo('lng_id'), $this->equalTo(array(23, 42)))
      ->will($this->returnValue("lng_id IN ('23', '42')"));
    $databaseAccess
      ->expects($this->once())
      ->method('deleteRecord')
      ->with('table_topic_public_trans', array('topic_id' => 21, 'lng_id' => array(23, 42)))
      ->will($this->returnValue(0));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmtWrite')
      ->with($this->isType('string'), $this->isType('array'))
      ->will($this->returnValue(2));
    $databaseAccess
      ->expects($this->once())
      ->method('updateRecord')
      ->with('table_topic', array('topic_unpublished_languages' => 1), array('topic_id' => 21));
    $page->setDatabaseAccess($databaseAccess);

    $this->assertTrue($page->publish(array(23, 42), 123, 456));
  }

  /**
  * @covers PapayaContentPageWork::publish
  * @covers PapayaContentPageWork::_publishTranslations
  */
  public function testPublishTranslationDeletionFailedExpetingFalse() {
    $page = $this->getContentPageFixture();
    $publication = $this->createMock(PapayaContentPagePublication::class);
    $publication
      ->expects($this->once())
      ->method('assign')
      ->with($this->isInstanceOf(PapayaContentPageWork::class));
    $publication
      ->expects($this->exactly(2))
      ->method('__set')
      ->with(
        $this->logicalOr($this->equalTo('publishedFrom'), $this->equalTo('publishedTo')),
        $this->greaterThan(0)
      );
    $publication
      ->expects($this->once())
      ->method('save')
      ->will($this->returnValue(TRUE));
    $page->publicationObject = $publication;

    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('deleteRecord')
      ->with('table_topic_public_trans', array('topic_id' => 21, 'lng_id' => array(23, 42)))
      ->will($this->returnValue(FALSE));
    $page->setDatabaseAccess($databaseAccess);

    $this->assertFalse($page->publish(array(23, 42), 123, 456));
  }

  /**
  * @covers PapayaContentPageWork::publish
  * @covers PapayaContentPageWork::_publishTranslations
  */
  public function testPublishTranslationFailedExpetingFalse() {
    $page = $this->getContentPageFixture();

    $publication = $this->createMock(PapayaContentPagePublication::class);
    $publication
      ->expects($this->once())
      ->method('assign')
      ->with($this->isInstanceOf(PapayaContentPageWork::class));
    $publication
      ->expects($this->exactly(2))
      ->method('__set')
      ->with(
        $this->logicalOr($this->equalTo('publishedFrom'), $this->equalTo('publishedTo')),
        $this->greaterThan(0)
      );
    $publication
      ->expects($this->once())
      ->method('save')
      ->will($this->returnValue(TRUE));
    $page->publicationObject = $publication;

    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('getSqlCondition')
      ->with($this->equalTo('lng_id'), $this->equalTo(array(23, 42)))
      ->will($this->returnValue("lng_id IN ('23', '42')"));
    $databaseAccess
      ->expects($this->once())
      ->method('deleteRecord')
      ->with('table_topic_public_trans', array('topic_id' => 21, 'lng_id' => array(23, 42)))
      ->will($this->returnValue(0));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmtWrite')
      ->with($this->isType('string'), $this->isType('array'))
      ->will($this->returnValue(FALSE));
    $page->setDatabaseAccess($databaseAccess);

    $this->assertFalse($page->publish(array(23, 42), 123, 456));
  }

  public function getContentPageFixture() {
    $page = new PapayaContentPageWork_TestProxy();
    $page->assign(
      array(
        'id' => 21,
        'parent_id' => 11,
        'parent_path' => array(0, 11),
        'owner' => '123456789012345678901234567890ab',
        'group' => -1,
        'permissions' => '777',
        'inherit_visitor_permissions' => PapayaContentOptions::INHERIT_PERMISSIONS_OWN,
        'visitor_permissions' => array(1, 2),
        'created' => 123,
        'modified' => 456,
        'position' => 0,
        'inherit_boxes' => TRUE,
        'default_language' => 1,
        'link_type' => 1,
        'inherit_meta_information' => TRUE,
        'change_frequency' => 50,
        'priority' => 3,
        'scheme' => PapayaContentOptions::SCHEME_SYSTEM,
        'cache_mode' => PapayaContentOptions::CACHE_SYSTEM,
        'cache_time' => 0,
        'expires_mode' => PapayaContentOptions::CACHE_SYSTEM,
        'expires_time' => 0,
        'unpublished_translations' => 0
      )
    );
    return $page;
  }
}

class PapayaContentPageWork_TestProxy extends PapayaContentPageWork {

  public $publicationObject;

  public function _createPublicationObject() {
    if (NULL !== $this->publicationObject) {
      return $this->publicationObject;
    }
    return parent::_createPublicationObject();
  }
}
