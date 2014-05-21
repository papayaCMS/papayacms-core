<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaContentViewsTest extends PapayaTestCase {

  /**
  * @covers PapayaContentViews::load
  */
  public function testLoad() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
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
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array(PapayaContentTables::VIEWS, PapayaContentTables::MODULES)
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
