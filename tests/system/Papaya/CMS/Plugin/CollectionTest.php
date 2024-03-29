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

namespace Papaya\CMS\Plugin;

require_once __DIR__.'/../../../../bootstrap.php';

class CollectionTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\CMS\Plugin\Collection::load
   */
  public function testLoad() {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->exactly(2))
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'module_guid' => '123',
            'module_type' => Types::PAGE,
            'module_class' => 'SampleClass',
            'module_path' => '/Sample/Path',
            'module_file' => 'SampleFile.php',
            'module_active' => '1',
            'modulegroup_prefix' => 'SamplePrefix',
            'modulegroup_classes' => '_classmap.php'
          ),
          FALSE
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), $this->equalTo(array('table_modules', 'table_modulegroups')))
      ->will($this->returnValue($databaseResult));
    $list = new Collection();
    $list->setDatabaseAccess($databaseAccess);
    $this->assertTrue($list->load('123'));
    $this->assertEquals(
      array(
        '123' => array(
          'guid' => '123',
          'type' => Types::PAGE,
          'class' => 'SampleClass',
          'path' => '/Sample/Path',
          'file' => 'SampleFile.php',
          'active' => TRUE,
          'prefix' => 'SamplePrefix',
          'classes' => '_classmap.php',
        )
      ),
      iterator_to_array($list)
    );
  }

  public function testWithType() {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->atLeastOnce())
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'module_guid' => '123',
            'module_type' => Types::PAGE,
            'module_class' => 'SampleClassOne',
            'module_path' => '/Sample/Path',
            'module_file' => 'SampleFile.php',
            'module_active' => '1',
            'modulegroup_prefix' => 'SamplePrefix',
            'modulegroup_classes' => '_classmap.php'
          ),
          array(
            'module_guid' => '456',
            'module_type' => Types::BOX,
            'module_class' => 'SampleClassTwo',
            'module_path' => '/Sample/Path',
            'module_file' => 'SampleFile.php',
            'module_active' => '1',
            'modulegroup_prefix' => 'SamplePrefix',
            'modulegroup_classes' => '_classmap.php'
          ),
          array(
            'module_guid' => '789',
            'module_type' => Types::PAGE,
            'module_class' => 'SampleClassTwo',
            'module_path' => '/Sample/Path',
            'module_file' => 'SampleFile.php',
            'module_active' => '0',
            'modulegroup_prefix' => 'SamplePrefix',
            'modulegroup_classes' => '_classmap.php'
          ),
          FALSE
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), $this->equalTo(array('table_modules', 'table_modulegroups')))
      ->will($this->returnValue($databaseResult));
    $list = new Collection();
    $list->setDatabaseAccess($databaseAccess);
    $this->assertTrue($list->load());
    $this->assertEquals(
      array(
        '123' => array(
          'guid' => '123',
          'type' => Types::PAGE,
          'class' => 'SampleClassOne',
          'path' => '/Sample/Path',
          'file' => 'SampleFile.php',
          'active' => TRUE,
          'prefix' => 'SamplePrefix',
          'classes' => '_classmap.php',
        )
      ),
      iterator_to_array($list->withType(Types::PAGE))
    );

  }
}
