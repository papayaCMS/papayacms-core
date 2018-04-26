<?php
require_once __DIR__.'/../../../../bootstrap.php';

class PapayaContentPageReferencesTest extends PapayaTestCase {

  /**
  * @covers PapayaContentPageReferences::load
  * @covers PapayaContentPageReferences::_fetchRecords
  *
  *
   */
  public function testLoad() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'topic_source_id' => 21,
            'topic_target_id' => 42,
            'topic_note' => 'note 21 -> 42',
            'topic_source_title' => 'topic 21',
            'topic_target_title' => 'topic 42',
            'topic_source_modified' => '123',
            'topic_target_modified' => '456'
          ),
          array(
            'topic_source_id' => 42,
            'topic_target_id' => 84,
            'topic_note' => 'note 42 -> 84',
            'topic_source_title' => 'topic 42',
            'topic_target_title' => 'topic 84',
            'topic_source_modified' => '123',
            'topic_target_modified' => '456'
          ),
          FALSE
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array(
          'table_'.PapayaContentTables::PAGE_REFERENCES,
          'table_'.PapayaContentTables::PAGES,
          'table_'.PapayaContentTables::PAGE_TRANSLATIONS,
          42,
          1
        )
      )
      ->will(
        $this->returnValue($databaseResult)
      );
    $references = new PapayaContentPageReferences();
    $references->setDatabaseAccess($databaseAccess);
    $this->assertTrue($references->load(42, 1));
    $this->assertEquals(
      array(
        21 => array(
          'source_id' => 42,
          'target_id' => 21,
          'title' => 'topic 21',
          'modified' => '123',
          'note' => 'note 21 -> 42'
        ),
        84 => array(
          'source_id' => 42,
          'target_id' => 84,
          'title' => 'topic 84',
          'modified' => '456',
          'note' => 'note 42 -> 84'
        )
      ),
      $references->getIterator()->getArrayCopy()
    );
  }
}
