<?php
/**
 * papaya CMS
 *
 * @copyright 2000-2018 by papayaCMS project - All rights reserved.
 * @link http://www.papaya-cms.com/
 * @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU General Public License, version 2
 *
 *  You can redistribute and/or modify this script under the terms of the GNU General Public
 *  License (GPL) version 2, provided that the copyright and license notes, including these
 *  lines, remain unmodified. papaya is distributed in the hope that it will be useful, but
 *  WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 *  FOR A PARTICULAR PURPOSE.
 */

require_once __DIR__.'/../../../vendor/papaya/test-framework/src/PapayaTestCase.php';

require_once __DIR__.'/../../../src/system/Papaya/Autoloader.php';
require_once __DIR__.'/../../../src/system/Papaya/Util/File/Path.php';

class PapayaAutoloaderTest extends PapayaTestCase {

  public function tearDown() {
    PapayaAutoloader::clear();
  }

  /**
  * @covers PapayaAutoloader
  */
  public function testLoad() {
    PapayaAutoloader::load('Papaya\\Test\\Autoloader\\Test_class', __DIR__.'/TestData/class.php');
    $this->assertTrue(class_exists('Papaya\\Test\\Autoloader\\Test_class', FALSE));
  }

  /**
   * @covers PapayaAutoloader
   */
  public function testLoadAddsAliasForNamespaceClass() {
    PapayaAutoloader::load('PapayaTestAutoloaderTest_class', __DIR__.'/TestData/class.php');
    $this->assertTrue(class_exists('PapayaTestAutoloaderTest_class', FALSE));
  }

  /**
   * @covers PapayaAutoloader
   * @dataProvider getClassFileDataProvider
   * @param string $expected
   * @param string $className
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
      array('/Papaya/Module/Sample/' => '/foo/bar/'), '_paths', PapayaAutoloader::class
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
      PapayaAutoloader::class
    );
  }

  /**
  * @covers PapayaAutoloader
  */
  public function testClearPaths() {
    PapayaAutoloader::registerPath('PapayaModuleSample', '/foo/bar');
    PapayaAutoloader::clear();
    $this->assertAttributeEquals(
      array(), '_paths', PapayaAutoloader::class
    );
  }

  /**
   * @covers PapayaAutoloader
   * @dataProvider getModuleClassFileDataProvider
   * @param string $expected
   * @param string $moduleClass
   * @param string $modulePrefix
   * @param string $modulePath
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
      array('/system/Papaya/Object.php', 'PapayaObject'),
      array('/system/Papaya/BaseObject.php', 'Papaya\\BaseObject'),
      array('/system/Papaya/Sample.php', 'PapayaSample'),
      array('/system/Papaya/Sample/Abbr.php', 'PapayaSampleABBR'),
      array('/system/Papaya/Sample/Abbr/Class.php', 'PapayaSampleABBRClass'),
      array('/system/base_options.php', 'base_options'),
      array('/system/Papaya/Sample.php', '\\Papaya\\Sample')
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
