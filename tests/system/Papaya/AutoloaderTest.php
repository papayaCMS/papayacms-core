<?php

require_once(dirname(__FILE__).'/../../../vendor/papaya/unittest-framework/PapayaTestCase.php');

require_once(dirname(__FILE__).'/../../../src/system/Papaya/Autoloader.php');
require_once(dirname(__FILE__).'/../../../src/system/Papaya/Util/File/Path.php');

class PapayaAutoloaderTest extends PapayaTestCase {

  public function tearDown() {
    PapayaAutoloader::clear();
  }

  /**
  * @covers PapayaAutoloader
  */
  public function testLoad() {
    PapayaAutoloader::load('AutoloaderTestClass', dirname(__FILE__).'/TestData/class.php');
    $this->assertTrue(class_exists('AutoloaderTestClass', FALSE));
  }

  /**
  * @covers PapayaAutoloader
  * @dataProvider getClassFileDataProvider
  */
  public function testGetClassFile($expected, $className) {
    $this->assertStringEndsWith(
      $expected,
      PapayaAutoloader::getClassFile($className)
    );
  }

  /**
  * @covers PapayaAutoloader
  */
  public function testGetClassFileForUnknownClass() {
    $this->assertNull(
      PapayaAutoloader::getClassFile('unknown_class_name')
    );
  }

  /**
  * @covers PapayaAutoloader
  */
  public function testRegisterPath() {
    PapayaAutoloader::clear();
    PapayaAutoloader::registerPath('PapayaModuleSample', '/foo/bar');
    $this->assertAttributeEquals(
      array('/Papaya/Module/Sample/' => '/foo/bar/'), '_paths', 'PapayaAutoloader'
    );
  }

  /**
  * @covers PapayaAutoloader
  */
  public function testRegisterPathSortsPaths() {
    PapayaAutoloader::registerPath('PapayaModuleSample', '/foo/bar');
    PapayaAutoloader::registerPath('PapayaModule', '/bar/foo/foobar');
    PapayaAutoloader::registerPath('PapayaModuleSampleChild', '/foo/bar/child');
    $this->assertAttributeEquals(
      array(
        '/Papaya/Module/Sample/Child/' => '/foo/bar/child/',
        '/Papaya/Module/Sample/' => '/foo/bar/',
        '/Papaya/Module/' => '/bar/foo/foobar/'
      ),
      '_paths',
      'PapayaAutoloader'
    );
  }

  /**
  * @covers PapayaAutoloader
  */
  public function testClearPaths() {
    PapayaAutoloader::registerPath('PapayaModuleSample', '/foo/bar');
    PapayaAutoloader::clear();
    $this->assertAttributeEquals(
      array(), '_paths', 'PapayaAutoloader'
    );
  }

  /**
  * @covers PapayaAutoloader
  * @dataProvider getModuleClassFileDataProvider
  */
  public function testGetClassFileAfterPathRegistration(
    $expected, $moduleClass, $modulePrefix, $modulePath
  ) {
    PapayaAutoloader::registerPath($modulePrefix, $modulePath);
    $this->assertEquals(
      $expected,
      PapayaAutoloader::getClassFile($moduleClass)
    );
  }

  /**
  * @covers PapayaAutoloader
  */
  public function testHasPrefixExpectingTrue() {
    PapayaAutoloader::registerPath('PapayaModuleSample', '/foo/bar');
    $this->assertTrue(PapayaAutoloader::hasPrefix('PapayaModuleSample'));
  }

  /**
  * @covers PapayaAutoloader
  */
  public function testHasPrefixExpectingFalse() {
    $this->assertFalse(PapayaAutoloader::hasPrefix('PapayaModuleSample'));
  }

  /**
  * @covers PapayaAutoloader
  */
  public function testHasClassmapExpectingTrue() {
    PapayaAutoloader::registerClassMap('/foo/bar', array('Foo', '/Foo.php'));
    $this->assertTrue(PapayaAutoloader::hasClassMap('/foo/bar'));
  }

  /**
  * @covers PapayaAutoloader
  */
  public function testHasClassmapExpectingFalse() {
    $this->assertFalse(PapayaAutoloader::hasClassMap('/foo/bar'));
  }

  /****************************
  * Data Provider
  ****************************/

  public static function getClassFileDataProvider() {
    return array(
      array('/system/Papaya/Sample.php', 'PapayaSample'),
      array('/system/Papaya/Sample/Abbr.php', 'PapayaSampleABBR'),
      array('/system/Papaya/Sample/Abbr/Class.php', 'PapayaSampleABBRClass'),
      array('/system/base_options.php', 'base_options')
    );
  }

  public static function getModuleClassFileDataProvider() {
    return array(
      array(
        '/some/module/Sample.php', 'PapayaModuleSample', 'PapayaModule', '/some/module'
      ),
      array(
        '/some/module/Group/Sample.php', 'PapayaModuleGroupSample', 'PapayaModule', '/some/module'
      ),
      array(
        '/some/module/external/Sample.php', 'ExternalSample', 'External', '/some/module/external'
      ),
      array(
        NULL, 'PapayaModuleFooSample', 'PapayaModuleBar', '/some/module'
      )
    );
  }
}
