<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentPageDependenciesTest extends PapayaTestCase {

  /**
  * @covers PapayaContentPageDependencies::load
  */
  public function testLoad() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with($this->isType('integer'))
      ->will(
        $this->onConsecutiveCalls(
          array(
            'topic_id' => 21,
            'topic_origin_id' => 42,
            'topic_synchronization' => 35,
            'topic_note' => 'sample note',
            'topic_title' => 'sample page title',
            'topic_modified' => 123,
            'topic_unpublished_languages' => 1,
            'topic_published' => 456,
            'published_from' => 0,
            'published_to' => 0
          ),
          FALSE
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with()
      ->will($this->returnValue($databaseResult));

    $dependencies = new PapayaContentPageDependencies();
    $dependencies->setDatabaseAccess($databaseAccess);
    $this->assertTrue(
      $dependencies->load(42, 1, 10, 0)
    );
    $this->assertAttributeEquals(
      array(
        21 => array(
          'id' => 21,
          'origin_id' => 42,
          'synchronization' => 35,
          'note' => 'sample note',
          'title' => 'sample page title',
          'modified' => 123,
          'unpublished_languages' => 1,
          'published' => 456,
          'published_from' => 0,
          'published_to' => 0
        )
      ),
      '_records',
      $dependencies
    );
  }

  /**
  * @covers PapayaContentPageDependencies::getDependency
  */
  public function testGetDependency() {
    $dependencies = new PapayaContentPageDependencies_TestProxy();
    $dependency = $dependencies->getDependency(21);
    $this->assertInstanceOf(PapayaContentPageDependency::class, $dependency);
    $this->assertAttributeEquals(
      array(
        'id' => NULL,
        'note' => NULL,
        'origin_id' => NULL,
        'synchronization' => NULL
      ),
      '_values',
      $dependency
    );
  }

  /**
  * @covers PapayaContentPageDependencies::getDependency
  */
  public function testGetDependencyWithData() {
    $dependencies = new PapayaContentPageDependencies_TestProxy();
    $dependencies->_records = array(
      21 => array(
        'id' => 21,
        'origin_id' => 42,
        'synchronization' => 35,
        'note' => 'sample note',
        'title' => 'sample page title',
        'modified' => 123,
        'unpublished_languages' => 1,
        'published' => 456,
        'published_from' => 0,
        'published_to' => 0
      )
    );
    $dependency = $dependencies->getDependency(21);
    $this->assertAttributeEquals(
      array(
        'id' => 21,
        'origin_id' => 42,
        'synchronization' => 35,
        'note' => 'sample note'
      ),
      '_values',
      $dependency
    );
  }

  /**
  * @covers PapayaContentPageDependencies::delete
  */
  public function testDelete() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('deleteRecord')
      ->with()
      ->will($this->returnValue(TRUE));
    $dependencies = new PapayaContentPageDependencies();
    $dependencies->setDatabaseAccess($databaseAccess);
    $this->assertTrue(
      $dependencies->delete(42)
    );
  }

  /**
  * @covers PapayaContentPageDependencies::delete
  */
  public function testDeleteChangesRecords() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('deleteRecord')
      ->with()
      ->will($this->returnValue(TRUE));
    $dependencies = new PapayaContentPageDependencies_TestProxy();
    $dependencies->_records = array(
      21 => array(
        'id' => 21,
        'origin_id' => 42,
        'synchronization' => 35,
        'note' => 'sample note',
        'title' => 'sample page title',
        'modified' => 123,
        'unpublished_languages' => 1,
        'published' => 456,
        'published_from' => 0,
        'published_to' => 0
      )
    );
    $dependencies->setDatabaseAccess($databaseAccess);
    $dependencies->delete(21);
    $this->assertAttributeEquals(
      array(), '_records', $dependencies
    );
  }

  /**
  * @covers PapayaContentPageDependencies::changeOrigin
  */
  public function testChangeOrigin() {
    $databaseResultLoad = $this->createMock(PapayaDatabaseResult::class);
    $databaseResultLoad
      ->expects($this->any())
      ->method('fetchRow')
      ->with($this->isType('integer'))
      ->will($this->returnValue(FALSE));
    $databaseResultCheck = $this->createMock(PapayaDatabaseResult::class);
    $databaseResultCheck
      ->expects($this->any())
      ->method('fetchField')
      ->will($this->returnValue(0));
    $databaseAccess = $this
      ->getMockBuilder(PapayaDatabaseAccess::class)
      ->disableOriginalConstructor()
      ->setMethods(
        array(
          'getTableName',
          'queryFmt',
          'deleteRecord',
          'updateRecord',
          'insertRecord',
          'getSqlCondition'
        )
      )
      ->getMock();
    $databaseAccess
      ->expects($this->any())
      ->method('getTableName')
      ->withAnyParameters()
      ->will($this->returnArgument(0));
    $databaseAccess
      ->expects($this->exactly(2))
      ->method('getSqlCondition')
      ->with($this->isType('array'))
      ->will($this->returnValue(" topic_id = 'xx'"));
    $databaseAccess
      ->expects($this->once())
      ->method('deleteRecord')
      ->with()
      ->will($this->returnValue(TRUE));
    $databaseAccess
      ->expects($this->once())
      ->method('updateRecord')
      ->with('topic_dependencies', array('topic_origin_id' => 42), array('topic_origin_id' => 21))
      ->will($this->returnValue(TRUE));
    $databaseAccess
      ->expects($this->exactly(3))
      ->method('queryFmt')
      ->withAnyParameters()
      ->will(
        $this->onConsecutiveCalls(
          $databaseResultLoad, $databaseResultCheck, $databaseResultCheck
        )
       );
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecord')
      ->with(
        'topic_dependencies',
        NULL,
        array(
          'topic_id' => 21,
          'topic_note' => NULL,
          'topic_origin_id' => 42,
          'topic_synchronization' => NULL
        )
      )
      ->will($this->returnValue(TRUE));
    $dependencies = new PapayaContentPageDependencies();
    $dependencies->setDatabaseAccess($databaseAccess);
    $dependencies->changeOrigin(21, 42);
  }
}

class PapayaContentPageDependencies_TestProxy extends PapayaContentPageDependencies {

  public $_records;
}
