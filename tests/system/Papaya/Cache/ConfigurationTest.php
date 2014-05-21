<?php
require_once(dirname(__FILE__).'/../../../bootstrap.php');

class PapayaCacheConfigurationTest extends PapayaTestCase {

  public function testConstructor() {
    $configuration = new PapayaCacheConfiguration();
    $this->assertEquals(
      array(
        'SERVICE' => 'file',
        'FILESYSTEM_PATH' => '/tmp',
        'FILESYSTEM_NOTIFIER_SCRIPT' => '',
        'FILESYSTEM_DISABLE_CLEAR' => FALSE,
        'MEMCACHE_SERVERS' => ''
      ),
      iterator_to_array($configuration)
    );
  }

}