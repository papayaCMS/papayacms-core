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

namespace Papaya\Content\Box;

require_once __DIR__.'/../../../../bootstrap.php';

class VersionsTest extends \Papaya\TestCase {
  /**
   * @covers Versions::load
   */
  public function testLoad() {
    $databaseResult = $this->createMock(\Papaya\Database\Result::class);
    $databaseResult
      ->expects($this->any())
      ->method('fetchRow')
      ->with($this->equalTo(\Papaya\Database\Result::FETCH_ASSOC))
      ->will(
        $this->onConsecutiveCalls(
          array(
            'version_id' => '21',
            'version_time' => '123',
            'version_author_id' => '1',
            'version_message' => 'Version log message',
            'box_id' => '42'
          ),
          FALSE
        )
      );
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('table_box_versions', 42), 10, 0)
      ->will($this->returnValue($databaseResult));
    $list = new Versions();
    $list->setDatabaseAccess($databaseAccess);
    $this->assertTrue($list->load(42, 10, 0));
    $this->assertAttributeEquals(
      array(
        '21' => array(
          'id' => '21',
          'created' => '123',
          'owner' => '1',
          'message' => 'Version log message',
          'box_id' => '42',
        )
      ),
      '_records',
      $list
    );
  }

  /**
   * @covers Versions::getVersion
   */
  public function testGetVersion() {
    $databaseAccess = $this->mockPapaya()->databaseAccess();
    $databaseAccess
      ->expects($this->once())
      ->method('queryFmt')
      ->with($this->isType('string'), array('table_box_versions', 21))
      ->will($this->returnValue(FALSE));
    $list = new Versions();
    $list->setDatabaseAccess($databaseAccess);
    $version = $list->getVersion(21);
    $this->assertInstanceOf(
      Version::class, $version
    );
  }
}
