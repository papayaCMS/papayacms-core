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

namespace Papaya;
require_once __DIR__.'/../../../vendor/papaya/test-framework/src/PapayaTestCase.php';

require_once __DIR__.'/../../../src/system/Papaya/Autoloader.php';
require_once __DIR__.'/../../../src/system/Papaya/Utility/File/Path.php';

class AutoloaderTest extends \Papaya\TestCase {

  public function tearDown() {
    Autoloader::clear();
  }

  /**
   * @covers \Papaya\Autoloader
   */
  public function testLoad() {
    Autoloader::load('Papaya\\Test\\Autoloader\\Test_class', __DIR__.'/TestData/class.php');
    $this->assertTrue(class_exists('Papaya\\Test\\Autoloader\\Test_class', FALSE));
  }

  /**
   * @covers \Papaya\Autoloader
   */
  public function testLoadAddsAliasForNamespaceClass() {
    Autoloader::load('PapayaTestAutoloaderTest_class', __DIR__.'/TestData/class.php');
    $this->assertTrue(class_exists('PapayaTestAutoloaderTest_class', FALSE));
  }

  /**
   * @covers       \Papaya\Autoloader
   * @dataProvider getClassFileDataProvider
   * @param string $expected
   * @param string $className
   */
  public function testGetClassFile($expected, $className) {
    $this->assertStringEndsWith(
      $expected,
      Autoloader::getClassFile($className)
    );
  }

  /**
   * @covers \Papaya\Autoloader
   */
  public function testGetClassFileForUnknownClass() {
    $this->assertNull(
      Autoloader::getClassFile('unknown_class_name')
    );
  }

  /**
   * @covers \Papaya\Autoloader
   */
  public function testRegisterPath() {
    Autoloader::clear();
    Autoloader::registerPath('PapayaModuleSample', '/foo/bar');
    $this->assertAttributeEquals(
      array('/Papaya/Module/Sample/' => '/foo/bar/'), '_paths', Autoloader::class
    );
  }

  /**
   * @covers \Papaya\Autoloader
   */
  public function testRegisterPathSortsPaths() {
    Autoloader::registerPath('PapayaModuleSample', '/foo/bar');
    Autoloader::registerPath('PapayaModule', '/bar/foo/foobar');
    Autoloader::registerPath('PapayaModuleSampleChild', '/foo/bar/child');
    $this->assertAttributeEquals(
      array(
        '/Papaya/Module/Sample/Child/' => '/foo/bar/child/',
        '/Papaya/Module/Sample/' => '/foo/bar/',
        '/Papaya/Module/' => '/bar/foo/foobar/'
      ),
      '_paths',
      Autoloader::class
    );
  }

  /**
   * @covers \Papaya\Autoloader
   */
  public function testClearPaths() {
    Autoloader::registerPath('PapayaModuleSample', '/foo/bar');
    Autoloader::clear();
    $this->assertAttributeEquals(
      array(), '_paths', Autoloader::class
    );
  }

  /**
   * @covers       \Papaya\Autoloader
   * @dataProvider getModuleClassFileDataProvider
   * @param string $expected
   * @param string $moduleClass
   * @param string $modulePrefix
   * @param string $modulePath
   */
  public function testGetClassFileAfterPathRegistration(
    $expected, $moduleClass, $modulePrefix, $modulePath
  ) {
    Autoloader::registerPath($modulePrefix, $modulePath);
    $this->assertEquals(
      $expected,
      Autoloader::getClassFile($moduleClass)
    );
  }

  /**
   * @covers \Papaya\Autoloader
   */
  public function testHasPrefixExpectingTrue() {
    Autoloader::registerPath('PapayaModuleSample', '/foo/bar');
    $this->assertTrue(Autoloader::hasPrefix('PapayaModuleSample'));
  }

  /**
   * @covers \Papaya\Autoloader
   */
  public function testHasPrefixExpectingFalse() {
    $this->assertFalse(Autoloader::hasPrefix('PapayaModuleSample'));
  }

  /**
   * @covers \Papaya\Autoloader
   */
  public function testHasClassmapExpectingTrue() {
    Autoloader::registerClassMap('/foo/bar', array('Foo', '/Foo.php'));
    $this->assertTrue(Autoloader::hasClassMap('/foo/bar'));
  }

  /**
   * @covers \Papaya\Autoloader
   */
  public function testHasClassmapExpectingFalse() {
    $this->assertFalse(Autoloader::hasClassMap('/foo/bar'));
  }

  /****************************
   * Data Provider
   ****************************/

  public static function getClassFileDataProvider() {
    return array(
      array('/system/Papaya/Request.php', 'PapayaRequest'),
      array('/system/Papaya/Request.php', 'Papaya\\Request'),
      array('/system/Papaya/Application/BaseObject.php', 'Papaya\\Application\\BaseObject'),
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
