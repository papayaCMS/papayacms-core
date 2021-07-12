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

namespace Papaya {
  require_once __DIR__.'/../../TestFramework/TestCase.php';

  require_once __DIR__.'/../../../src/system/Papaya/Autoloader.php';
  require_once __DIR__.'/../../../src/system/Papaya/Utility/File/Path.php';

  /**
   * @covers \Papaya\Autoloader
   */
  class AutoloaderTest extends TestCase {

    public function tearDown(): void {
      Autoloader::clear();
    }

    public function testLoad() {
      Autoloader::load('Papaya\\Test\\Autoloader\\Test_class', __DIR__.'/TestData/class.php');
      $this->assertTrue(class_exists('Papaya\\Test\\Autoloader\\Test_class', FALSE));
    }

    public function testLoadAddsAliasForNamespaceClass() {
      Autoloader::load('PapayaTestAutoloaderTest_class', __DIR__.'/TestData/class.php');
      $this->assertTrue(class_exists('PapayaTestAutoloaderTest_class', FALSE));
    }

    /**
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

    public function testGetClassFileForUnknownClass() {
      $this->assertNull(
        Autoloader::getClassFile('unknown_class_name')
      );
    }

    public function testRegisterPath() {
      Autoloader::clear();
      Autoloader::registerPath('PapayaModuleSample', '/foo/bar');
      $this->assertEquals(
        ['/Papaya/Module/Sample/' => '/foo/bar/'],
        Autoloader::getRegisteredPaths()
      );
    }

    public function testRegisterPathSortsPaths() {
      Autoloader::registerPath('PapayaModuleSample', '/foo/bar');
      Autoloader::registerPath('PapayaModule', '/bar/foo/foobar');
      Autoloader::registerPath('PapayaModuleSampleChild', '/foo/bar/child');
      $this->assertEquals(
        [
          '/Papaya/Module/Sample/Child/' => '/foo/bar/child/',
          '/Papaya/Module/Sample/' => '/foo/bar/',
          '/Papaya/Module/' => '/bar/foo/foobar/'
        ],
        Autoloader::getRegisteredPaths()
      );
    }

    public function testClearPaths() {
      Autoloader::registerPath('PapayaModuleSample', '/foo/bar');
      Autoloader::clear();
      $this->assertEquals(
        [],
        Autoloader::getRegisteredPaths()
      );
    }

    /**
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

    public function testHasPrefixExpectingTrue() {
      Autoloader::registerPath('PapayaModuleSample', '/foo/bar');
      $this->assertTrue(Autoloader::hasPrefix('PapayaModuleSample'));
    }

    public function testHasPrefixExpectingFalse() {
      $this->assertFalse(Autoloader::hasPrefix('PapayaModuleSample'));
    }

    public function testHasClassmapExpectingTrue() {
      Autoloader::registerClassMap('/foo/bar', ['Foo', '/Foo.php']);
      $this->assertTrue(Autoloader::hasClassMap('/foo/bar'));
    }

    public function testHasClassmapExpectingFalse() {
      $this->assertFalse(Autoloader::hasClassMap('/foo/bar'));
    }

    /**
     * @param string $expected
     * @param string $className
     * @testWith
     *   ["Papaya\\Example", "PapayaExample"]
     *   ["Papaya\\Text\\Example", "PapayaStringExample"]
     *   ["Papaya\\Application\\BaseObject", "PapayaObject"]
     */
    public function testConvertToNamespaceClass($expected, $className) {
      $this->assertSame($expected, Autoloader::convertToNamespaceClass($className));
    }

    /**
     * @param string $expected
     * @param string $className
     * @testWith
     *   ["PapayaExample", "Papaya\\Example"]
     *   ["PapayaStringExample", "Papaya\\Text\\Example"]
     *   ["PapayaObject", "Papaya\\Application\\BaseObject"]
     */
    public function testConvertToToOldClass($expected, $className) {
      $this->assertSame($expected, Autoloader::convertToToOldClass($className));
    }

    /****************************
     * Data Provider
     ****************************/

    public static function getClassFileDataProvider() {
      return [
        ['/system/Papaya/Request.php', 'PapayaRequest'],
        ['/system/Papaya/Request.php', 'Papaya\\Request'],
        ['/system/Papaya/Application/BaseObject.php', 'Papaya\\Application\\BaseObject'],
        ['/system/Papaya/Sample.php', 'PapayaSample'],
        ['/system/Papaya/Sample/Abbr.php', 'PapayaSampleABBR'],
        ['/system/Papaya/Sample/Abbr/Class.php', 'PapayaSampleABBRClass'],
        ['/system/base_options.php', 'base_options'],
        ['/system/Papaya/Sample.php', '\\Papaya\\Sample']
      ];
    }

    public static function getModuleClassFileDataProvider() {
      return [
        [
          '/some/module/Sample.php', 'PapayaModuleSample', 'PapayaModule', '/some/module'
        ],
        [
          '/some/module/Group/Sample.php', 'PapayaModuleGroupSample', 'PapayaModule', '/some/module'
        ],
        [
          '/some/module/external/Sample.php', 'ExternalSample', 'External', '/some/module/external'
        ],
        [
          NULL, 'PapayaModuleFooSample', 'PapayaModuleBar', '/some/module'
        ]
      ];
    }
  }
}
