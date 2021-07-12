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

namespace Papaya\Profiler\Storage;
require_once __DIR__.'/../../../../bootstrap.php';

class XhguiTest extends \Papaya\TestFramework\TestCase {

  /**
   * @covers \Papaya\Profiler\Storage\Xhgui::__construct
   */
  public function testConstructor() {
    $storage = new Xhgui('database', 'table', 'foo');
    $this->assertEquals(
      'database', $storage->getDatabase()
    );
    $this->assertEquals(
      'table', $storage->getTableName()
    );
    $this->assertEquals(
      'foo', $storage->getServerID()
    );
  }

  /**
   * @covers \Papaya\Profiler\Storage\Xhgui::saveRun
   * @covers \Papaya\Profiler\Storage\Xhgui::getId
   * @covers \Papaya\Profiler\Storage\Xhgui::normalizeURL
   * @covers \Papaya\Profiler\Storage\Xhgui::removeSid
   */
  public function testSaveRun() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmtWrite')
      ->with($this->isType('string'), $this->isType('array'))
      ->will($this->returnValue(TRUE));
    $storage = new Xhgui('database', 'table', 'foo');
    $storage->setDatabaseAccess($databaseAccess);
    $this->assertNotEmpty(
      $storage->saveRun(array(), 'type')
    );
  }

  /**
   * @covers \Papaya\Profiler\Storage\Xhgui::setDatabaseAccess
   * @covers \Papaya\Profiler\Storage\Xhgui::getDatabaseAccess
   */
  public function testGetDatabaseAccessAfterSet() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $storage = new Xhgui('database', 'table', 'foo');
    $storage->setDatabaseAccess($databaseAccess);
    $this->assertSame(
      $databaseAccess, $storage->getDatabaseAccess()
    );
  }

  /**
   * @covers \Papaya\Profiler\Storage\Xhgui::getDatabaseAccess
   */
  public function testGetDatabaseAccessImplicitCreate() {
    $storage = new Xhgui('database', 'table', 'foo');
    $storage->papaya($this->mockPapaya()->application());
    $this->assertInstanceOf(
      \Papaya\Database\Access::class, $storage->getDatabaseAccess()
    );
    $this->assertSame(
      $storage->papaya(), $storage->getDatabaseAccess()->papaya()
    );
  }
}
