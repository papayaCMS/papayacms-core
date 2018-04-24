<?php
require_once __DIR__.'/../../../../bootstrap.php';
PapayaTestCase::defineConstantDefaults(
  'PAPAYA_DB_TBL_MODULES'
);

class PapayaApplicationProfilePluginsTest extends PapayaTestCase {

  /**
  * @covers PapayaApplicationProfilePlugins::createObject
  */
  public function testCreateObject() {
    $application = $this->createMock(PapayaApplication::class);
    $profile = new PapayaApplicationProfilePlugins();
    $plugins = $profile->createObject($application);
    $this->assertInstanceOf(
      'PapayaPluginLoader',
      $plugins
    );
    $this->assertSame($application, $plugins->papaya());
  }
}
