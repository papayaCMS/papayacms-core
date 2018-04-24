<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentPageTagsTest extends PapayaTestCase {

  /**
  * @covers PapayaContentPageTags::load
  */
  public function testLoad() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will(
        $this->onConsecutiveCalls(
          array(
            'tag_id' => 1,
            'link_id' => 23,
            'tag_title' => NULL,
            'tag_image' => NULL,
            'tag_description' => NULL,
            'tag_char' => NULL,
          ),
          array(
            'tag_id' => 2,
            'link_id' => 23,
            'tag_title' => NULL,
            'tag_image' => NULL,
            'tag_description' => NULL,
            'tag_char' => NULL,
          ),
          FALSE
        )
      );
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('tag_links', 'tag_trans', 0, 'topic', 23))
      ->will($this->returnValue($databaseResult));
    $tags = new PapayaContentPageTags();
    $tags->setDatabaseAccess($databaseAccess);
    $this->assertTrue($tags->load(23));
    $this->assertEquals(
      array(
        1 => array(
          'id' => 1,
          'page_id' => 23,
          'title' => NULL,
          'image' => NULL,
          'description' => NULL,
          'char' => NULL
        ),
        2 => array(
          'id' => 2,
          'page_id' => 23,
          'title' => NULL,
          'image' => NULL,
          'description' => NULL,
          'char' => NULL
        )
      ),
      $tags->getIterator()->getArrayCopy()
    );
  }

  /**
  * @covers PapayaContentPageTags::load
  */
  public function testLoadWithLanguageId() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with(PapayaDatabaseResult::FETCH_ASSOC)
      ->will(
        $this->onConsecutiveCalls(
          array(
            'tag_id' => 1,
            'link_id' => 23,
            'tag_title' => 'sample title one',
            'tag_image' => 'fdcadb8ada3a8a5067a597cd705824fb',
            'tag_description' => 'A short description',
            'tag_char' => 's'
          ),
          array(
            'tag_id' => 2,
            'link_id' => 23,
            'tag_title' => 'sample title two',
            'tag_image' => 'fdcadb8ada3a8a5067a597cd705824fb',
            'tag_description' => NULL,
            'tag_char' => 's'
          ),
          FALSE
        )
      );
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('tag_links', 'tag_trans', 2, 'topic', 23))
      ->will($this->returnValue($databaseResult));
    $tags = new PapayaContentPageTags();
    $tags->setDatabaseAccess($databaseAccess);
    $this->assertTrue($tags->load(23, 2));
    $this->assertEquals(
      array(
        1 => array(
          'id' => 1,
          'page_id' => 23,
          'title' => 'sample title one',
          'image' => 'fdcadb8ada3a8a5067a597cd705824fb',
          'description' => 'A short description',
          'char' => 's'
        ),
        2 => array(
          'id' => 2,
          'page_id' => 23,
          'title' => 'sample title two',
          'image' => 'fdcadb8ada3a8a5067a597cd705824fb',
          'description' => NULL,
          'char' => 's'
        )
      ),
      $tags->getIterator()->getArrayCopy()
    );
  }

  /**
  * @covers PapayaContentPageTags::clear
  */
  public function testClear() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('deleteRecord'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('deleteRecord')
      ->with(
        'tag_links',
        array(
          'link_type' => 'topic',
          'link_id' => 23
        )
      )
      ->will($this->returnValue(2));
    $tags = new PapayaContentPageTags();
    $tags->setDatabaseAccess($databaseAccess);
    $this->assertTrue($tags->clear(23));
  }

  /**
  * @covers PapayaContentPageTags::insert
  */
  public function testInsert() {
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('insertRecords'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('insertRecords')
      ->with(
        'tag_links',
        array(
          array(
            'link_type' => 'topic',
            'link_id' => 23,
            'tag_id' => 2
          ),
          array(
            'link_type' => 'topic',
            'link_id' => 23,
            'tag_id' => 3
          )
        )
      )
      ->will($this->returnValue(2));
    $tags = new PapayaContentPageTags();
    $tags->setDatabaseAccess($databaseAccess);
    $this->assertTrue($tags->insert(23, array(2, 3)));
  }
}
