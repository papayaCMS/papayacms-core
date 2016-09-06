<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');

class PapayaContentViewConfigurationsTest extends PapayaTestCase {

  /**
   * @covers PapayaContentViewConfigurations
   */
  public function testLoad() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with($this->equalTo(PapayaDatabaseResult::FETCH_ASSOC))
      ->will(
        $this->onConsecutiveCalls(
          array(
            'view_id' => '42',
            'viewmode_id' => '123',
            'viewlink_data' => 'DATA',
            'module_guid' => '123456789012345678901234567890ab',
            'module_type' => 'page'
          ),
          FALSE
        )
      );
    $databaseAccess = $this->getMock(
      'PapayaDatabaseAccess', array('getTableName', 'queryFmt'), array(new stdClass)
    );
    $databaseAccess
      ->expects($this->any())
      ->method('getTableName')
      ->with($this->isType('string'))
      ->will($this->returnArgument(0));
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with(
        $this->isType('string'),
        array(
          PapayaContentTables::VIEW_CONFIGURATIONS,
          PapayaContentTables::VIEW_MODES,
          PapayaContentTables::MODULES,
          PapayaContentTables::VIEW_DATAFILTER_CONFIGURATIONS,
          PapayaContentTables::VIEW_DATAFILTERS,
          PapayaContentTables::MODULES
        ),
        10,
        0
      )
      ->will($this->returnValue($databaseResult));
    $list = new PapayaContentViewConfigurations();
    $list->setDatabaseAccess($databaseAccess);
    $this->assertTrue(
      $list->load(42, 10, 0)
    );
    $this->assertEquals(
      array(
        array(
          'id' => '42',
          'mode_id' => 123,
          'options' => 'DATA',
          'module_guid' => '123456789012345678901234567890ab',
          'type' => 'page'
        )
      ),
      iterator_to_array($list)
    );
  }
}
