<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaContentConfigurationTest extends PapayaTestCase {

  /**
  * @covers PapayaContentConfiguration::load
  */
  public function testLoad() {
    $databaseResult = $this->createMock(PapayaDatabaseResult::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'opt_name' => 'SAMPLE_OPTION',
            'opt_value' => '42'
          ),
          FALSE
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('table_options'))
      ->will($this->returnValue($databaseResult));
    $configuration = new PapayaContentConfiguration();
    $configuration->setDatabaseAccess($databaseAccess);
    $this->assertTrue($configuration->load());
    $this->assertAttributeEquals(
      array(
        'SAMPLE_OPTION' => array(
          'name' => 'SAMPLE_OPTION',
          'value' => '42'
        )
      ),
      '_records',
      $configuration
    );
  }
}
