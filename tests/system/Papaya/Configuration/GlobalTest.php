<?php
require_once __DIR__.'/../../../bootstrap.php';

class PapayaConfigurationGlobalTest extends PapayaTestCase {

  /**
  * @covers PapayaConfigurationGlobal::get
  */
  public function testGetReadingConstant() {
    $config = new PapayaConfigurationGlobal_TestProxy();
    $this->assertNotEquals(
      'failed', $config->get('PAPAYA_INCLUDE_PATH')
    );
  }

  /**
  * @covers PapayaConfigurationGlobal::get
  */
  public function testGetCallingParentMethod() {
    $config = new PapayaConfigurationGlobal_TestProxy();
    $this->assertEquals(
      42, $config->get('SAMPLE_INT')
    );
  }

  /**
  * @covers PapayaConfigurationGlobal::get
  */
  public function testSetConstantShouldBeIgnored() {
    $config = new PapayaConfigurationGlobal_TestProxy();
    $config->set('PAPAYA_INCLUDE_PATH', 21);
    $this->assertNotEquals(
      21, $config->get('PAPAYA_INCLUDE_PATH')
    );
  }

  /**
  * @covers PapayaConfigurationGlobal::has
  */
  public function testHasWithConstantExpectingTrue() {
    $config = new PapayaConfigurationGlobal_TestProxy();
    $this->assertTrue($config->has('PAPAYA_INCLUDE_PATH'));
  }

  /**
  * @covers PapayaConfigurationGlobal::has
  */
  public function testHasExpectingTrue() {
    $config = new PapayaConfigurationGlobal_TestProxy();
    $this->assertTrue($config->has('SAMPLE_INT'));
  }

  /**
  * @covers PapayaConfigurationGlobal::defineConstants
  * @preserveGlobalState disabled
  * @runInSeparateProcess
  */
  public function testDefineConstants() {
    $config = new PapayaConfigurationGlobal_TestProxy();
    $this->assertFalse(defined('SAMPLE_INT'));
    $config->defineConstants();
    $this->assertTrue(defined('SAMPLE_INT'));
  }
}

class PapayaConfigurationGlobal_TestProxy extends PapayaConfigurationGlobal {

  public function __construct() {
    parent::__construct(
      array(
        'SAMPLE_INT' => 42,
        'PAPAYA_INCLUDE_PATH' => 'failed'
      )
    );
  }
}
