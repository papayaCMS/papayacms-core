<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaContentViewsTest extends PapayaTestCase {

  /**
  * @covers PapayaContentViews::load
  */
  public function testLoad() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'view_id' => 21,
            'view_title' => 'Sample Title',
            'module_guid' => 'ab123456789012345678901234567890',
            'view_checksum' => 'ab123456789012345678901234567890:ab123456789012345678901234567890'
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
        array('table_'.PapayaContentTables::VIEWS, 'table_'.PapayaContentTables::MODULES)
      )
      ->will($this->returnValue($databaseResult));
    $pages = new PapayaContentViews();
    $pages->setDatabaseAccess($databaseAccess);
    $this->assertTrue($pages->load());
    $this->assertEquals(
      array(
        21 => array(
          'id' => 21,
          'title' => 'Sample Title',
          'module_id' => 'ab123456789012345678901234567890',
          'checksum' => 'ab123456789012345678901234567890:ab123456789012345678901234567890'
        )
      ),
      $pages->toArray()
    );
  }
}
