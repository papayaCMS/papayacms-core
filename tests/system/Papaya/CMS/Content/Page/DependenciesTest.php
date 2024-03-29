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

namespace Papaya\CMS\Content\Page {

  require_once __DIR__.'/../../../../../bootstrap.php';

  class DependenciesTest extends \Papaya\TestFramework\TestCase {

    /**
     * @covers \Papaya\CMS\Content\Page\Dependencies::load
     */
    public function testLoad() {
      $databaseResult = $this->createMock(\Papaya\Database\Result::class);
      $databaseResult
        ->expects($this->any())
        ->method('fetchRow')
        ->with($this->isType('integer'))
        ->will(
          $this->onConsecutiveCalls(
            array(
              'topic_id' => 21,
              'topic_origin_id' => 42,
              'topic_synchronization' => 35,
              'topic_note' => 'sample note',
              'topic_title' => 'sample page title',
              'topic_modified' => 123,
              'topic_unpublished_languages' => 1,
              'topic_published' => 456,
              'published_from' => 0,
              'published_to' => 0
            ),
            FALSE
          )
        );
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('queryFmt')
        ->with()
        ->will($this->returnValue($databaseResult));

      $dependencies = new Dependencies();
      $dependencies->setDatabaseAccess($databaseAccess);
      $this->assertTrue(
        $dependencies->load(42, 1, 10, 0)
      );
      $this->assertEquals(
        array(
          21 => array(
            'id' => 21,
            'origin_id' => 42,
            'synchronization' => 35,
            'note' => 'sample note',
            'title' => 'sample page title',
            'modified' => 123,
            'unpublished_languages' => 1,
            'published' => 456,
            'published_from' => 0,
            'published_to' => 0
          )
        ),
        iterator_to_array($dependencies)
      );
    }

    /**
     * @covers \Papaya\CMS\Content\Page\Dependencies::getDependency
     */
    public function testGetDependency() {
      $dependencies = new Dependencies_TestProxy();
      $dependency = $dependencies->getDependency(21);
      $this->assertInstanceOf(Dependency::class, $dependency);
      $this->assertEquals(
        array(
          'id' => NULL,
          'note' => NULL,
          'origin_id' => NULL,
          'synchronization' => NULL
        ),
        iterator_to_array($dependency)
      );
    }

    /**
     * @covers \Papaya\CMS\Content\Page\Dependencies::getDependency
     */
    public function testGetDependencyWithData() {
      $dependencies = new Dependencies_TestProxy();
      $dependencies->_records = array(
        21 => array(
          'id' => 21,
          'origin_id' => 42,
          'synchronization' => 35,
          'note' => 'sample note',
          'title' => 'sample page title',
          'modified' => 123,
          'unpublished_languages' => 1,
          'published' => 456,
          'published_from' => 0,
          'published_to' => 0
        )
      );
      $dependency = $dependencies->getDependency(21);
      $this->assertEquals(
        array(
          'id' => 21,
          'origin_id' => 42,
          'synchronization' => 35,
          'note' => 'sample note'
        ),
        iterator_to_array($dependency)
      );
    }

    /**
     * @covers \Papaya\CMS\Content\Page\Dependencies::delete
     */
    public function testDelete() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('deleteRecord')
        ->with()
        ->will($this->returnValue(TRUE));
      $dependencies = new Dependencies();
      $dependencies->setDatabaseAccess($databaseAccess);
      $this->assertTrue(
        $dependencies->delete(42)
      );
    }

    /**
     * @covers \Papaya\CMS\Content\Page\Dependencies::delete
     */
    public function testDeleteChangesRecords() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('deleteRecord')
        ->with()
        ->will($this->returnValue(TRUE));
      $dependencies = new Dependencies_TestProxy();
      $dependencies->_records = array(
        21 => array(
          'id' => 21,
          'origin_id' => 42,
          'synchronization' => 35,
          'note' => 'sample note',
          'title' => 'sample page title',
          'modified' => 123,
          'unpublished_languages' => 1,
          'published' => 456,
          'published_from' => 0,
          'published_to' => 0
        )
      );
      $dependencies->setDatabaseAccess($databaseAccess);
      $dependencies->delete(21);
      $this->assertEquals(
        array(), iterator_to_array($dependencies)
      );
    }

    /**
     * @covers \Papaya\CMS\Content\Page\Dependencies::changeOrigin
     */
    public function testChangeOrigin() {
      $databaseResultLoad = $this->createMock(\Papaya\Database\Result::class);
      $databaseResultLoad
        ->expects($this->any())
        ->method('fetchRow')
        ->with($this->isType('integer'))
        ->will($this->returnValue(FALSE));
      $databaseResultCheck = $this->createMock(\Papaya\Database\Result::class);
      $databaseResultCheck
        ->expects($this->any())
        ->method('fetchField')
        ->will($this->returnValue(0));
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->exactly(2))
        ->method('getSqlCondition')
        ->with($this->isType('array'))
        ->will($this->returnValue(" topic_id = 'xx'"));
      $databaseAccess
        ->expects($this->once())
        ->method('deleteRecord')
        ->with()
        ->will($this->returnValue(TRUE));
      $databaseAccess
        ->expects($this->once())
        ->method('updateRecord')
        ->with('table_topic_dependencies', array('topic_origin_id' => 42), array('topic_origin_id' => 21))
        ->will($this->returnValue(TRUE));
      $databaseAccess
        ->expects($this->exactly(3))
        ->method('queryFmt')
        ->withAnyParameters()
        ->will(
          $this->onConsecutiveCalls(
            $databaseResultLoad, $databaseResultCheck, $databaseResultCheck
          )
        );
      $databaseAccess
        ->expects($this->once())
        ->method('insertRecord')
        ->with(
          'table_topic_dependencies',
          NULL,
          array(
            'topic_id' => 21,
            'topic_note' => NULL,
            'topic_origin_id' => 42,
            'topic_synchronization' => NULL
          )
        )
        ->will($this->returnValue(TRUE));
      $dependencies = new Dependencies();
      $dependencies->setDatabaseAccess($databaseAccess);
      $dependencies->changeOrigin(21, 42);
    }
  }

  class Dependencies_TestProxy extends Dependencies {

    public $_records;
  }
}
