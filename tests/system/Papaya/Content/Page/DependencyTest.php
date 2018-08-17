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

namespace Papaya\Content\Page {

  require_once __DIR__.'/../../../../bootstrap.php';

  class DependencyTest extends \Papaya\TestCase {

    /**
     * @covers Dependency::_createKey
     */
    public function testCreateKey() {
      $dependency = new Dependency();
      $key = $dependency->key();
      $this->assertInstanceOf(\Papaya\Database\Record\Key\Fields::class, $key);
      $this->assertEquals(array('id'), $key->getProperties());
    }

    /**
     * @covers Dependency::save
     */
    public function testSaveWithoutPageIdExpectingException() {
      $dependency = new Dependency();
      try {
        $dependency->save();
      } catch (\UnexpectedValueException $e) {
        $this->assertEquals(
          'UnexpectedValueException: No target page defined.',
          $e->getMessage()
        );
      }
    }

    /**
     * @covers Dependency::save
     */
    public function testSaveWithoutOriginPageIdExpectingException() {
      $dependency = new Dependency();
      $dependency->id = 1;
      try {
        $dependency->save();
      } catch (\UnexpectedValueException $e) {
        $this->assertEquals(
          'UnexpectedValueException: No origin page defined.',
          $e->getMessage()
        );
      }
    }

    /**
     * @covers Dependency::save
     */
    public function testSaveIdEqualsOriginExpectingException() {
      $dependency = new Dependency();
      $dependency->id = 1;
      $dependency->originId = 1;
      try {
        $dependency->save();
      } catch (\UnexpectedValueException $e) {
        $this->assertEquals(
          'UnexpectedValueException: Target equals origin.',
          $e->getMessage()
        );
      }
    }

    /**
     * @covers Dependency::save
     */
    public function testSaveOriginHasDependencyExpectingException() {
      $dependency = new Dependency_TestProxy();
      $dependency->isDependency = TRUE;
      $dependency->id = 1;
      $dependency->originId = 2;
      try {
        $dependency->save();
      } catch (\UnexpectedValueException $e) {
        $this->assertEquals(
          'UnexpectedValueException: Origin page is a dependency. Chaining is not possible.',
          $e->getMessage()
        );
      }
    }

    /**
     * @covers Dependency::save
     */
    public function testSaveInsertsRecordExpectingTrue() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->any())
        ->method('getSqlCondition')
        ->with(array('topic_id' => 21))
        ->will($this->returnValue('>>CONDITION<<'));
      $databaseAccess
        ->expects($this->any())
        ->method('queryFmt')
        ->with(
        /** @lang Text */
          'SELECT COUNT(*) FROM %s WHERE >>CONDITION<<',
          array('table_topic_dependencies')
        )
        ->will($this->returnValue(NULL));
      $databaseAccess
        ->expects($this->once())
        ->method('insertRecord')
        ->with(
          'table_topic_dependencies',
          NULL,
          array(
            'topic_id' => 21,
            'topic_origin_id' => 42,
            'topic_synchronization' => 35,
            'topic_note' => 'sample note'
          )
        )
        ->will($this->returnValue(TRUE));
      $dependency = new Dependency_TestProxy();
      $dependency->setDatabaseAccess($databaseAccess);
      $dependency->isDependency = FALSE;
      $dependency->id = 21;
      $dependency->originId = 42;
      $dependency->synchronization =
        Dependency::SYNC_PROPERTIES |
        Dependency::SYNC_CONTENT |
        Dependency::SYNC_PUBLICATION;
      $dependency->note = 'sample note';
      $this->assertEquals(array('id' => 21), $dependency->save()->getFilter());
    }

    /**
     * @covers Dependency::isDependency
     */
    public function testIsDependencyExpectingTrue() {
      $databaseResult = $this->createMock(\Papaya\Database\Result::class);
      $databaseResult
        ->expects($this->once())
        ->method('fetchField')
        ->will($this->returnValue(1));
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('queryFmt')
        ->with($this->isType('string'), array('table_topic_dependencies', 42))
        ->will($this->returnValue($databaseResult));
      $dependency = new Dependency();
      $dependency->setDatabaseAccess($databaseAccess);
      $this->assertTrue($dependency->isDependency(42));
    }

    /**
     * @covers Dependency::isDependency
     */
    public function testIsDependencyExpectingFalse() {
      $databaseResult = $this->createMock(\Papaya\Database\Result::class);
      $databaseResult
        ->expects($this->once())
        ->method('fetchField')
        ->will($this->returnValue(0));
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('queryFmt')
        ->with($this->isType('string'), array('table_topic_dependencies', 42))
        ->will($this->returnValue($databaseResult));
      $dependency = new Dependency();
      $dependency->setDatabaseAccess($databaseAccess);
      $this->assertFalse($dependency->isDependency(42));
    }

    /**
     * @covers Dependency::isDependency
     */
    public function testIsDependencyWithDatabaseErrorExpectingFalse() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('queryFmt')
        ->with($this->isType('string'), array('table_topic_dependencies', 42))
        ->will($this->returnValue(FALSE));
      $dependency = new Dependency();
      $dependency->setDatabaseAccess($databaseAccess);
      $this->assertFalse($dependency->isDependency(42));
    }

    /**
     * @covers Dependency::isOrigin
     */
    public function testIsOriginExpectingTrue() {
      $databaseResult = $this->createMock(\Papaya\Database\Result::class);
      $databaseResult
        ->expects($this->once())
        ->method('fetchField')
        ->will($this->returnValue(1));
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('queryFmt')
        ->with($this->isType('string'), array('table_topic_dependencies', 42))
        ->will($this->returnValue($databaseResult));
      $dependency = new Dependency();
      $dependency->setDatabaseAccess($databaseAccess);
      $this->assertTrue($dependency->isOrigin(42));
    }

    /**
     * @covers Dependency::isOrigin
     */
    public function testIsOriginExpectingFalse() {
      $databaseResult = $this->createMock(\Papaya\Database\Result::class);
      $databaseResult
        ->expects($this->once())
        ->method('fetchField')
        ->will($this->returnValue(0));
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('queryFmt')
        ->with($this->isType('string'), array('table_topic_dependencies', 42))
        ->will($this->returnValue($databaseResult));
      $dependency = new Dependency();
      $dependency->setDatabaseAccess($databaseAccess);
      $this->assertFalse($dependency->isOrigin(42));
    }

    /**
     * @covers Dependency::isOrigin
     */
    public function testIsOriginWithDatabaseErrorExpectingFalse() {
      $databaseAccess = $this->mockPapaya()->databaseAccess();
      $databaseAccess
        ->expects($this->once())
        ->method('queryFmt')
        ->with($this->isType('string'), array('table_topic_dependencies', 42))
        ->will($this->returnValue(FALSE));
      $dependency = new Dependency();
      $dependency->setDatabaseAccess($databaseAccess);
      $this->assertFalse($dependency->isOrigin(42));
    }
  }

  class Dependency_TestProxy extends Dependency {

    public $isDependency = FALSE;

    public function isDependency($pageId) {
      return $this->isDependency;
    }
  }
}
