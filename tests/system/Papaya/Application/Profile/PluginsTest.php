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
    $profile = new PapayaApplicationProfilePlugins();
    $plugins = $profile->createObject($application = $this->mockPapaya()->application());
    $this->assertInstanceOf(
      PapayaPluginLoader::class,
      $plugins
    );
    $this->assertSame($application, $plugins->papaya());
  }
}
