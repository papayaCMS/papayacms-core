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

require_once __DIR__.'/../../../../bootstrap.php';

class PapayaProfilerStorageXhguiTest extends PapayaTestCase {

  /**
  * @covers \PapayaProfilerStorageXhgui::__construct
  */
  public function testConstructor() {
    $storage = new \PapayaProfilerStorageXhgui('database', 'table', 'foo');
    $this->assertAttributeEquals(
      'database', '_database', $storage
    );
    $this->assertAttributeEquals(
      'table', '_tableName', $storage
    );
    $this->assertAttributeEquals(
      'foo', '_serverId', $storage
    );
  }

  /**
  * @covers \PapayaProfilerStorageXhgui::saveRun
  * @covers \PapayaProfilerStorageXhgui::getId
  * @covers \PapayaProfilerStorageXhgui::normalizeUrl
  * @covers \PapayaProfilerStorageXhgui::removeSid
  */
  public function testSaveRun() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmtWrite')
      ->with($this->isType('string'), $this->isType('array'))
      ->will($this->returnValue(TRUE));
    $storage = new \PapayaProfilerStorageXhgui('database', 'table', 'foo');
    $storage->setDatabaseAccess($databaseAccess);
    $this->assertNotEmpty(
      $storage->saveRun(array(), 'type')
    );
  }

  /**
  * @covers \PapayaProfilerStorageXhgui::setDatabaseAccess
  * @covers \PapayaProfilerStorageXhgui::getDatabaseAccess
  */
  public function testGetDatabaseAccessAfterSet() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $storage = new \PapayaProfilerStorageXhgui('database', 'table', 'foo');
    $storage->setDatabaseAccess($databaseAccess);
    $this->assertSame(
      $databaseAccess, $storage->getDatabaseAccess()
    );
  }

  /**
  * @covers \PapayaProfilerStorageXhgui::getDatabaseAccess
  */
  public function testGetDatabaseAccessImplicitCreate() {
    $storage = new \PapayaProfilerStorageXhgui('database', 'table', 'foo');
    $storage->papaya($this->mockPapaya()->application());
    $this->assertInstanceOf(
      \PapayaDatabaseAccess::class, $storage->getDatabaseAccess()
    );
    $this->assertSame(
      $storage->papaya(), $storage->getDatabaseAccess()->papaya()
    );
  }
}
