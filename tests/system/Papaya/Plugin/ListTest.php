<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaPluginListTest extends PapayaTestCase {

  /**
  * @covers PapayaPluginList::load
  */
  public function testLoad() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->exactly(2))
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'module_guid' => '123',
            'module_class' => 'SampleClass',
            'module_path' => '/Sample/Path',
            'module_file' => 'SampleFile.php',
            'module_active' => '1',
            'modulegroup_prefix' => 'SamplePrefix',
            'modulegroup_classes' => '_classmap.php'
          ),
          FALSE
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), $this->equalTo(array('table_modules', 'table_modulegroups')))
      ->will($this->returnValue($databaseResult));
    $list = new PapayaPluginList();
    $list->setDatabaseAccess($databaseAccess);
    $this->assertTrue($list->load('123'));
    $this->assertEquals(
      array(
        '123' => array(
          'guid' => '123',
          'class' => 'SampleClass',
          'path' => '/Sample/Path',
          'file' => 'SampleFile.php',
          'active' => TRUE,
          'prefix' => 'SamplePrefix',
          'classes' => '_classmap.php',
        )
      ),
      iterator_to_array($list)
    );
  }
}
