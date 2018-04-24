<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaContentConfigurationTest extends PapayaTestCase {

  /**
  * @covers PapayaContentConfiguration::load
  */
  public function testLoad() {
    $databaseResult = $this->getMock('PapayaDatabaseResult');
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
    $databaseAccess = $this
      ->getMockBuilder('PapayaDatabaseAccess')
      ->disableOriginalConstructor()
      ->setMethods(array('queryFmt'))
      ->getMock();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('options'))
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
