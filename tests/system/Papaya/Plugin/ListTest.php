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

use Papaya\Database\Result;

require_once __DIR__.'/../../../bootstrap.php';

class PapayaPluginListTest extends \PapayaTestCase {

  /**
  * @covers \PapayaPluginList::load
  */
  public function testLoad() {
    $databaseResult = $this->createMock(Result::class);
    $databaseResult
      ->expects($this->exactly(2))
      ->method('fetchRow')
      ->will(
        $this->onConsecutiveCalls(
          array(
            'module_guid' => '123',
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
    $list = new \PapayaPluginList();
    $list->setDatabaseAccess($databaseAccess);
    $this->assertTrue($list->load('123'));
    $this->assertEquals(
      array(
        '123' => array(
          'guid' => '123',
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
}
