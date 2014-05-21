<?php
require_once(dirname(__FILE__).'/../../../../bootstrap.php');
PapayaTestCase::defineConstantDefaults(
  'PAPAYA_DB_TBL_MODULES'
);

class PapayaApplicationProfilePluginsTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfilePlugins::createObject
  */
  public function testCreateObject() {
    $application = $this->getMock('PapayaApplication');
    $profile = new PapayaApplicationProfilePlugins();
    $plugins = $profile->createObject($application);
    $this->assertInstanceOf(
      'PapayaPluginLoader',
      $plugins
    );
    $this->assertSame($application, $plugins->papaya());
  }
}
